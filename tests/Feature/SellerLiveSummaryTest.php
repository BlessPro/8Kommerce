<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SellerLiveSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_receives_current_summary_and_etag_support(): void
    {
        $seller = User::factory()->create([
            'phone' => '+233201234590',
            'phone_verified_at' => now(),
            'pin_hash' => Hash::make('2486'),
            'plan_type' => User::PLAN_FREE_TRIAL,
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays(10),
        ]);
        SellerProfile::create([
            'user_id' => $seller->id,
            'business_name' => 'Live Store',
            'phone' => $seller->phone,
            'public_slug' => 'live-store',
            'paystack_subaccount_code' => 'ACCT_live',
        ]);
        Payment::create([
            'user_id' => $seller->id,
            'reference' => 'LIVE-PAYMENT-1',
            'amount' => '125.00',
            'status' => Payment::STATUS_SUCCESS,
            'paid_at' => now(),
        ]);
        Order::create([
            'user_id' => $seller->id,
            'reference' => 'LIVE-ORDER-1',
            'status' => Order::STATUS_PAID,
            'payment_status' => Payment::STATUS_SUCCESS,
            'customer_name' => 'Ama Customer',
            'customer_phone' => '+233541900229',
            'total' => '125.00',
        ]);
        AnalyticsEvent::create([
            'user_id' => $seller->id,
            'event_type' => AnalyticsEvent::TYPE_LISTING_VIEW,
        ]);

        $response = $this->actingAs($seller)->getJson(route('seller.live-summary'));
        $response->assertOk()
            ->assertJsonPath('values.amount_received', 'GHS 125.00')
            ->assertJsonPath('values.orders_count', '1')
            ->assertJsonPath('values.traffic_count', '1')
            ->assertJsonPath('values.conversion_rate', '100.0%')
            ->assertJsonPath('values.new_customers', '1 new customers');

        $etag = $response->headers->get('ETag');
        $this->actingAs($seller)
            ->withHeader('If-None-Match', $etag)
            ->getJson(route('seller.live-summary'))
            ->assertStatus(304);
    }

    public function test_live_summary_requires_authentication(): void
    {
        $this->getJson(route('seller.live-summary'))->assertUnauthorized();
    }
}
