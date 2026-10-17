<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderFulfillmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_move_paid_order_through_fulfillment_stages(): void
    {
        [$seller, $order] = $this->paidOrder();

        $this->asSeller($seller)->post(route('notifications.orders.accept', $order))->assertSessionHas('status', 'order-updated');
        $this->assertSame(Order::STATUS_ACCEPTED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->accepted_at);

        $this->asSeller($seller)->post(route('notifications.orders.processing', $order))->assertSessionHas('status', 'order-updated');
        $this->assertSame(Order::STATUS_PROCESSING, $order->fresh()->status);

        $this->asSeller($seller)->post(route('notifications.orders.ready', $order))->assertSessionHas('status', 'order-updated');
        $this->assertSame(Order::STATUS_READY, $order->fresh()->status);

        $this->asSeller($seller)->post(route('notifications.orders.shipped', $order))->assertSessionHas('status', 'order-updated');
        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->status);

        $this->asSeller($seller)->post(route('notifications.orders.delivered', $order))->assertSessionHas('status', 'order-updated');
        $this->assertSame(Order::STATUS_DELIVERED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->delivered_at);
        $this->assertDatabaseCount('order_feedback_tokens', 1);
    }

    public function test_cancelling_paid_order_creates_refund_request_without_calling_paystack_automatically(): void
    {
        [$seller, $order, $payment] = $this->paidOrder();

        $this->asSeller($seller)
            ->post(route('notifications.orders.reject', $order))
            ->assertSessionHas('status', 'order-updated');

        $order->refresh();
        $payment->refresh();

        $this->assertSame(Order::STATUS_CANNOT_FULFILL, $order->status);
        $this->assertSame(Order::REFUND_REQUESTED, $order->refund_status);
        $this->assertNotNull($order->cancelled_at);
        $this->assertTrue((bool) data_get($payment->raw_payload, 'refund_requested'));
        $this->assertSame('seller_cannot_fulfill', data_get($payment->raw_payload, 'refund_reason'));
    }

    private function paidOrder(): array
    {
        $seller = User::factory()->create([
            'plan_type' => User::PLAN_FREE_TRIAL,
            'pin_hash' => Hash::make('2486'),
        ]);

        SellerProfile::create([
            'user_id' => $seller->id,
            'business_name' => 'Lifecycle Seller',
            'public_slug' => 'lifecycle-seller-'.$seller->id,
            'phone' => '+233541900229',
            'paystack_subaccount_code' => 'ACCT_test_sub',
        ]);

        $order = Order::create([
            'user_id' => $seller->id,
            'reference' => 'ORDER-'.fake()->uuid(),
            'status' => Order::STATUS_PAID,
            'payment_status' => Payment::STATUS_SUCCESS,
            'customer_phone' => '+233541900228',
            'customer_location' => 'Accra',
            'delivery_required' => true,
            'fulfillment_type' => Order::FULFILLMENT_DELIVERY,
            'subtotal' => '100.00',
            'total' => '100.00',
            'currency' => 'GHS',
            'paid_at' => now(),
        ]);

        $payment = Payment::create([
            'user_id' => $seller->id,
            'order_id' => $order->id,
            'reference' => $order->reference,
            'amount' => '100.00',
            'status' => Payment::STATUS_SUCCESS,
            'paid_at' => now(),
            'raw_payload' => ['customer' => ['phone' => $order->customer_phone]],
        ]);

        return [$seller, $order, $payment];
    }

    private function asSeller(User $seller): static
    {
        return $this->actingAs($seller)
            ->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    }
}
