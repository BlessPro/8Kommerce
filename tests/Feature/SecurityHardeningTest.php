<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\SmsOtpService;
use App\Support\Csv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_paystack_signature_is_rejected_before_payload_is_stored(): void
    {
        config()->set('services.paystack.secret_key', 'known-secret');

        $this->withHeader('X-Paystack-Signature', 'invalid')
            ->postJson('/webhooks/paystack', [
                'event' => 'charge.success',
                'data' => ['reference' => 'ATTACKER-REF'],
            ])
            ->assertUnauthorized();

        $this->assertDatabaseCount('webhook_events', 0);
    }

    public function test_invalid_twilio_signature_is_rejected(): void
    {
        config()->set('services.twilio.auth_token', 'known-token');
        config()->set('services.twilio.status_callback_url', 'https://example.test/webhooks/twilio/status');

        $this->post('/webhooks/twilio/status', [
            'MessageSid' => 'SM123',
            'MessageStatus' => 'delivered',
        ], ['X-Twilio-Signature' => 'invalid'])->assertForbidden();
    }

    public function test_phone_otp_request_does_not_reveal_whether_an_account_exists(): void
    {
        config()->set('auth_phone.enabled', true);
        $otp = Mockery::mock(SmsOtpService::class);
        $otp->shouldNotReceive('sendOtp');
        $this->app->instance(SmsOtpService::class, $otp);

        $this->post(route('login.phone.send'), [
            'phone_country' => '+233',
            'phone_number' => '0541999999',
        ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('otp_status', 'sent');
    }

    public function test_provider_amount_mismatch_cannot_complete_payment(): void
    {
        $user = User::factory()->create();
        $payment = Payment::create([
            'user_id' => $user->id,
            'reference' => 'VERIFY-001',
            'amount' => '50.00',
            'status' => Payment::STATUS_PENDING,
        ]);

        try {
            app(PaymentService::class)->markSuccess($payment, [
                'status' => 'success',
                'reference' => $payment->reference,
                'amount' => 1,
                'currency' => 'GHS',
                'channel' => 'card',
            ]);
            $this->fail('A mismatched provider amount was accepted.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('amount', $exception->getMessage());
        }

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_manual_plan_activation_is_not_available_to_sellers(): void
    {
        config()->set('billing.manual_activation_enabled', false);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('billing.activate', User::PLAN_GROWTH))
            ->assertNotFound();

        $this->assertNull($user->fresh()->plan_type);
    }

    public function test_seller_cannot_edit_another_sellers_product(): void
    {
        $seller = User::factory()->create([
            'pin_hash' => Hash::make('482951'),
            'plan_type' => User::PLAN_FREE_TRIAL,
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDay(),
        ]);
        SellerProfile::create([
            'user_id' => $seller->id,
            'business_name' => 'Seller A',
            'phone' => '+233541111111',
            'public_slug' => 'seller-a',
        ]);

        $otherSeller = User::factory()->create();
        $product = Product::create([
            'user_id' => $otherSeller->id,
            'name' => 'Private Product',
            'price' => '20.00',
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
            'stock_quantity' => 3,
            'low_stock_threshold' => 1,
        ]);

        $this->actingAs($seller)
            ->get(route('products.edit', $product))
            ->assertForbidden();
    }

    public function test_browser_security_headers_are_present(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy', "base-uri 'self'; object-src 'none'; frame-ancestors 'none'");
    }

    public function test_payout_changes_require_the_current_pin(): void
    {
        $seller = User::factory()->create([
            'pin_hash' => Hash::make('482951'),
            'plan_type' => User::PLAN_FREE_TRIAL,
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDay(),
        ]);
        SellerProfile::create([
            'user_id' => $seller->id,
            'business_name' => 'Protected Payout Seller',
            'phone' => '+233541111111',
            'public_slug' => 'protected-payout-seller',
            'payout_method' => 'MOMO',
            'settlement_bank_code' => 'MTN',
            'account_number' => '0541111111',
            'account_name' => 'Seller',
        ]);

        $this->actingAs($seller)
            ->from('/profile')
            ->put(route('profile.seller.update'), [
                'business_name' => 'Protected Payout Seller',
                'phone_country' => '+233',
                'phone_number' => '0541111111',
                'payout_method' => 'MOMO',
                'settlement_bank_code' => 'MTN',
                'account_number' => '0549999999',
                'account_name' => 'Seller',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHasErrors('current_pin');

        $this->assertSame('0541111111', $seller->sellerProfile->fresh()->account_number);
    }

    public function test_csv_values_cannot_become_spreadsheet_formulas(): void
    {
        $this->assertSame("'=HYPERLINK(\"https://evil.test\")", Csv::cell('=HYPERLINK("https://evil.test")'));
        $this->assertSame("' +SUM(1,1)", Csv::cell(' +SUM(1,1)'));
        $this->assertSame('Normal value', Csv::cell('Normal value'));
    }
}
