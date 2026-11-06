<?php

namespace Tests\Feature;

use App\Jobs\SendOutboundMessage;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PaystackService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class SubscriptionBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_start_a_configured_subscription_checkout(): void
    {
        config()->set('billing.recurring_enabled', true);
        config()->set('billing.plan_codes.STARTER', 'PLN_starter');
        $user = User::factory()->create(['plan_type' => User::PLAN_FREE_TRIAL, 'trial_started_at' => now()->subDays(10), 'trial_ends_at' => now()->subDay()]);

        $paystack = Mockery::mock(PaystackService::class);
        $paystack->shouldReceive('initializeSubscriptionCheckout')->once()->withArgs(fn ($email, $code, $amount, $currency, $reference, $metadata) => $email === 'billing@example.com' && $code === 'PLN_starter' && $amount === '15.00' && $currency === 'GHS' && str_starts_with($reference, 'SUB-') && $metadata['plan_type'] === User::PLAN_STARTER)->andReturn(['authorization_url' => 'https://checkout.paystack.test/subscription']);
        $this->app->instance(PaystackService::class, $paystack);

        $this->actingAs($user)->post(route('billing.checkout', User::PLAN_STARTER), ['billing_email' => 'billing@example.com'])
            ->assertRedirect('https://checkout.paystack.test/subscription');

        $this->assertDatabaseHas('subscriptions', ['user_id' => $user->id, 'plan_type' => User::PLAN_STARTER, 'status' => Subscription::STATUS_PENDING]);
    }

    public function test_unconfigured_plan_returns_a_clear_error_instead_of_failing(): void
    {
        config()->set('billing.recurring_enabled', true);
        config()->set('billing.plan_codes.STARTER', null);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('billing.show'))
            ->post(route('billing.checkout', User::PLAN_STARTER), ['billing_email' => 'billing@example.com'])
            ->assertRedirect(route('billing.show'))
            ->assertSessionHasErrors(['billing' => 'This subscription plan is not available yet. Please choose another plan or contact support.']);

        $this->assertDatabaseMissing('subscriptions', ['user_id' => $user->id]);
    }

    public function test_subscription_checkout_sends_plan_amount_currency_and_card_channel(): void
    {
        config()->set('services.paystack.secret_key', 'sk_test_checkout');
        config()->set('billing.callback_url', 'https://app.8kommerce.test/billing/callback');
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.test/subscription'],
            ]),
        ]);

        app(PaystackService::class)->initializeSubscriptionCheckout(
            'billing@example.com',
            'PLN_starter',
            '15.00',
            'GHS',
            'SUB-CHECKOUT-1',
            ['plan_type' => User::PLAN_STARTER]
        );

        Http::assertSent(fn ($request) => $request->url() === 'https://api.paystack.co/transaction/initialize'
            && $request['email'] === 'billing@example.com'
            && $request['plan'] === 'PLN_starter'
            && $request['amount'] === 1500
            && $request['currency'] === 'GHS'
            && $request['channels'] === ['card']
            && $request['callback_url'] === 'https://app.8kommerce.test/billing/callback');
    }

    public function test_provider_plan_mismatch_returns_an_actionable_error(): void
    {
        config()->set('billing.recurring_enabled', true);
        config()->set('billing.plan_codes.STARTER', 'PLN_wrong_environment');
        config()->set('services.paystack.secret_key', 'sk_test_checkout');
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => false,
                'message' => 'Plan not found',
            ], 404),
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('billing.show'))
            ->post(route('billing.checkout', User::PLAN_STARTER), ['billing_email' => 'billing@example.com'])
            ->assertRedirect(route('billing.show'))
            ->assertSessionHasErrors([
                'billing' => 'This subscription plan is not available in the current payment environment. Please contact support.',
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_type' => User::PLAN_STARTER,
            'status' => Subscription::STATUS_EXPIRED,
        ]);
    }

    public function test_seller_can_start_a_manual_monthly_payment_without_a_paystack_plan_code(): void
    {
        config()->set('billing.manual_renewal_enabled', true);
        config()->set('billing.recurring_enabled', false);
        config()->set('billing.plan_codes.STARTER', null);
        config()->set('services.paystack.secret_key', 'sk_test_checkout');
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.test/manual-renewal'],
            ]),
        ]);
        $user = User::factory()->create(['plan_type' => User::PLAN_FREE_TRIAL]);

        $this->actingAs($user)
            ->post(route('billing.checkout', User::PLAN_STARTER), ['billing_email' => 'billing@example.com'])
            ->assertRedirect('https://checkout.paystack.test/manual-renewal');

        $subscription = $user->subscriptions()->firstOrFail();
        $this->assertTrue($subscription->usesManualRenewal());
        $this->assertNull($subscription->provider_plan_code);
        Http::assertSent(fn ($request) => $request['amount'] === 1500
            && $request['currency'] === 'GHS'
            && $request['metadata']['billing_mode'] === 'manual_renewal'
            && ! isset($request['plan'])
            && ! isset($request['channels']));
    }

    public function test_verified_manual_payment_activates_exactly_one_access_period(): void
    {
        config()->set('billing.manual_period_days', 30);
        Queue::fake();
        $paidAt = now()->startOfSecond();
        $user = User::factory()->create(['plan_type' => User::PLAN_FREE_TRIAL]);
        $subscription = app(SubscriptionService::class)->createManualPending($user, User::PLAN_GROWTH, 'billing@example.com');
        $paystack = Mockery::mock(PaystackService::class);
        $verification = ['data' => [
            'status' => 'success', 'reference' => $subscription->checkout_reference,
            'amount' => 3000, 'currency' => 'GHS', 'paid_at' => $paidAt->toIso8601String(),
            'metadata' => ['subscription_id' => $subscription->id, 'billing_mode' => 'manual_renewal'],
            'customer' => ['customer_code' => 'CUS_manual'],
        ]];
        $paystack->shouldReceive('verifyTransaction')->twice()->with($subscription->checkout_reference)->andReturn($verification);
        $this->app->instance(PaystackService::class, $paystack);

        $callback = route('billing.callback', ['reference' => $subscription->checkout_reference]);
        $this->actingAs($user)->get($callback)->assertRedirect(route('billing.show'));
        $firstPeriodEnd = $subscription->fresh()->current_period_end;
        $this->assertTrue($firstPeriodEnd->equalTo($paidAt->copy()->addDays(30)));

        $this->actingAs($user)->get($callback)->assertRedirect(route('billing.show'));
        $this->assertTrue($subscription->fresh()->current_period_end->equalTo($firstPeriodEnd));
        $this->assertDatabaseCount('subscription_invoices', 1);
    }

    public function test_early_manual_renewal_adds_days_after_current_access(): void
    {
        config()->set('billing.manual_period_days', 30);
        Queue::fake();
        $existingEnd = now()->addDays(8)->startOfSecond();
        $user = User::factory()->create([
            'plan_type' => User::PLAN_STARTER,
            'plan_ends_at' => $existingEnd,
        ]);
        $subscription = app(SubscriptionService::class)->createManualPending($user, User::PLAN_STARTER, 'billing@example.com');

        app(SubscriptionService::class)->activateFromCharge($subscription, [
            'status' => 'success',
            'reference' => $subscription->checkout_reference,
            'amount' => 1500,
            'currency' => 'GHS',
            'paid_at' => now()->toIso8601String(),
            'metadata' => ['subscription_id' => $subscription->id],
        ]);

        $this->assertTrue($subscription->fresh()->current_period_end->equalTo($existingEnd->copy()->addDays(30)));
        $this->assertTrue($user->fresh()->plan_ends_at->equalTo($existingEnd->copy()->addDays(30)));
    }

    public function test_active_manual_plan_can_be_renewed_from_billing_page(): void
    {
        config()->set('billing.manual_renewal_enabled', true);
        config()->set('billing.recurring_enabled', false);
        $user = User::factory()->create([
            'plan_type' => User::PLAN_STARTER,
            'plan_ends_at' => now()->addDays(10),
        ]);
        $user->subscriptions()->create([
            'plan_type' => User::PLAN_STARTER,
            'provider' => 'paystack',
            'checkout_reference' => 'SUB-MANUAL-ACTIVE',
            'billing_email' => 'billing@example.com',
            'status' => Subscription::STATUS_ACTIVE,
            'amount' => '15.00',
            'currency' => 'GHS',
            'interval' => 'monthly',
            'current_period_start' => now()->subDays(20),
            'current_period_end' => now()->addDays(10),
            'metadata' => ['billing_mode' => 'manual_renewal', 'access_days' => 30],
        ]);

        $this->actingAs($user)
            ->get(route('billing.show'))
            ->assertOk()
            ->assertSee('Manual renewal')
            ->assertSee('Renew securely')
            ->assertSee('Renewing early adds 30 days');
    }

    public function test_manual_renewal_reminders_are_sent_once_and_link_to_billing(): void
    {
        Queue::fake();
        $user = User::factory()->create([
            'plan_type' => User::PLAN_STARTER,
            'plan_ends_at' => now()->addDays(3),
            'phone' => '0240000000',
        ]);
        $subscription = $user->subscriptions()->create([
            'plan_type' => User::PLAN_STARTER,
            'provider' => 'paystack',
            'checkout_reference' => 'SUB-MANUAL-REMINDER',
            'billing_email' => 'billing@example.com',
            'status' => Subscription::STATUS_ACTIVE,
            'amount' => '15.00',
            'currency' => 'GHS',
            'interval' => 'monthly',
            'current_period_start' => now()->subDays(27),
            'current_period_end' => now()->addDays(3)->startOfDay(),
            'metadata' => ['billing_mode' => 'manual_renewal', 'access_days' => 30],
        ]);

        $service = app(SubscriptionService::class);
        $this->assertSame(1, $service->sendManualRenewalReminders());
        $this->assertSame(0, $service->sendManualRenewalReminders());
        $this->assertDatabaseCount('notifications', 1);
        Queue::assertPushed(SendOutboundMessage::class, fn ($job) => str_contains($job->message, route('billing.show')));
        $this->assertCount(1, data_get($subscription->fresh()->metadata, 'renewal_reminders', []));
    }

    public function test_expiring_an_old_payment_does_not_end_newer_access(): void
    {
        $futureEnd = now()->addDays(20)->startOfSecond();
        $user = User::factory()->create([
            'plan_type' => User::PLAN_GROWTH,
            'plan_ends_at' => $futureEnd,
        ]);
        $base = [
            'user_id' => $user->id,
            'plan_type' => User::PLAN_GROWTH,
            'provider' => 'paystack',
            'billing_email' => 'billing@example.com',
            'amount' => '30.00',
            'currency' => 'GHS',
            'interval' => 'monthly',
            'metadata' => ['billing_mode' => 'manual_renewal'],
        ];
        Subscription::create([...$base, 'checkout_reference' => 'SUB-OLD', 'status' => Subscription::STATUS_ACTIVE, 'current_period_end' => now()->subMinute()]);
        Subscription::create([...$base, 'checkout_reference' => 'SUB-NEW', 'status' => Subscription::STATUS_ACTIVE, 'current_period_end' => $futureEnd]);

        $this->assertSame(1, app(SubscriptionService::class)->expireStale());
        $this->assertTrue($user->fresh()->plan_ends_at->equalTo($futureEnd));
    }

    public function test_verified_callback_activates_plan_and_records_invoice(): void
    {
        $user = User::factory()->create(['plan_type' => User::PLAN_FREE_TRIAL, 'trial_started_at' => now()->subDays(10), 'trial_ends_at' => now()->subDay()]);
        $subscription = $user->subscriptions()->create([
            'plan_type' => User::PLAN_GROWTH, 'provider' => 'paystack', 'provider_plan_code' => 'PLN_growth',
            'checkout_reference' => 'SUB-CALLBACK-1', 'billing_email' => 'billing@example.com',
            'status' => Subscription::STATUS_PENDING, 'amount' => '30.00', 'currency' => 'GHS', 'interval' => 'monthly',
        ]);
        $paystack = Mockery::mock(PaystackService::class);
        $paystack->shouldReceive('verifyTransaction')->once()->with('SUB-CALLBACK-1')->andReturn(['data' => [
            'status' => 'success', 'reference' => 'SUB-CALLBACK-1', 'amount' => 3000, 'currency' => 'GHS',
            'paid_at' => now()->toIso8601String(), 'customer' => ['customer_code' => 'CUS_123'],
        ]]);
        $this->app->instance(PaystackService::class, $paystack);

        $this->actingAs($user)->get(route('billing.callback', ['reference' => 'SUB-CALLBACK-1']))
            ->assertRedirect(route('billing.show'));

        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->fresh()->status);
        $this->assertSame(User::PLAN_GROWTH, $user->fresh()->plan_type);
        $this->assertDatabaseHas('subscription_invoices', ['subscription_id' => $subscription->id, 'status' => 'paid']);
    }

    public function test_failed_renewal_starts_grace_period_from_signed_webhook(): void
    {
        config()->set('services.paystack.secret_key', 'test_secret');
        $user = User::factory()->create(['plan_type' => User::PLAN_STARTER, 'plan_started_at' => now()->subMonth(), 'plan_ends_at' => now()->addDay()]);
        $subscription = $user->subscriptions()->create([
            'plan_type' => User::PLAN_STARTER, 'provider' => 'paystack', 'provider_plan_code' => 'PLN_starter',
            'provider_subscription_code' => 'SUB_provider', 'checkout_reference' => 'SUB-LOCAL',
            'billing_email' => 'billing@example.com', 'status' => Subscription::STATUS_ACTIVE,
            'amount' => '15.00', 'currency' => 'GHS', 'interval' => 'monthly', 'current_period_end' => now()->addDay(),
        ]);
        $payload = json_encode(['event' => 'invoice.payment_failed', 'data' => ['subscription' => ['subscription_code' => 'SUB_provider'], 'invoice_code' => 'INV_failed', 'amount' => 1500, 'currency' => 'GHS']], JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha512', $payload, 'test_secret');

        $this->call('POST', '/webhooks/paystack', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYSTACK_SIGNATURE' => $signature], $payload)
            ->assertOk()->assertJson(['status' => 'ok']);

        $this->assertSame(Subscription::STATUS_PAST_DUE, $subscription->fresh()->status);
        $this->assertNotNull($subscription->fresh()->grace_ends_at);
        $this->assertTrue($user->fresh()->hasActivePlan());
    }

    public function test_commission_matches_the_active_tier(): void
    {
        $service = app(PaystackService::class);
        $starter = User::factory()->make(['plan_type' => User::PLAN_STARTER, 'plan_ends_at' => now()->addMonth()]);
        $growth = User::factory()->make(['plan_type' => User::PLAN_GROWTH, 'plan_ends_at' => now()->addMonth()]);
        $enterprise = User::factory()->make(['plan_type' => User::PLAN_ENTERPRISE, 'plan_ends_at' => now()->addMonth()]);

        $this->assertSame('1.90', $service->platformChargeFor('100.00', $starter));
        $this->assertSame('1.25', $service->platformChargeFor('100.00', $growth));
        $this->assertSame('1.00', $service->platformChargeFor('100.00', $enterprise));
    }
}
