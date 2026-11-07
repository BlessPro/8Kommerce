<?php

namespace App\Services;

use App\Exceptions\InventoryUnavailableException;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SellerNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function syncStatusAndAlert(Product $product, ?User $user = null, bool $notify = true): Product
    {
        if (! $product->isInventoryManaged()) {
            return $product;
        }

        $newStatus = $this->deriveStatus($product->stock_quantity, $product->low_stock_threshold);
        $changed = false;

        if ($product->status !== $newStatus) {
            $product->status = $newStatus;
            $changed = true;
        }

        if ($newStatus === Product::STATUS_IN_STOCK) {
            if ($product->stock_alert_state !== null || $product->stock_alerted_at !== null) {
                $product->stock_alert_state = null;
                $product->stock_alerted_at = null;
                $changed = true;
            }
        } elseif ($notify && $product->stock_alert_state !== $newStatus) {
            $product->stock_alert_state = $newStatus;
            $product->stock_alerted_at = now();
            $changed = true;

            $targetUser = $user ?? $product->user;
            if ($targetUser) {
                $title = $newStatus === Product::STATUS_SOLD_OUT ? 'Product sold out' : 'Low stock alert';
                $body = $newStatus === Product::STATUS_SOLD_OUT
                    ? $product->name.' is now sold out.'
                    : $product->name.' is low on stock ('.$product->stock_quantity.' left).';

                app(SellerNotifier::class)->notify(
                    $targetUser,
                    SellerNotification::TYPE_STOCK_ALERT,
                    $title,
                    $body,
                    [
                        'product_id' => $product->id,
                        'status' => $newStatus,
                        'stock_quantity' => $product->stock_quantity,
                        'low_stock_threshold' => $product->low_stock_threshold,
                    ],
                    sendEmail: false,
                    sendWhatsApp: true
                );
            }
        }

        if ($changed) {
            $product->save();
        }

        return $product;
    }

    public function decrementForDirectProductPayment(Product $product, int $quantity = 1, ?User $user = null): Product
    {
        return DB::transaction(function () use ($product, $quantity, $user): Product {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            if (! $locked->isInventoryManaged()) {
                return $locked;
            }

            $quantity = max(1, $quantity);
            if ((int) $locked->stock_quantity < $quantity) {
                throw new InventoryUnavailableException(
                    $locked->name,
                    $quantity,
                    (int) $locked->stock_quantity,
                );
            }

            $locked->stock_quantity = (int) $locked->stock_quantity - $quantity;
            $locked->save();

            return $this->syncStatusAndAlert($locked, $user, true);
        }, 3);
    }

    public function decrementForOrder(Order $order, ?User $user = null): void
    {
        DB::transaction(function () use ($order, $user): void {
            $quantities = $this->orderQuantities($order);
            $products = Product::query()
                ->whereIn('id', array_keys($quantities))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);
                if (! $product || ! $product->isInventoryManaged()) {
                    continue;
                }

                if ((int) $product->stock_quantity < $quantity) {
                    throw new InventoryUnavailableException(
                        $product->name,
                        $quantity,
                        (int) $product->stock_quantity,
                    );
                }
            }

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);
                if (! $product || ! $product->isInventoryManaged()) {
                    continue;
                }

                $product->stock_quantity = (int) $product->stock_quantity - $quantity;
                $product->save();
                $this->syncStatusAndAlert($product, $user, true);
            }
        }, 3);
    }

    /**
     * Reserve every inventory-managed order item atomically. Stock is removed
     * from availability immediately and restored if checkout fails or expires.
     */
    public function reserveForOrder(Order $order, Payment $payment): void
    {
        DB::transaction(function () use ($order, $payment): void {
            $existing = InventoryReservation::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->exists();

            if ($existing) {
                return;
            }

            $quantities = $this->orderQuantities($order);
            $products = Product::query()
                ->whereIn('id', array_keys($quantities))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);
                if (! $product || ! $product->isInventoryManaged()) {
                    continue;
                }

                if ((int) $product->stock_quantity < $quantity) {
                    throw new InventoryUnavailableException(
                        $product->name,
                        $quantity,
                        (int) $product->stock_quantity,
                    );
                }
            }

            $expiresAt = now()->addMinutes((int) config('orders.inventory_reservation_minutes', 30));
            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);
                if (! $product || ! $product->isInventoryManaged()) {
                    continue;
                }

                $product->stock_quantity = (int) $product->stock_quantity - $quantity;
                $product->save();
                $this->syncStatusAndAlert($product, $order->user, false);

                InventoryReservation::create([
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'status' => InventoryReservation::STATUS_ACTIVE,
                    'expires_at' => $expiresAt,
                ]);
            }
        }, 3);
    }

    /**
     * Commit an order reservation. Returns false for legacy orders which did
     * not have reservations and therefore still need a direct decrement.
     */
    public function commitForOrder(Order $order, ?User $user = null): bool
    {
        return DB::transaction(function () use ($order, $user): bool {
            $reservations = InventoryReservation::query()
                ->where('order_id', $order->id)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            if ($reservations->isEmpty()) {
                return false;
            }

            if ($reservations->every(fn (InventoryReservation $row) => $row->status === InventoryReservation::STATUS_COMMITTED)) {
                return true;
            }

            $reacquireReservations = $reservations->filter(
                fn (InventoryReservation $row) => in_array($row->status, [
                    InventoryReservation::STATUS_RELEASED,
                    InventoryReservation::STATUS_EXPIRED,
                ], true)
            );

            $products = Product::query()
                ->whereIn('id', $reservations->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($reacquireReservations->isNotEmpty()) {
                foreach ($reacquireReservations as $reservation) {
                    $product = $products->get($reservation->product_id);
                    if (! $product || ! $product->isInventoryManaged()) {
                        continue;
                    }

                    if ((int) $product->stock_quantity < $reservation->quantity) {
                        throw new InventoryUnavailableException(
                            $product->name,
                            $reservation->quantity,
                            (int) $product->stock_quantity,
                        );
                    }
                }

                foreach ($reacquireReservations as $reservation) {
                    $product = $products->get($reservation->product_id);
                    if (! $product || ! $product->isInventoryManaged()) {
                        continue;
                    }
                    $product->stock_quantity = (int) $product->stock_quantity - $reservation->quantity;
                    $product->save();
                }
            }

            foreach ($reservations as $reservation) {
                $reservation->forceFill([
                    'status' => InventoryReservation::STATUS_COMMITTED,
                    'committed_at' => now(),
                    'released_at' => null,
                ])->save();

                $product = $products->get($reservation->product_id);
                if ($product) {
                    $this->syncStatusAndAlert($product, $user, true);
                }
            }

            return true;
        }, 3);
    }

    public function releaseForOrder(Order $order, string $status = InventoryReservation::STATUS_RELEASED): int
    {
        return DB::transaction(function () use ($order, $status): int {
            $reservations = InventoryReservation::query()
                ->where('order_id', $order->id)
                ->where('status', InventoryReservation::STATUS_ACTIVE)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            if ($reservations->isEmpty()) {
                return 0;
            }

            $products = Product::query()
                ->whereIn('id', $reservations->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($reservations as $reservation) {
                $product = $products->get($reservation->product_id);
                if ($product && $product->isInventoryManaged()) {
                    $product->stock_quantity = (int) $product->stock_quantity + $reservation->quantity;
                    $product->save();
                    $this->syncStatusAndAlert($product, $order->user, false);
                }

                $reservation->forceFill([
                    'status' => $status,
                    'released_at' => now(),
                ])->save();
            }

            return $reservations->count();
        }, 3);
    }

    public function releaseExpired(int $limit = 100): int
    {
        $orderIds = InventoryReservation::query()
            ->where('status', InventoryReservation::STATUS_ACTIVE)
            ->where('expires_at', '<=', now())
            ->orderBy('expires_at')
            ->limit(max(1, $limit))
            ->pluck('order_id')
            ->unique();

        $released = 0;
        foreach ($orderIds as $orderId) {
            DB::transaction(function () use ($orderId, &$released): void {
                // Keep the lock order aligned with PaymentService: payment first,
                // then reservation/product rows. This avoids a checkout/expiry deadlock.
                $payments = Payment::query()
                    ->where('order_id', $orderId)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $order = Order::query()->find($orderId);
                if (! $order) {
                    return;
                }

                $hasSuccessfulPayment = $payments->contains(
                    fn (Payment $payment) => $payment->status === Payment::STATUS_SUCCESS
                );

                if ($hasSuccessfulPayment) {
                    $this->commitForOrder($order, $order->user);

                    return;
                }

                $released += $this->releaseForOrder($order, InventoryReservation::STATUS_EXPIRED);
            }, 3);
        }

        return $released;
    }

    private function orderQuantities(Order $order): array
    {
        $order->loadMissing('items');

        return $order->items
            ->filter(fn ($item) => $item->product_id !== null)
            ->groupBy('product_id')
            ->map(fn ($items) => max(1, (int) $items->sum('quantity')))
            ->sortKeys()
            ->all();
    }

    private function deriveStatus(int $stockQuantity, int $threshold): string
    {
        if ($stockQuantity <= 0) {
            return Product::STATUS_SOLD_OUT;
        }

        if ($stockQuantity <= max(0, $threshold)) {
            return Product::STATUS_LOW_STOCK;
        }

        return Product::STATUS_IN_STOCK;
    }
}
