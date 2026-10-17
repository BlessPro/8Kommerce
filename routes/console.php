<?php

use App\Mail\WeeklySellerPerformanceSummary;
use App\Models\AdminAuditLog;
use App\Models\AnalyticsEvent;
use App\Models\CustomerTrackingSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\SellerNotification;
use App\Models\TwilioMessageLog;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Services\PaymentReconciliationService;
use App\Services\SellerNotifier;
use App\Services\SubscriptionService;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:seed {--reset} {--force}', function () {
    if (app()->environment('production') && ! $this->option('force')) {
        $this->error('Refusing to run demo seeding in production without --force.');

        return 1;
    }

    if ($this->option('reset')) {
        DB::statement('TRUNCATE TABLE payments, order_items, orders, coupons, products, invoices, seller_profiles, users RESTART IDENTITY CASCADE');
        $this->warn('Demo reset completed.');
    }

    Artisan::call('db:seed', [
        '--class' => DemoDataSeeder::class,
        '--force' => true,
    ]);

    $this->info('Demo data seeded.');
    $this->line('Admin: admin@8kommerce.demo / password');
    $this->line('Seller sample: apex@8kommerce.demo / password');
})->purpose('Seed deterministic demo data for staging/local demos');

Artisan::command('ops:smoke:http {--base-url=} {--timeout=10}', function () {
    $base = rtrim((string) ($this->option('base-url') ?: config('app.url')), '/');
    $timeout = max(2, (int) $this->option('timeout'));

    if ($base === '') {
        $this->error('Missing base URL. Set APP_URL or pass --base-url=');

        return 1;
    }

    $checks = [
        ['label' => 'Landing', 'path' => '/'],
        ['label' => 'Pricing', 'path' => '/pricing'],
        ['label' => 'Sellers', 'path' => '/sellers'],
        ['label' => 'Privacy', 'path' => '/privacy'],
        ['label' => 'Terms', 'path' => '/terms'],
        ['label' => 'Register', 'path' => '/register'],
        ['label' => 'Login', 'path' => '/login'],
        ['label' => 'Admin Login', 'path' => '/admin/login'],
    ];

    $rows = [];
    $hasFailure = false;

    foreach ($checks as $check) {
        $url = $base.$check['path'];
        try {
            $response = Http::timeout($timeout)->get($url);
            $ok = $response->successful();
            $rows[] = [$check['label'], $url, $response->status(), $ok ? 'PASS' : 'FAIL'];
            if (! $ok) {
                $hasFailure = true;
            }
        } catch (Throwable $exception) {
            $rows[] = [$check['label'], $url, 'ERR', 'FAIL'];
            $hasFailure = true;
        }
    }

    $this->table(['Check', 'URL', 'Status', 'Result'], $rows);

    if ($hasFailure) {
        $this->error('Smoke HTTP checks failed.');

        return 1;
    }

    $this->info('Smoke HTTP checks passed.');
})->purpose('Run launch smoke checks against public/admin entry routes');

Artisan::command('ops:smoke:test-suite', function () {
    $this->line('Running auth + checkout + webhook smoke feature tests...');

    $exitCode = Artisan::call('test', [
        '--filter' => 'AuthenticationTest|PublicCheckoutSmokeTest|WebhookIdempotencyTest',
    ]);

    $this->output->write(Artisan::output());

    if ($exitCode !== 0) {
        $this->error('Smoke test suite failed.');

        return $exitCode;
    }

    $this->info('Smoke test suite passed.');

    return 0;
})->purpose('Run key smoke feature tests (register/login/checkout/webhook)');

