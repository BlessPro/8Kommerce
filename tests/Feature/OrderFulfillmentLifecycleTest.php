<?php

namespace Tests\Feature;

use App\Jobs\SendOutboundMessage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class OrderFulfillmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_move_paid_order_through_fulfillment_stages(): void
    {
        Queue::fake();
        [$seller, $order] = $this->paidOrder();

        $this->asSeller($seller)->post(route('notifications.orders.accept', $order))->assertSessionHas('status', 'order-updated');
        $this->assertSame(Order::STATUS_ACCEPTED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->accepted_at);
        Queue::assertPushed(SendOutboundMessage::class, fn (SendOutboundMessage $job) => $job->phone === $order->customer_phone && str_contains($job->message, 'has been accepted'));

        $this->asSeller($seller)->post(route('notifications.orders.processing', $order))->assertSessionHas('status', 'order-updated');
        $this->assertSame(Order::STATUS_PROCESSING, $order->fresh()->status);

        $this->asSeller($seller)->post(route('notifications.orders.ready', $order))->assertSessionHas('status', 'order-updated');
        $this->assertSame(Order::STATUS_READY, $order->fresh()->status);

        $this->asSeller($seller)->post(route('notifications.orders.shipped', $order))->assertSessionHas('status', 'order-updated');
        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->status);
        Queue::assertPushed(SendOutboundMessage::class, fn (SendOutboundMessage $job) => str_contains($job->message, 'on the way'));

        $this->asSeller($seller)->post(route('notifications.orders.delivered', $order))->assertSessionHas('status', 'order-updated');
        $this->assertSame(Order::STATUS_DELIVERED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->delivered_at);
        $this->assertDatabaseCount('order_feedback_tokens', 1);
    }

    public function test_seller_can_bulk_accept_owned_paid_orders_and_queue_customer_sms(): void
    {
        Queue::fake();
        [$seller, $first] = $this->paidOrder();
        $second = $first->replicate(['reference', 'accepted_at']);
        $second->reference = 'ORDER-'.fake()->uuid();
        $second->status = Order::STATUS_PAID;
        $second->save();

        $this->asSeller($seller)
            ->post(route('notifications.orders.bulk-accept'), ['order_ids' => [$first->id, $second->id]])
            ->assertSessionHas('status', 'orders-bulk-accepted')
            ->assertSessionHas('accepted_count', 2);

        $this->assertSame(Order::STATUS_ACCEPTED, $first->fresh()->status);
        $this->assertSame(Order::STATUS_ACCEPTED, $second->fresh()->status);
        $customerMessages = Queue::pushed(SendOutboundMessage::class, fn (SendOutboundMessage $job) => $job->phone === $first->customer_phone && str_contains($job->message, 'has been accepted'));
        $this->assertCount(2, $customerMessages);
    }

    public function test_order_worklist_exposes_bulk_acceptance_and_delivery_exception_controls(): void
    {
        [$seller, $order] = $this->paidOrder();

        $this->asSeller($seller)->get(route('products.orders'))
            ->assertOk()
            ->assertSee('Accept selected')
            ->assertSee('data-order-bulk-checkbox', false)
            ->assertSee('Delivery area: Accra');

        $order->update(['status' => Order::STATUS_SHIPPED, 'shipped_at' => now()]);
        $this->asSeller($seller)->get(route('products.orders'))
            ->assertOk()
            ->assertSee('Delivery problem or return')
            ->assertSee('Return and request refund');
    }

    public function test_failed_delivery_can_be_rescheduled_then_returned_with_products_only_refund(): void
    {
        Queue::fake();
        [$seller, $order, $payment] = $this->paidOrder();
        $order->update([
            'status' => Order::STATUS_SHIPPED,
            'subtotal' => '100.00',
            'fulfillment_fee' => '15.00',
            'total' => '115.00',
            'shipped_at' => now(),
        ]);
        $payment->update(['amount' => '115.00']);

        $this->asSeller($seller)->post(route('notifications.orders.delivery-attempt', $order), [
            'failure_reason' => 'customer_unavailable',
            'failure_details' => 'Phone was off.',
            'rescheduled_for' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertSessionHas('status', 'order-updated');

        $order->refresh();
        $this->assertSame(Order::STATUS_DELIVERY_RESCHEDULED, $order->status);
        $this->assertNotNull($order->delivery_attempted_at);
        $this->assertNotNull($order->rescheduled_for);

        $this->asSeller($seller)->post(route('notifications.orders.returned', $order), [
            'return_reason' => 'customer_unavailable',
            'return_details' => 'Customer asked to cancel after the attempt.',
        ])->assertSessionHas('status', 'order-updated');

        $order->refresh();
        $payment->refresh();
        $this->assertSame(Order::STATUS_RETURNED, $order->status);
        $this->assertSame(Order::REFUND_REQUESTED, $order->refund_status);
        $this->assertSame('100.00', (string) $order->refund_requested_amount);
        $this->assertSame('customer_unavailable', $order->refund_reason);
        $this->assertFalse((bool) data_get($payment->raw_payload, 'refund_delivery_fee_included'));
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
        $this->assertSame('100.00', (string) $order->refund_requested_amount);
        $this->assertSame('seller_cannot_fulfill', $order->refund_reason);
        $this->assertNotNull($order->cancelled_at);
        $this->assertTrue((bool) data_get($payment->raw_payload, 'refund_requested'));
        $this->assertSame('seller_cannot_fulfill', data_get($payment->raw_payload, 'refund_reason'));
    }

    public function test_admin_can_approve_the_exact_delivery_refund_request(): void
    {
        Queue::fake();
        [, $order, $payment] = $this->paidOrder();
        $order->update([
            'status' => Order::STATUS_RETURNED,
            'refund_status' => Order::REFUND_REQUESTED,
            'refund_requested_amount' => '85.00',
            'refund_reason' => 'customer_unavailable',
        ]);
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'refund-admin@8kommerce.test']);
        config(['admin.allowed_emails' => ['refund-admin@8kommerce.test']]);

        $paystack = Mockery::mock(PaystackService::class);
        $paystack->shouldReceive('createRefund')->once()->with($payment->reference, '85.00')->andReturn(['id' => 'refund_123']);
        $this->app->instance(PaystackService::class, $paystack);

        $this->actingAs($admin)->post(route('admin.order-refunds.approve', $order), [
            'admin_note' => 'Validated against the delivery attempt.',
        ])->assertSessionHas('status', 'order-refund-approved');

        $this->assertSame(Order::STATUS_REFUNDED, $order->fresh()->status);
        $this->assertSame(Order::REFUND_COMPLETED, $order->fresh()->refund_status);
        $this->assertSame('85.00', data_get($payment->fresh()->raw_payload, 'refund.amount'));
    }

    public function test_seller_fault_return_includes_the_delivery_fee_in_refund_request(): void
    {
        Queue::fake();
        [$seller, $order, $payment] = $this->paidOrder();
        $order->update(['status' => Order::STATUS_SHIPPED, 'subtotal' => '100.00', 'fulfillment_fee' => '15.00', 'total' => '115.00']);
        $payment->update(['amount' => '115.00']);

        $this->asSeller($seller)->post(route('notifications.orders.returned', $order), [
            'return_reason' => 'seller_fault',
            'return_details' => 'Courier damaged the parcel.',
        ])->assertSessionHas('status', 'order-updated');

        $this->assertSame('115.00', (string) $order->fresh()->refund_requested_amount);
        $this->assertTrue((bool) data_get($payment->fresh()->raw_payload, 'refund_delivery_fee_included'));
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
            'fulfillment_method' => 'option_0',
            'fulfillment_label' => 'Delivery area: Accra',
            'fulfillment_fee' => '0.00',
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
