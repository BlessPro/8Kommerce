<?php

namespace App\Http\Controllers;

use App\Jobs\SendOutboundMessage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SellerNotification;
use App\Services\OrderFeedbackService;
use App\Services\SellerNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->sellerNotifications()->latest()->paginate(10);
        $pendingOrders = $request->user()->orders()
            ->with(['items', 'payments'])
            ->where('status', Order::STATUS_PAID)
            ->latest()
            ->take(20)
            ->get();

        return view('dashboard.notifications.index', [
            'notifications' => $notifications,
            'pendingOrders' => $pendingOrders,
            'currency' => config('services.paystack.currency', 'GHS'),
        ]);
    }

    public function acceptOrder(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        if ($order->status !== Order::STATUS_PAID) {
            return back()->withErrors(['order' => 'Order cannot be accepted in its current status.']);
        }

        $order->status = Order::STATUS_ACCEPTED;
        $order->accepted_at = now();
        $order->save();

        app(SellerNotifier::class)->notify(
            $request->user(),
            SellerNotification::TYPE_ORDER_ACCEPTED,
            'Order accepted',
            'You accepted order '.$order->reference.'.',
            ['order_id' => $order->id]
        );

        $this->notifyCustomer($order, 'accepted');

        return back()->with('status', 'order-updated');
    }

    public function rejectOrder(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        if ($order->status !== Order::STATUS_PAID) {
            return back()->withErrors(['order' => 'Order cannot be rejected in its current status.']);
        }

        $order->status = Order::STATUS_CANNOT_FULFILL;
        $order->cancelled_at = now();
        $order->refund_status = Order::REFUND_REQUESTED;
        $order->save();

        $payment = $order->payments()->where('status', Payment::STATUS_SUCCESS)->latest()->first();
        if ($payment) {
            $payload = is_array($payment->raw_payload) ? $payment->raw_payload : [];
            $payload['refund_requested'] = true;
            $payload['refund_requested_at'] = now()->toIso8601String();
            $payload['refund_requested_by'] = $request->user()->id;
            $payload['refund_reason'] = 'seller_cannot_fulfill';
            $payment->raw_payload = $payload;
            $payment->save();
        }

        app(SellerNotifier::class)->notify(
            $request->user(),
            SellerNotification::TYPE_ORDER_REJECTED,
            'Order cannot be fulfilled',
            'You marked order '.$order->reference.' as cannot fulfill.',
            ['order_id' => $order->id]
        );

        $this->notifyCustomer($order, 'rejected');

        return back()->with('status', 'order-updated');
    }

    public function startProcessing(Request $request, Order $order): RedirectResponse
    {
        return $this->transitionOrder($request, $order, Order::STATUS_ACCEPTED, Order::STATUS_PROCESSING, 'processing_at', 'Order is being prepared.');
    }

    public function markReady(Request $request, Order $order): RedirectResponse
    {
        return $this->transitionOrder($request, $order, Order::STATUS_PROCESSING, Order::STATUS_READY, 'ready_at', 'Order is ready for pickup or dispatch.');
    }

    public function markShipped(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        if (! in_array($order->status, [Order::STATUS_PROCESSING, Order::STATUS_READY], true)) {
            return back()->withErrors(['order' => 'Only processing or ready orders can be marked as shipped.']);
        }

        return $this->transitionOrder($request, $order, $order->status, Order::STATUS_SHIPPED, 'shipped_at', 'Order is on the way.');
    }

    public function markDelivered(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        if (! in_array($order->status, [Order::STATUS_READY, Order::STATUS_SHIPPED], true)) {
            return back()->withErrors(['order' => 'Only ready or shipped orders can be marked as delivered.']);
        }

        $order->status = Order::STATUS_DELIVERED;
        $order->delivered_at = now();
        $order->save();

        app(SellerNotifier::class)->notify(
            $request->user(),
            SellerNotification::TYPE_ORDER_STATUS_UPDATED,
            'Order marked delivered',
            'Order '.$order->reference.' is awaiting customer confirmation.',
            ['order_id' => $order->id, 'status' => $order->status]
        );
        $this->notifyCustomer($order, 'delivered');

        return back()->with('status', 'order-updated');
    }

    private function transitionOrder(
        Request $request,
        Order $order,
        string $expectedStatus,
        string $newStatus,
        string $timestampField,
        string $customerMessage
    ): RedirectResponse {
        abort_unless($order->user_id === $request->user()->id, 403);
        if ($order->status !== $expectedStatus) {
            return back()->withErrors(['order' => 'Order cannot move to that stage from its current status.']);
        }

        $order->status = $newStatus;
        $order->{$timestampField} = now();
        $order->save();

        app(SellerNotifier::class)->notify(
            $request->user(),
            SellerNotification::TYPE_ORDER_STATUS_UPDATED,
            'Order status updated',
            'Order '.$order->reference.' is now '.strtolower(str_replace('_', ' ', $newStatus)).'.',
            ['order_id' => $order->id, 'status' => $newStatus]
        );
        $this->sendCustomerMessage($order, 'Order '.$order->reference.': '.$customerMessage);

        return back()->with('status', 'order-updated');
    }

    private function notifyCustomer(Order $order, string $action): void
    {
        $phone = $order->customer_phone;
        if (! $phone) {
            return;
        }

        if ($action === 'accepted') {
            $message = 'Your order '.$order->reference.' has been accepted. The seller will update you as it is prepared.';
        } elseif ($action === 'delivered') {
            $token = app(OrderFeedbackService::class)->createOneTimeToken($order, $phone);
            $link = app(OrderFeedbackService::class)->feedbackUrl($token);
            $message = 'Order '.$order->reference.' was marked delivered. Confirm receipt or report an issue: '.$link;
        } else {
            $message = 'Your order '.$order->reference.' could not be fulfilled. A refund request has been created for review.';
        }

        $this->sendCustomerMessage($order, $message);
    }

    private function sendCustomerMessage(Order $order, string $message): void
    {
        $phone = $order->customer_phone;
        if (! $phone) {
            return;
        }

        SendOutboundMessage::dispatch($phone, $message, [
            'user_id' => $order->user_id,
            'context_type' => 'order_status',
            'context_id' => $order->id,
        ], true, null, 'notifications')->afterCommit();
    }
}