Artisan::command('payments:reconcile {--days=1}', function (PaymentReconciliationService $reconciliation) {
    $days = (int) $this->option('days');
    $report = $reconciliation->buildReport($days);

    $this->info('Reconciliation complete.');
    $this->table(
        ['Metric', 'Value'],
        [
            ['Period (days)', $report['days']],
            ['Local total', $report['localTotal']],
            ['Paystack total', $report['paystackTotal']],
            ['Matched', $report['statusCounts']['matched']],
            ['Missing in DB', $report['statusCounts']['missing_in_db']],
            ['Missing in Paystack', $report['statusCounts']['missing_in_paystack']],
            ['Amount mismatch', $report['statusCounts']['amount_mismatch']],
            ['Status mismatch', $report['statusCounts']['status_mismatch']],
            ['Duplicate reference', $report['statusCounts']['duplicate_reference']],
        ]
    );

    $adminId = User::query()->where('is_admin', true)->value('id');
    if ($adminId) {
        AdminAuditLog::create([
            'admin_user_id' => $adminId,
            'action' => 'payment.reconciliation.snapshot',
            'target_type' => 'system',
            'target_id' => null,
            'title' => 'Scheduled reconciliation snapshot',
            'meta' => [
                'days' => $report['days'],
                'localTotal' => $report['localTotal'],
                'paystackTotal' => $report['paystackTotal'],
                'statusCounts' => $report['statusCounts'],
            ],
            'ip_address' => null,
        ]);

        $critical = (int) ($report['severityBuckets']['critical'] ?? 0);
        $high = (int) ($report['severityBuckets']['high'] ?? 0);
        $criticalThreshold = (int) config('monitoring.reconciliation.critical_threshold', 1);
        $highThreshold = (int) config('monitoring.reconciliation.high_threshold', 3);

        if ($critical >= $criticalThreshold || $high >= $highThreshold) {
            AdminAuditLog::create([
                'admin_user_id' => $adminId,
                'action' => 'payment.reconciliation.alert',
                'target_type' => 'system',
                'target_id' => null,
                'title' => 'Reconciliation threshold breached',
                'meta' => [
                    'days' => $report['days'],
                    'critical' => $critical,
                    'high' => $high,
                    'critical_threshold' => $criticalThreshold,
                    'high_threshold' => $highThreshold,
                ],
                'ip_address' => null,
            ]);
        }
    }
})->purpose('Run Paystack vs DB reconciliation report');

Schedule::command('payments:reconcile --days=1')->dailyAt('03:30');

Artisan::command('orders:remind-overdue-sellers', function () {
    $notified = 0;

    Order::query()
        ->with('user')
        ->where('status', Order::STATUS_PAID)
        ->whereNotNull('seller_response_due_at')
        ->where('seller_response_due_at', '<=', now())
        ->chunkById(100, function ($orders) use (&$notified) {
            foreach ($orders as $order) {
                if (! $order->user) {
                    continue;
                }

                app(SellerNotifier::class)->notify(
                    $order->user,
                    SellerNotification::TYPE_ORDER_STATUS_UPDATED,
                    'Paid order needs your response',
                    'Accept or cancel order '.$order->reference.' so the customer is not left waiting.',
                    ['order_id' => $order->id, 'reminder' => 'seller_response_overdue']
                );

                // Nulling the deadline makes this a one-time reminder and prevents notification spam.
                $order->seller_response_due_at = null;
                $order->save();
                $notified++;
            }
        });

    $this->info("Overdue order reminders sent: {$notified}.");
})->purpose('Notify sellers once when a paid order has not been accepted in time');

Schedule::command('orders:remind-overdue-sellers')->hourly();

