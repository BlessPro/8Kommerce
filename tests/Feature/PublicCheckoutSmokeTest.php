<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class PublicCheckoutSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_checkout_initializes_payment_and_keeps_cart_until_payment_is_verified(): void
    {
        $seller = User::factory()->create([
            'plan_type' => User::PLAN_FREE_TRIAL,
        ]);

        $profile = SellerProfile::create([
            'user_id' => $seller->id,
            'business_name' => 'Smoke Seller',
            'public_slug' => 'smoke-seller',
            'paystack_subaccount_code' => 'ACCT_test_sub',
        ]);

        $product = Product::create([
            'user_id' => $seller->id,
            'name' => 'Smoke Product',
            'price' => '55.00',
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
        ]);

        $paystackMock = Mockery::mock(PaystackService::class);
        $paystackMock->shouldReceive('platformChargeFor')
            ->once()
            ->with('110.00', Mockery::on(fn ($user) => $user->is($seller)))
            ->andReturn('0.00');
        $paystackMock->shouldReceive('initializeTransaction')
            ->once()
            ->andReturn([
                'authorization_url' => 'https://paystack.test/checkout',
            ]);
        $this->app->instance(PaystackService::class, $paystackMock);

        $cartKey = 'public_cart:'.$profile->public_slug;
        Session::put($cartKey, [
            $product->id => [
                'product_id' => $product->id,
                'quantity' => 2,
            ],
        ]);

        $response = $this->post(route('public.cart.checkout', $profile->public_slug), [
            'phone_number' => '0541900229',
            'phone_country' => '+233',
            'idempotency_key' => (string) Str::uuid(),
        ]);

        $response->assertRedirect('https://paystack.test/checkout');

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(Order::STATUS_PENDING_PAYMENT, $order->status);
        $this->assertSame(Payment::STATUS_PENDING, $order->payment_status);
        $this->assertSame('110.00', $order->total);

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame((string) $order->id, (string) $payment->order_id);
        $this->assertSame('110.00', (string) $payment->amount);

        $orderItem = OrderItem::query()->first();
        $this->assertNotNull($orderItem);
        $this->assertSame($product->id, $orderItem->product_id);
        $this->assertSame(2, $orderItem->quantity);

        $response->assertSessionHas($cartKey);
    }

    public function test_cart_checkout_adds_the_selected_seller_delivery_fee(): void
    {
        $seller = User::factory()->create(['plan_type' => User::PLAN_FREE_TRIAL]);
        $profile = SellerProfile::create([
            'user_id' => $seller->id,
            'business_name' => 'Delivery Seller',
            'public_slug' => 'delivery-seller',
            'paystack_subaccount_code' => 'ACCT_delivery_sub',
            'storefront_settings' => [
                'fulfillment_options' => [
                    ['type' => 'delivery', 'label' => 'Madina and Adenta', 'fee' => '15.00', 'details' => 'Same-day delivery.'],
                ],
            ],
        ]);
        $product = Product::create([
            'user_id' => $seller->id,
            'name' => 'Delivered Product',
            'price' => '55.00',
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
        ]);

        $paystackMock = Mockery::mock(PaystackService::class);
        $paystackMock->shouldReceive('platformChargeFor')->once()->with('125.00', Mockery::on(fn ($user) => $user->is($seller)))->andReturn('0.00');
        $paystackMock->shouldReceive('initializeTransaction')->once()->withArgs(fn ($amount) => $amount === '125.00')->andReturn([
            'authorization_url' => 'https://paystack.test/delivery-checkout',
        ]);
        $this->app->instance(PaystackService::class, $paystackMock);

        Session::put('public_cart:'.$profile->public_slug, [
            $product->id => ['product_id' => $product->id, 'quantity' => 2],
        ]);

        $this->post(route('public.cart.checkout', $profile->public_slug), [
            'phone_number' => '0541900229',
            'phone_country' => '+233',
            'fulfillment_method' => 'option_0',
            'location' => 'Osu, Accra',
            'idempotency_key' => (string) Str::uuid(),
        ])->assertRedirect('https://paystack.test/delivery-checkout');

        $order = Order::query()->firstOrFail();
        $payment = Payment::query()->firstOrFail();
        $this->assertSame('110.00', (string) $order->subtotal);
        $this->assertSame('125.00', (string) $order->total);
        $this->assertTrue($order->delivery_required);
        $this->assertSame(Order::FULFILLMENT_DELIVERY, $order->fulfillment_type);
        $this->assertSame('15.00', data_get($payment->raw_payload, 'order.fulfillment_fee'));
        $this->assertSame('option_0', data_get($payment->raw_payload, 'order.fulfillment_method'));
    }
}
