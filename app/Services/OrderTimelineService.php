<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\SellerNotification;
use Illuminate\Support\Collection;

class OrderTimelineService
{
    public function build(Order $order): Collection
    {
        $timeline = collect([[
            'title' => 'Order placed',
            'body' => 'Your order was created successfully.',
            'time' => $order->created_at,
            'state' => 'done',
        ]]);

        $successfulPayment = $order->payments->firstWhere('status', Payment::STATUS_SUCCESS);
        if ($successfulPayment || $order->paid_at) {
            $timeline->push([
                'title' => 'Payment confirmed',
                'body' => 'Your payment was verified.',
                'time' => $successfulPayment?->paid_at ?? $order->paid_at,
                'state' => 'done',
            ]);
        }

        $decision = SellerNotification::query()
            ->where('user_id', $order->user_id)
            ->whereIn('type', [SellerNotification::TYPE_ORDER_ACCEPTED, SellerNotification::TYPE_ORDER_REJECTED])
            ->whereRaw("data->>'order_id' = ?", [(string) $order->id])
            ->latest()
            ->first();

        $steps = [
            Order::STATUS_PAID => ['Waiting for seller', 'The seller has received your paid order.', null, 'pending'],
            Order::STATUS_ACCEPTED => ['Order accepted', 'The seller confirmed your order.', $order->accepted_at ?? $decision?->created_at, 'done'],
            Order::STATUS_PROCESSING => ['Being prepared', 'The seller is preparing your order.', $order->processing_at, 'done'],
            Order::STATUS_READY => ['Ready', $order->delivery_required ? 'Your order is ready for dispatch.' : 'Your order is ready for pickup.', $order->ready_at, 'done'],
            Order::STATUS_SHIPPED => ['On the way', 'Your order has been dispatched.', $order->shipped_at, 'done'],
            Order::STATUS_DELIVERED => ['Delivered', 'The seller marked the order delivered. Please confirm receipt or report an issue.', $order->delivered_at, 'pending'],
            Order::STATUS_COMPLETED => ['Completed', 'Delivery was confirmed and the order is complete.', $order->updated_at, 'done'],
            Order::STATUS_CANNOT_FULFILL => ['Cannot fulfill', 'The seller cannot fulfill this order. Contact the seller for next steps.', $order->cancelled_at ?? $decision?->created_at, 'failed'],
            Order::STATUS_DISPUTED_PENDING_ADMIN => ['Issue under review', 'Your report is being reviewed by the LinkPay team.', $order->updated_at, 'pending'],
            Order::STATUS_REFUNDED => ['Refund processed', 'A refund was approved for this order.', $order->updated_at, 'done'],
            Order::STATUS_COMPLAINT_REJECTED => ['Review completed', 'The reported issue was reviewed and rejected.', $order->updated_at, 'failed'],
            Order::STATUS_PENDING_PAYMENT => ['Waiting for payment', 'Complete payment to move this order forward.', null, 'pending'],
        ];

        [$title, $body, $time, $state] = $steps[$order->status] ?? ['Order updated', 'Your order has a new update.', $order->updated_at, 'pending'];
        $timeline->push(compact('title', 'body', 'time', 'state'));

        return $timeline;
    }
}