Artisan::command('ops:failed-jobs:alert {--hours=24} {--threshold=1}', function () {
    $hours = max(1, (int) $this->option('hours'));
    $threshold = max(1, (int) $this->option('threshold'));

    $since = now()->subHours($hours);
    $total = DB::table(config('queue.failed.table', 'failed_jobs'))
        ->where('failed_at', '>=', $since)
        ->count();

    $this->info('Failed jobs in last '.$hours.'h: '.$total);

    if ($total < $threshold) {
        return;
    }

    Log::warning('Failed jobs threshold reached', [
        'hours' => $hours,
        'threshold' => $threshold,
        'total' => $total,
    ]);

    $adminId = User::query()->where('is_admin', true)->value('id');
    if (! $adminId) {
        return;
    }

    AdminAuditLog::create([
        'admin_user_id' => $adminId,
        'action' => 'queue.failed_jobs.alert',
        'target_type' => 'system',
        'target_id' => null,
        'title' => 'Failed jobs threshold reached',
        'meta' => [
            'hours' => $hours,
            'threshold' => $threshold,
            'total' => $total,
        ],
        'ip_address' => null,
    ]);
})->purpose('Alert when failed jobs exceed threshold in a time window');

Schedule::command('ops:failed-jobs:alert --hours=24 --threshold=1')->hourly();

Artisan::command('ops:queue-health {--max=}', function () {
    $connection = (string) config('queue.default');
    $maxDepth = max(1, (int) ($this->option('max') ?: config('monitoring.queue.max_depth', 100)));
    $queues = (array) config('monitoring.queue.names', ['otp', 'payments', 'notifications', 'default']);
    $rows = [];
    $overloaded = [];

    foreach ($queues as $queue) {
        $depth = Queue::connection($connection)->size($queue);
        $status = $depth > $maxDepth ? 'OVERLOADED' : 'OK';
        $rows[] = [$connection, $queue, $depth, $status];

        if ($depth > $maxDepth) {
            $overloaded[$queue] = $depth;
        }
    }

    $this->table(['Connection', 'Queue', 'Depth', 'Status'], $rows);

    if ($overloaded === []) {
        return 0;
    }

    $cooldown = max(60, (int) config('monitoring.queue.alert_cooldown_seconds', 300));
    if (! Cache::add('ops:queue-health:alert', true, $cooldown)) {
        return 1;
    }

    Log::warning('Queue depth threshold exceeded', [
        'connection' => $connection,
        'max_depth' => $maxDepth,
        'queues' => $overloaded,
    ]);

    $adminId = User::query()->where('is_admin', true)->value('id');
    if ($adminId) {
        AdminAuditLog::create([
            'admin_user_id' => $adminId,
            'action' => 'queue.depth.alert',
            'target_type' => 'system',
            'target_id' => null,
            'title' => 'Queue depth threshold exceeded',
            'meta' => [
                'connection' => $connection,
                'max_depth' => $maxDepth,
                'queues' => $overloaded,
            ],
            'ip_address' => null,
        ]);
    }

    return 1;
})->purpose('Report queue depths and alert when work is backing up');

Schedule::command('ops:queue-health')->everyMinute()->withoutOverlapping();

Artisan::command('ops:failed-jobs:retry-latest {--limit=25} {--dry-run}', function () {
    $limit = max(1, min(200, (int) $this->option('limit')));
    $dryRun = (bool) $this->option('dry-run');
    $table = config('queue.failed.table', 'failed_jobs');

    $failed = DB::table($table)
        ->orderByDesc('failed_at')
        ->limit($limit)
        ->get(['id', 'queue', 'failed_at']);

    if ($failed->isEmpty()) {
        $this->info('No failed jobs found.');

        return 0;
    }

    $this->table(
        ['ID', 'Queue', 'Failed At'],
        $failed->map(fn ($job) => [$job->id, $job->queue, $job->failed_at])->all()
    );

    if ($dryRun) {
        $this->warn('Dry run only; no retries executed.');

        return 0;
    }

    $retried = 0;
    foreach ($failed as $job) {
        Artisan::call('queue:retry', ['id' => (string) $job->id]);
        $retried++;
    }

    $adminId = User::query()->where('is_admin', true)->value('id');
    if ($adminId) {
        AdminAuditLog::create([
            'admin_user_id' => $adminId,
            'action' => 'queue.failed_jobs.retry_latest',
            'target_type' => 'system',
            'target_id' => null,
            'title' => 'Retry latest failed jobs',
            'meta' => [
                'limit' => $limit,
                'retried' => $retried,
            ],
            'ip_address' => null,
        ]);
    }

    $this->info("Retried {$retried} failed job(s).");

    return 0;
})->purpose('Retry latest failed jobs in bulk with optional dry-run');

