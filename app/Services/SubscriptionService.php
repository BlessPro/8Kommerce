<?php

namespace App\Services;

use App\Models\FeaturedPlacement;
use App\Models\SellerNotification;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class SubscriptionService
{
    public const WEBHOOK_EVENTS = [
        'subscription.create', 'subscription.disable', 'subscription.not_renew',
        'subscription.expiring_cards', 'invoice.create', 'invoice.update', 'invoice.payment_failed',
    ];

    public function planConfig(string $plan): array
    {
        $key = strtolower($plan);
        $config = config('plans.'.$key);
        if (! is_array($config) || ! in_array(strtoupper($plan), [User::PLAN_STARTER, User::PLAN_GROWTH, User::PLAN_ENTERPRISE], true)) {
            throw new RuntimeException('Unknown subscription plan.');
        }
        return $config;
    }

    public function createPending(User $user, string $plan, string $email): Subscription
    {
        $plan = strtoupper($plan);
        $config = $this->planConfig($plan);
        $planCode = (string) config('billing.plan_codes.'.$plan);
        if ($planCode === '') {
            throw new RuntimeException('This subscription plan is not configured yet.');
        }

        return $user->subscriptions()->create([
            'plan_type' => $plan,
            'provider' => 'paystack',
            'provider_plan_code' => $planCode,
            'checkout_reference' => 'SUB-'.Str::upper(Str::random(24)),
            'billing_email' => Str::lower($email),
            'status' => Subscription::STATUS_PENDING,
            'amount' => number_format((float) ($config['price_monthly'] ?? 0), 2, '.', ''),
            'currency' => config('services.paystack.currency', 'GHS'),
            'interval' => 'monthly',
        ]);
    }

    public function findForEvent(array $data, bool $allowEmailFallback = true): ?Subscription
    {
        $subscriptionCode = $this->subscriptionCode($data);
        $reference = (string) (data_get($data, 'reference') ?? data_get($data, 'transaction.reference') ?? '');
        $localId = data_get($data, 'metadata.subscription_id') ?? data_get($data, 'transaction.metadata.subscription_id');
        $email = Str::lower((string) (data_get($data, 'customer.email') ?? data_get($data, 'email') ?? ''));
        $planCode = (string) (data_get($data, 'plan.plan_code') ?? data_get($data, 'plan_code') ?? '');

        $found = null;
        if ($subscriptionCode !== '' || $reference !== '' || $localId) {
            $found = Subscription::query()->where(function ($query) use ($subscriptionCode, $reference, $localId): void {
                if ($subscriptionCode !== '') $query->where('provider_subscription_code', $subscriptionCode);
                if ($reference !== '') $subscriptionCode !== '' ? $query->orWhere('checkout_reference', $reference) : $query->where('checkout_reference', $reference);
                if ($localId) ($subscriptionCode !== '' || $reference !== '') ? $query->orWhere('id', $localId) : $query->where('id', $localId);
            })->latest('id')->first();
        }

        if ($found || $email === '' || ! $allowEmailFallback) {
            return $found;
        }

        return Subscription::query()->where('billing_email', $email)
            ->when($planCode !== '', fn ($query) => $query->where('provider_plan_code', $planCode))
            ->whereIn('status', [Subscription::STATUS_PENDING, Subscription::STATUS_ACTIVE, Subscription::STATUS_NON_RENEWING, Subscription::STATUS_PAST_DUE])
            ->latest('id')->first();
    }

    public function activateFromCharge(Subscription $subscription, array $data): void
    {
        $status = (string) data_get($data, 'status');
        if ($status !== 'success') {
            throw new RuntimeException('Subscription transaction was not successful.');
        }

        $amount = Money::fromMinor((string) data_get($data, 'amount', 0));
        $currency = strtoupper((string) data_get($data, 'currency', ''));
        if (Money::compare($amount, (string) $subscription->amount) !== 0 || $currency !== strtoupper($subscription->currency)) {
            throw new RuntimeException('Subscription amount or currency mismatch.');
        }
        $verifiedPlan = (string) (data_get($data, 'plan.plan_code') ?? (is_string(data_get($data, 'plan')) ? data_get($data, 'plan') : ''));
        if ($verifiedPlan !== '' && ! hash_equals((string) $subscription->provider_plan_code, $verifiedPlan)) {
            throw new RuntimeException('Subscription plan mismatch.');
        }
        $metadataSubscriptionId = data_get($data, 'metadata.subscription_id');
        if ($metadataSubscriptionId && (int) $metadataSubscriptionId !== $subscription->id) {
            throw new RuntimeException('Subscription metadata mismatch.');
        }

        $paidAt = Carbon::parse(data_get($data, 'paid_at') ?: now());
        $periodEnd = $this->nextPaymentDate($data) ?: $paidAt->copy()->addMonthNoOverflow();

        DB::transaction(function () use ($subscription, $data, $paidAt, $periodEnd): void {
            $subscription = Subscription::query()->lockForUpdate()->findOrFail($subscription->id);
            $subscription->update([
                'provider_subscription_code' => $this->subscriptionCode($data) ?: $subscription->provider_subscription_code,
                'provider_customer_code' => data_get($data, 'customer.customer_code') ?: $subscription->provider_customer_code,
                'provider_email_token' => data_get($data, 'subscription.email_token') ?: $subscription->provider_email_token,
                'status' => Subscription::STATUS_ACTIVE,
                'current_period_start' => $paidAt,
                'current_period_end' => $periodEnd,
                'next_payment_at' => $periodEnd,
                'grace_ends_at' => null,
                'failed_at' => null,
                'last_payment_at' => $paidAt,
                'last_event_at' => now(),
            ]);

            $subscription->user()->update([
                'plan_type' => $subscription->plan_type,
                'plan_started_at' => $subscription->user->plan_started_at ?: $paidAt,
                'plan_ends_at' => $periodEnd,
            ]);

            $this->recordInvoice($subscription, $data, 'paid');
        });

        $this->grantMonthlyPlacement($subscription->fresh());
    }

    public function handleEvent(string $event, array $data): bool
    {
        if (! in_array($event, self::WEBHOOK_EVENTS, true)) {
            return false;
        }

        if ($event === 'subscription.expiring_cards') {
            return true;
        }

        $subscription = $this->findForEvent($data);
        if (! $subscription) {
            return false;
        }

        $subscriptionCode = $this->subscriptionCode($data);
        $common = [
            'provider_subscription_code' => $subscriptionCode ?: $subscription->provider_subscription_code,
            'provider_customer_code' => data_get($data, 'customer.customer_code') ?: $subscription->provider_customer_code,
            'provider_email_token' => data_get($data, 'email_token') ?: data_get($data, 'subscription.email_token') ?: $subscription->provider_email_token,
            'next_payment_at' => $this->nextPaymentDate($data) ?: $subscription->next_payment_at,
            'last_event_at' => now(),
        ];

        if ($event === 'subscription.create') {
            // Provider creation alone is not proof of payment. Access is activated
            // only after a charge.success transaction is independently verified.
            $subscription->update($common);
        } elseif ($event === 'subscription.not_renew') {
            $subscription->update([...$common, 'status' => Subscription::STATUS_NON_RENEWING, 'cancel_at_period_end' => true]);
        } elseif ($event === 'subscription.disable') {
            $subscription->update([...$common, 'status' => Subscription::STATUS_CANCELLED, 'cancelled_at' => now()]);
            $subscription->user()->update(['plan_ends_at' => now()]);
        } elseif ($event === 'invoice.create') {
            $this->recordInvoice($subscription, $data, 'pending');
            $subscription->update($common);
        } elseif ($event === 'invoice.payment_failed') {
            $graceEnd = now()->addDays((int) config('billing.grace_days', 3));
            $subscription->update([...$common, 'status' => Subscription::STATUS_PAST_DUE, 'failed_at' => now(), 'grace_ends_at' => $graceEnd]);
            $subscription->user()->update(['plan_ends_at' => $graceEnd]);
            $this->recordInvoice($subscription, $data, 'failed');
            $this->notify($subscription->user, 'Subscription payment failed', 'Your payment could not be processed. Your access remains available during the grace period.');
        } elseif ($event === 'invoice.update') {
            $invoiceStatus = strtolower((string) data_get($data, 'status'));
            $this->recordInvoice($subscription, $data, in_array($invoiceStatus, ['success', 'paid'], true) ? 'paid' : $invoiceStatus);
        }

        return true;
    }

    public function cancel(Subscription $subscription, PaystackService $paystack): void
    {
        if (! $subscription->provider_subscription_code || ! $subscription->provider_email_token) {
            throw new RuntimeException('Paystack is still preparing this subscription. Try again shortly.');
        }
        $paystack->disableSubscription($subscription->provider_subscription_code, $subscription->provider_email_token);
        $subscription->update(['status' => Subscription::STATUS_NON_RENEWING, 'cancel_at_period_end' => true, 'cancelled_at' => now()]);
    }

    public function reactivate(Subscription $subscription, PaystackService $paystack): void
    {
        if (! $subscription->provider_subscription_code || ! $subscription->provider_email_token || $subscription->current_period_end?->isPast()) {
            throw new RuntimeException('This subscription cannot be reactivated. Start a new checkout instead.');
        }
        $paystack->enableSubscription($subscription->provider_subscription_code, $subscription->provider_email_token);
        $subscription->update(['status' => Subscription::STATUS_ACTIVE, 'cancel_at_period_end' => false, 'cancelled_at' => null]);
    }

    public function stopOlderRenewals(Subscription $current, PaystackService $paystack): void
    {
        Subscription::query()->where('user_id', $current->user_id)->where('id', '!=', $current->id)
            ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_NON_RENEWING, Subscription::STATUS_PAST_DUE])
            ->get()->each(function (Subscription $older) use ($paystack): void {
                if (! $older->provider_subscription_code || ! $older->provider_email_token) return;
                try {
                    $paystack->disableSubscription($older->provider_subscription_code, $older->provider_email_token);
                    $older->update(['status' => Subscription::STATUS_NON_RENEWING, 'cancel_at_period_end' => true, 'cancelled_at' => now()]);
                } catch (\Throwable $exception) {
                    Log::error('Older subscription could not be disabled after plan change', ['subscription_id' => $older->id, 'message' => $exception->getMessage()]);
                    $this->notify($older->user, 'Billing action required', 'Your previous subscription could not be cancelled automatically. Contact support to prevent a second renewal.');
                }
            });
    }

    public function expireStale(): int
    {
        return Subscription::query()
            ->where(function ($query) {
                $query->where(fn ($q) => $q->where('status', Subscription::STATUS_PAST_DUE)->where('grace_ends_at', '<=', now()))
                    ->orWhere(fn ($q) => $q->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_NON_RENEWING])->where('current_period_end', '<=', now()));
            })
            ->get()
            ->each(function (Subscription $subscription): void {
                $subscription->update(['status' => Subscription::STATUS_EXPIRED]);
                $subscription->user()->update(['plan_ends_at' => now()]);
            })->count();
    }

    public function grantDuePlacements(): int
    {
        $count = 0;
        Subscription::query()->where('status', Subscription::STATUS_ACTIVE)->with('user.products')->get()->each(function ($subscription) use (&$count) {
            $before = FeaturedPlacement::query()->count();
            $this->grantMonthlyPlacement($subscription);
            $count += FeaturedPlacement::query()->count() > $before ? 1 : 0;
        });
        return $count;
    }

    private function grantMonthlyPlacement(Subscription $subscription): void
    {
        $days = match ($subscription->plan_type) { User::PLAN_GROWTH => 3, User::PLAN_ENTERPRISE => 7, default => 0 };
        if ($days === 0) return;
        $product = $subscription->user->products()->where('is_active', true)->whereNotIn('status', ['unavailable', 'trashed'])->latest()->first();
        if (! $product) return;
        $alreadyGranted = FeaturedPlacement::query()->where('user_id', $subscription->user_id)->where('source', 'package')->whereBetween('starts_at', [now()->startOfMonth(), now()->endOfMonth()])->exists();
        if ($alreadyGranted) return;
        FeaturedPlacement::create(['user_id' => $subscription->user_id, 'product_id' => $product->id, 'placement' => 'marketplace_home', 'source' => 'package', 'label' => 'Plan feature', 'starts_at' => now(), 'ends_at' => now()->addDays($days)]);
    }

    private function recordInvoice(Subscription $subscription, array $data, string $status): void
    {
        $invoiceCode = (string) (data_get($data, 'invoice_code') ?? data_get($data, 'invoice.invoice_code') ?? '');
        $reference = (string) (data_get($data, 'reference') ?? data_get($data, 'transaction.reference') ?? '');
        $attributes = $invoiceCode !== '' ? ['provider_invoice_code' => $invoiceCode] : ($reference !== '' ? ['reference' => $reference] : ['subscription_id' => $subscription->id, 'created_at' => now()]);
        SubscriptionInvoice::updateOrCreate($attributes, [
            'subscription_id' => $subscription->id, 'user_id' => $subscription->user_id, 'provider' => 'paystack',
            'reference' => $reference ?: null, 'status' => $status,
            'amount' => Money::fromMinor((string) (data_get($data, 'amount') ?? Money::toMinor((string) $subscription->amount))),
            'currency' => strtoupper((string) data_get($data, 'currency', $subscription->currency)),
            'due_at' => data_get($data, 'due_date') ? Carbon::parse(data_get($data, 'due_date')) : null,
            'paid_at' => $status === 'paid' ? Carbon::parse(data_get($data, 'paid_at') ?: now()) : null,
            'failed_at' => $status === 'failed' ? now() : null,
            'metadata' => Arr::only($data, ['status', 'description']),
        ]);
    }

    private function subscriptionCode(array $data): string
    {
        return (string) (data_get($data, 'subscription_code') ?? data_get($data, 'subscription.subscription_code') ?? data_get($data, 'subscription.code') ?? (is_string(data_get($data, 'subscription')) ? data_get($data, 'subscription') : ''));
    }

    private function nextPaymentDate(array $data): ?Carbon
    {
        $value = data_get($data, 'next_payment_date') ?? data_get($data, 'subscription.next_payment_date');
        return $value ? Carbon::parse($value) : null;
    }

    private function notify(User $user, string $title, string $body): void
    {
        try { app(SellerNotifier::class)->notify($user, SellerNotification::TYPE_ADMIN_MESSAGE, $title, $body, ['billing' => true]); }
        catch (\Throwable $exception) { Log::warning('Subscription notification failed', ['user_id' => $user->id, 'message' => $exception->getMessage()]); }
    }
}
