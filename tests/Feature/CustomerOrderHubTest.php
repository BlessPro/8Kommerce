<?php

namespace Tests\Feature;

use App\Jobs\SendCustomerTrackingOtp;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CustomerOrderHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_use_phone_and_otp_to_see_single_and_bulk_orders(): void
    {
        $phone = '+233541900228';
        $first = $this->orderFor($phone, 'PAID-100', ['Canvas bag']);
        $second = $this->orderFor($phone, 'BULK-200', ['T-shirt', 'Cap']);
        $this->putOtp($phone, '482951');

        $this->post(route('customer.orders.verify'), [
            'phone_number' => '0541900228',
            'otp' => '482951',
            'remember' => '1',
        ])->assertRedirect(route('customer.orders.index'));
        $this->assertDatabaseCount('customer_tracking_sessions', 1);

        $this->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('PAID-100')
            ->assertSee('BULK-200')
            ->assertSee('T-shirt')
            ->assertSee('Cap');

        $this->get(route('customer.orders.show', $second))
            ->assertOk()
            ->assertSee('Products')
            ->assertSee('T-shirt')
            ->assertSee('follows the order status');

        $this->assertNotNull($first);
    }

    public function test_customer_cannot_open_an_order_belonging_to_another_phone(): void
    {
        $ownOrder = $this->orderFor('+233541900228', 'OWN-100', ['Bag']);
        $otherOrder = $this->orderFor('+233541900229', 'OTHER-100', ['Shoes']);
        $this->putOtp($ownOrder->customer_phone, '482951');

        $this->post(route('customer.orders.verify'), ['phone_number' => $ownOrder->customer_phone, 'otp' => '482951']);
        $this->get(route('customer.orders.show', $otherOrder))->assertNotFound();
    }

    public function test_otp_is_single_use_and_five_wrong_attempts_block_verification(): void
    {
        $phone = '+233541900228';
        $this->orderFor($phone, 'LOCK-100', ['Bag']);
        $this->putOtp($phone, '482951');

        foreach (range(1, 5) as $attempt) {
            $this->post(route('customer.orders.verify'), ['phone_number' => $phone, 'otp' => '000000'])
                ->assertSessionHasErrors('credentials');
        }

        $this->post(route('customer.orders.verify'), ['phone_number' => $phone, 'otp' => '482951'])
            ->assertSessionHasErrors('credentials');
    }

    public function test_successful_otp_cannot_be_used_twice(): void
    {
        $phone = '+233541900228';
        $this->orderFor($phone, 'ONCE-100', ['Bag']);
        $this->putOtp($phone, '482951');

        $this->post(route('customer.orders.verify'), [
            'phone_number' => $phone,
            'otp' => '482951',
            'remember' => '0',
        ])->assertRedirect(route('customer.orders.index'));

        $this->post(route('customer.orders.logout'));

        $this->post(route('customer.orders.verify'), [
            'phone_number' => $phone,
            'otp' => '482951',
            'remember' => '0',
        ])->assertSessionHasErrors('credentials');
    }

    public function test_otp_request_is_generic_and_queues_delivery_for_known_phone(): void
    {
        Queue::fake();
        $phone = '+233541900228';
        $this->orderFor($phone, 'OTP-100', ['Bag']);
        $message = 'If that number has orders, a verification code will arrive shortly.';

        $this->post(route('customer.orders.send-otp'), ['phone_number' => '0541900228'])
            ->assertSessionHas('tracking-otp-sent', $message);

        Queue::assertPushed(SendCustomerTrackingOtp::class, fn ($job) => $job->phone === $phone);

        $this->post(route('customer.orders.send-otp'), ['phone_number' => '0541900200'])
            ->assertSessionHas('tracking-otp-sent', $message);
    }

    private function orderFor(string $phone, string $reference, array $items): Order
    {
        $seller = User::factory()->create();
        SellerProfile::create([
            'user_id' => $seller->id,
            'business_name' => 'Test Store',
            'public_slug' => 'store-'.$seller->id,
            'phone' => '+233541900220',
        ]);

        $order = Order::create([
            'user_id' => $seller->id,
            'reference' => $reference,
            'status' => Order::STATUS_PAID,
            'payment_status' => 'SUCCESS',
            'customer_name' => 'Customer',
            'customer_phone' => $phone,
            'customer_location' => 'Accra',
            'delivery_required' => true,
            'fulfillment_type' => Order::FULFILLMENT_DELIVERY,
            'subtotal' => '100.00',
            'total' => '100.00',
            'currency' => 'GHS',
            'paid_at' => now(),
        ]);

        foreach ($items as $name) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_name' => $name,
                'unit_price' => '50.00',
                'quantity' => 1,
                'line_total' => '50.00',
            ]);
        }

        return $order;
    }

    private function putOtp(string $phone, string $code): void
    {
        Cache::put('customer_tracking_otp:'.sha1($phone), hash('sha256', $code), 300);
    }
}
