<?php

namespace Tests\Feature;

use App\Exceptions\InventoryUnavailableException;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InventoryReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_prevents_a_second_checkout_from_claiming_the_same_stock(): void
    {
        [$seller, $product] = $this->sellerAndProduct(1);
        [$firstOrder, $firstPayment] = $this->orderAndPayment($seller, $product, 'FIRST');
        [$secondOrder, $secondPayment] = $this->orderAndPayment($seller, $product, 'SECOND');

        $inventory = app(InventoryService::class);
        $inventory->reserveForOrder($firstOrder, $firstPayment);

        $this->assertSame(0, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_reservations', [
            'order_id' => $firstOrder->id,
            'status' => InventoryReservation::STATUS_ACTIVE,
            'quantity' => 1,
        ]);

        $this->expectException(InventoryUnavailableException::class);
        $inventory->reserveForOrder($secondOrder, $secondPayment);
    }

    public function test_failed_checkout_releases_reserved_stock_once(): void
    {
        [$seller, $product] = $this->sellerAndProduct(2);
        [$order, $payment] = $this->orderAndPayment($seller, $product, 'RELEASE');
        $inventory = app(InventoryService::class);

        $inventory->reserveForOrder($order, $payment);
        $this->assertSame(1, $product->fresh()->stock_quantity);

        $inventory->releaseForOrder($order);
        $inventory->releaseForOrder($order);

        $this->assertSame(2, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_reservations', [
            'order_id' => $order->id,
            'status' => InventoryReservation::STATUS_RELEASED,
        ]);
    }

    public function test_successful_payment_commits_without_decrementing_stock_twice(): void
    {
        [$seller, $product] = $this->sellerAndProduct(3);
        [$order, $payment] = $this->orderAndPayment($seller, $product, 'COMMIT');
        $inventory = app(InventoryService::class);

        $inventory->reserveForOrder($order, $payment);
        $this->assertSame(2, $product->fresh()->stock_quantity);

        $this->assertTrue($inventory->commitForOrder($order, $seller));
        $this->assertTrue($inventory->commitForOrder($order, $seller));

        $this->assertSame(2, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_reservations', [
            'order_id' => $order->id,
            'status' => InventoryReservation::STATUS_COMMITTED,
        ]);
    }

    public function test_expired_pending_reservation_is_returned_to_stock(): void
    {
        [$seller, $product] = $this->sellerAndProduct(1);
        [$order, $payment] = $this->orderAndPayment($seller, $product, 'EXPIRE');
        $inventory = app(InventoryService::class);

        $inventory->reserveForOrder($order, $payment);
        InventoryReservation::query()->where('order_id', $order->id)->update([
            'expires_at' => now()->subMinute(),
        ]);

        $this->assertSame(1, $inventory->releaseExpired());
        $this->assertSame(1, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_reservations', [
            'order_id' => $order->id,
            'status' => InventoryReservation::STATUS_EXPIRED,
        ]);
    }

    public function test_late_paid_order_without_recoverable_stock_enters_refund_review(): void
    {
        Queue::fake();
        [$seller, $product] = $this->sellerAndProduct(1);
        [$lateOrder, $latePayment] = $this->orderAndPayment($seller, $product, 'LATE');
        [$newOrder, $newPayment] = $this->orderAndPayment($seller, $product, 'NEW');
        $inventory = app(InventoryService::class);

        $inventory->reserveForOrder($lateOrder, $latePayment);
        InventoryReservation::query()->where('order_id', $lateOrder->id)->update([
            'expires_at' => now()->subMinute(),
        ]);
        $inventory->releaseExpired();
        $inventory->reserveForOrder($newOrder, $newPayment);

        app(PaymentService::class)->markSuccess($latePayment, [
            'status' => 'success',
            'reference' => $latePayment->reference,
            'amount' => 2500,
            'currency' => 'GHS',
            'channel' => 'card',
            'paid_at' => now()->toIso8601String(),
        ]);

        $this->assertSame(Payment::STATUS_SUCCESS, $latePayment->fresh()->status);
        $this->assertSame(Order::STATUS_CANNOT_FULFILL, $lateOrder->fresh()->status);
        $this->assertSame(Order::REFUND_REQUESTED, $lateOrder->fresh()->refund_status);
        $this->assertSame(0, $product->fresh()->stock_quantity);
    }

    private function sellerAndProduct(int $stock): array
    {
        $seller = User::factory()->create();
        $product = Product::create([
            'user_id' => $seller->id,
            'name' => 'Limited product',
            'slug' => 'limited-product-'.uniqid(),
            'price' => '25.00',
            'stock_quantity' => $stock,
            'low_stock_threshold' => 1,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);

        return [$seller, $product];
    }

    private function orderAndPayment(User $seller, Product $product, string $suffix): array
    {
        $order = Order::create([
            'user_id' => $seller->id,
            'reference' => 'ORDER-'.$suffix,
            'status' => Order::STATUS_PENDING_PAYMENT,
            'payment_status' => Payment::STATUS_PENDING,
            'customer_phone' => '+233541900229',
            'delivery_required' => false,
            'subtotal' => '25.00',
            'total' => '25.00',
            'currency' => 'GHS',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => '25.00',
            'quantity' => 1,
            'line_total' => '25.00',
        ]);

        $payment = Payment::create([
            'user_id' => $seller->id,
            'order_id' => $order->id,
            'reference' => 'PAY-'.$suffix,
            'amount' => '25.00',
            'status' => Payment::STATUS_PENDING,
        ]);

        return [$order, $payment];
    }
}