Artisan::command('sellers:weekly-performance-email', function () {
    $from = now()->subDays(6)->startOfDay();
    $to = now()->endOfDay();
    $currency = config('services.paystack.currency', 'GHS');

    $sent = 0;
    $skipped = 0;

    User::query()
        ->whereNotNull('email')
        ->has('sellerProfile')
        ->chunkById(100, function ($sellers) use ($from, $to, $currency, &$sent, &$skipped) {
            foreach ($sellers as $seller) {
                $revenue = (string) $seller->payments()
                    ->where('status', Payment::STATUS_SUCCESS)
                    ->whereBetween('created_at', [$from, $to])
                    ->sum('amount');

                $paidOrders = $seller->orders()
                    ->where('status', Order::STATUS_PAID)
                    ->whereBetween('created_at', [$from, $to])
                    ->count();

                $newCustomers = $seller->orders()
                    ->where('status', Order::STATUS_PAID)
                    ->whereBetween('created_at', [$from, $to])
                    ->distinct('customer_phone')
                    ->count('customer_phone');

                $topProduct = OrderItem::query()
                    ->selectRaw('product_name, SUM(quantity) as qty')
                    ->whereHas('order', function ($query) use ($seller, $from, $to) {
                        $query->where('user_id', $seller->id)
                            ->where('status', Order::STATUS_PAID)
                            ->whereBetween('created_at', [$from, $to]);
                    })
                    ->groupBy('product_name')
                    ->orderByDesc('qty')
                    ->value('product_name');

                if ($revenue === '0' && $paidOrders === 0 && $newCustomers === 0 && $topProduct === null) {
                    $skipped++;

                    continue;
                }

                Mail::to($seller->email)->queue((new WeeklySellerPerformanceSummary([
                    'seller_name' => $seller->sellerProfile?->business_name ?? $seller->name,
                    'revenue' => $revenue,
                    'paid_orders' => $paidOrders,
                    'new_customers' => $newCustomers,
                    'top_product' => $topProduct,
                    'currency' => $currency,
                ]))->onQueue('notifications'));

                $sent++;
            }
        });

    $this->info("Weekly summaries sent: {$sent}. Skipped: {$skipped}.");
})->purpose('Send weekly seller performance summary emails');

Schedule::command('sellers:weekly-performance-email')->weeklyOn(1, '08:00');
Schedule::call(fn () => app(SubscriptionService::class)->expireStale())
    ->hourly()
    ->name('subscriptions:expire-stale')
    ->withoutOverlapping();
Schedule::call(fn () => app(SubscriptionService::class)->grantDuePlacements())
    ->dailyAt('00:20')
    ->name('subscriptions:grant-monthly-placements')
    ->withoutOverlapping();
Schedule::call(fn () => CustomerTrackingSession::query()->where('expires_at', '<=', now())->delete())
    ->dailyAt('03:15')
    ->name('customer-tracking:prune-sessions');

Schedule::call(function (): void {
    $webhookDays = max(1, (int) config('security.retention.webhook_days', 180));
    $messageDays = max(1, (int) config('security.retention.message_log_days', 90));
    $analyticsDays = max(1, (int) config('security.retention.analytics_days', 365));

    WebhookEvent::query()->where('created_at', '<', now()->subDays($webhookDays))->delete();
    TwilioMessageLog::query()->where('created_at', '<', now()->subDays($messageDays))->delete();
    AnalyticsEvent::query()->where('created_at', '<', now()->subDays($analyticsDays))->delete();
})
    ->dailyAt('03:30')
    ->name('security:prune-sensitive-logs');
