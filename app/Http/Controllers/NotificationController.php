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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    public function bulkAcceptOrders(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1', 'max:50'],
            'order_ids.*' => ['required', 'uuid', 'distinct'],
        ], [
            'order_ids.required' => 'Select at least one paid order to accept.',
        ]);

        $accepted = DB::transaction(function () use ($request, $validated) {
            $orders = Order::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('id', $validated['order_ids'])
                ->lockForUpdate()
                ->get();

            return $orders->filter(function (Order $order): bool {
                if ($order->status !== Order::STATUS_PAID) {
                    return false;
                }

                $order->status = Order::STATUS_ACCEPTED;
                $order->accepted_at = now();
                $order->save();

                return true;
            })->values();
        });

        if ($accepted->isEmpty()) {
            return back()->withErrors(['order_ids' => 'None of the selected orders are awaiting acceptance.']);
        }

        app(SellerNotifier::class)->notify(
            $request->user(),
            SellerNotification::TYPE_ORDER_ACCEPTED,
            'Orders accepted',
            'You accepted '.$accepted->count().' paid '.str('order')->plural($accepted->count()).'.',
            ['order_ids' => $accepted->pluck('id')->all()]
        );

        $accepted->each(fn (Order $order) => $this->notifyCustomer($order, 'accepted'));

        return back()->with('status', 'orders-bulk-accepted')->with('accepted_count', $accepted->count());
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
        $order->refund_requested_amount = $order->total;
        $order->refund_reason = 'seller_cannot_fulfill';
        $order->save();

        $payment = $order->payments()->where('status', Payment::STATUS_SUCCESS)->latest()->first();
        if ($payment) {
            $payload = is_array($payment->raw_payload) ? $payment->raw_payload : [];
            $payload['refund_requested'] = true;
            $payload['refund_requested_at'] = now()->toIso8601String();
            $payload['refund_requested_by'] = $request->user()->id;
            $payload['refund_reason'] = 'seller_cannot_fulfill';
            $payload['refund_requested_amount'] = (string) $order->total;
            $payload['refund_delivery_fee_included'] = true;
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
        if (! in_array($order->status, [Order::STATUS_PROCESSING, Order::STATUS_READY, Order::STATUS_DELIVERY_RESCHEDULED], true)) {
            return back()->withErrors(['order' => 'Only processing or ready orders can be marked as shipped.']);
        }

        return $this->transitionOrder($request, $order, $order->status, Order::STATUS_SHIPPED, 'shipped_at', 'Your order is on the way.');
    }

    public function reportDeliveryAttempt(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        if (! in_array($order->status, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERY_ATTEMPTED, Order::STATUS_DELIVERY_RESCHEDULED], true)) {
            return back()->withErrors(['order' => 'Only dispatched orders can record a delivery attempt.']);
        }

        $validated = $request->validate([
            'failure_reason' => ['required', Rule::in(['customer_unavailable', 'invalid_address', 'customer_requested', 'seller_delay', 'other'])],
            'failure_details' => ['nullable', 'string', 'max:500'],
            'rescheduled_for' => ['nullable', 'date', 'after:now'],
        ]);

        $labels = [
            'customer_unavailable' => 'Customer was unavailable',
            'invalid_address' => 'Address could not be located',
            'customer_requested' => 'Customer requested another time',
            'seller_delay' => 'Seller or courier delay',
            'other' => 'Delivery could not be completed',
        ];
        $reason = $labels[$validated['failure_reason']];
        if (filled($validated['failure_details'] ?? null)) {
            $reason .= ': '.trim($validated['failure_details']);
        }

        $order->status = filled($validated['rescheduled_for'] ?? null)
            ? Order::STATUS_DELIVERY_RESCHEDULED
            : Order::STATUS_DELIVERY_ATTEMPTED;
        $order->delivery_attempted_at = now();
        $order->rescheduled_for = $validated['rescheduled_for'] ?? null;
        $order->delivery_failure_reason = $reason;
        $order->save();

        $message = 'Order '.$order->reference.': '.$reason.'.';
        if ($order->rescheduled_for) {
            $message .= ' New delivery: '.$order->rescheduled_for->format('M j, Y g:i A').'.';
        } else {
            $message .= ' The seller will contact you about the next step.';
        }
        $this->sendCustomerMessage($order, $message);

        return back()->with('status', 'order-updated');
    }

    public function returnOrder(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        if (! in_array($order->status, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERY_ATTEMPTED, Order::STATUS_DELIVERY_RESCHEDULED], true)) {
            return back()->withErrors(['order' => 'This order cannot be returned from its current stage.']);
        }

        $validated = $request->validate([
            'return_reason' => ['required', Rule::in(['customer_unavailable', 'invalid_address', 'customer_refused', 'seller_fault', 'damaged'])],
            'return_details' => ['nullable', 'string', 'max:500'],
        ]);

        $includeDeliveryFee = in_array($validated['return_reason'], ['seller_fault', 'damaged'], true);
        $refundableProducts = max(0, (float) $order->total - (float) $order->fulfillment_fee);
        $refundAmount = $includeDeliveryFee ? (float) $order->total : $refundableProducts;

        $order->status = Order::STATUS_RETURNED;
        $order->returned_at = now();
        $order->refund_status = Order::REFUND_REQUESTED;
        $order->refund_requested_amount = number_format($refundAmount, 2, '.', '');
        $order->refund_reason = $validated['return_reason'];
        $order->delivery_failure_reason = trim((string) ($validated['return_details'] ?? '')) ?: $order->delivery_failure_reason;
        $order->save();

        $payment = $order->payments()->where('status', Payment::STATUS_SUCCESS)->latest()->first();
        if ($payment) {
            $payload = is_array($payment->raw_payload) ? $payment->raw_payload : [];
            $payload['refund_requested'] = true;
            $payload['refund_requested_at'] = now()->toIso8601String();
            $payload['refund_requested_by'] = $request->user()->id;
            $payload['refund_reason'] = $validated['return_reason'];
            $payload['refund_requested_amount'] = number_format($refundAmount, 2, '.', '');
            $payload['refund_delivery_fee_included'] = $includeDeliveryFee;
            $payment->raw_payload = $payload;
            $payment->save();
        }

        $feeMessage = $includeDeliveryFee ? 'including the delivery fee' : 'excluding the completed delivery attempt fee';
        $this->sendCustomerMessage($order, 'Order '.$order->reference.' was returned. A refund request for '.$order->currency.' '.number_format($refundAmount, 2).' ('.$feeMessage.') is awaiting review.');

        return back()->with('status', 'order-updated');
    }

    public function markDelivered(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        if (! in_array($order->status, [Order::STATUS_READY, Order::STATUS_SHIPPED, Order::STATUS_DELIVERY_ATTEMPTED, Order::STATUS_DELIVERY_RESCHEDULED], true)) {
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
        string|array $expectedStatus,
        string $newStatus,
        string $timestampField,
        string $customerMessage
    ): RedirectResponse {
        abort_unless($order->user_id === $request->user()->id, 403);
        if (! in_array($order->status, (array) $expectedStatus, true)) {
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
