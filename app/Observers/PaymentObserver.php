<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class PaymentObserver
{
    public function updated(Payment $payment): void
    {
        if (! $payment->wasChanged('status') || $payment->status !== Payment::STATUS_FAILED || ! $payment->order_id) {
            return;
        }

        $orderId = $payment->order_id;
        DB::afterCommit(function () use ($orderId): void {
            $order = Order::find($orderId);
            if ($order) {
                app(InventoryService::class)->releaseForOrder($order);
            }
        });
    }
}
