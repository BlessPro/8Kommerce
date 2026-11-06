<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use App\Services\PaystackService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
