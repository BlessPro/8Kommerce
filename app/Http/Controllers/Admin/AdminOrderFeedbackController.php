<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendOutboundMessage;
use App\Models\Order;
use App\Models\OrderFeedback;
use App\Models\Payment;
use App\Services\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminOrderFeedbackController extends Controller
{
    public function photo(OrderFeedback $feedback)
    {
        abort_unless($feedback->issue_photo_path && Storage::disk('local')->exists($feedback->issue_photo_path), 404);

        return Storage::disk('local')->response($feedback->issue_photo_path);
    }

    public function index(): View
    {
        $feedbacks = OrderFeedback::query()
            ->with(['order.user.sellerProfile', 'reviewedByAdmin'])
            ->where('type', OrderFeedback::TYPE_REPORTED)
            ->latest()
            ->paginate(20);

        return view('admin.order-feedback.index', [
            'feedbacks' => $feedbacks,
            'refundPercent' => (float) config('orders.dispute_refund_percent', 0.90),
            'currency' => config('services.paystack.currency', 'GHS'),
        ]);
    }

    public function approveRefund(Request $request, OrderFeedback $feedback, PaystackService $paystack): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $lock = Cache::lock('order-feedback-refund:'.$feedback->id, 120);
        if (! $lock->get()) {
            return back()->withErrors(['feedback' => 'This refund is already being processed.']);
        }

        try {
            $feedback->refresh();
            if ($feedback->type !== OrderFeedback::TYPE_REPORTED || $feedback->admin_status !== OrderFeedback::ADMIN_PENDING) {
                return back()->withErrors(['feedback' => 'Only reported issues can be refunded.']);
            }

            $order = $feedback->order;
            if (! $order) {
                return back()->withErrors(['feedback' => 'Order not found for this feedback.']);
            }
            if (in_array($order->refund_status, [Order::REFUND_PROCESSING, Order::REFUND_COMPLETED], true)) {
                return back()->withErrors(['feedback' => 'This refund has already been submitted.']);
            }

            $payment = $order->payments()->where('status', Payment::STATUS_SUCCESS)->latest()->first();
            if (! $payment) {
                return back()->withErrors(['feedback' => 'No successful payment found for this order.']);
            }

            $refundPercent = (float) config('orders.dispute_refund_percent', 0.90);
            $refundAmount = round(((float) $payment->amount) * $refundPercent, 2);
            $refundData = null;

            $order->refund_status = Order::REFUND_PROCESSING;
            $order->save();

            try {
                $refundData = $paystack->createRefund(
                    $payment->reference,
                    (string) number_format($refundAmount, 2, '.', '')
                );
            } catch (\Throwable $exception) {
                return back()->withErrors(['feedback' => 'The refund result is uncertain. It was not retried to prevent a duplicate; reconcile it with Paystack.']);
            }

            $feedback->admin_status = OrderFeedback::ADMIN_REFUND_APPROVED;
            $feedback->admin_note = $validated['admin_note'] ?? null;
            $feedback->reviewed_by_admin_id = $request->user()->id;
            $feedback->reviewed_at = now();
            $feedback->save();

            $order->status = Order::STATUS_REFUNDED;
            $order->refund_status = Order::REFUND_COMPLETED;
            $order->save();

            $payload = $payment->raw_payload ?? [];
            $payload['refund'] = [
                'approved' => true,
                'amount' => (string) number_format($refundAmount, 2, '.', ''),
                'percent' => $refundPercent,
                'response' => $refundData,
                'admin_id' => $request->user()->id,
                'admin_note' => $validated['admin_note'] ?? null,
                'at' => now()->toIso8601String(),
            ];
            $payment->raw_payload = $payload;
            $payment->save();

            $this->sms(
                $order->customer_phone,
                'Your complaint for order '.$order->reference.' was approved. Refund of '.number_format($refundPercent * 100, 0).'% has been initiated.'
            );

            return back()->with('status', 'order-feedback-refund-approved');
        } finally {
            $lock->release();
        }
    }

    public function ignore(Request $request, OrderFeedback $feedback): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        if ($feedback->type !== OrderFeedback::TYPE_REPORTED) {
            return back()->withErrors(['feedback' => 'Only reported issues can be reviewed.']);
        }

        $order = $feedback->order;
        if (! $order) {
            return back()->withErrors(['feedback' => 'Order not found for this feedback.']);
        }

        $feedback->admin_status = OrderFeedback::ADMIN_IGNORED;
        $feedback->admin_note = $validated['admin_note'];
        $feedback->reviewed_by_admin_id = $request->user()->id;
        $feedback->reviewed_at = now();
        $feedback->save();

        $order->status = Order::STATUS_COMPLAINT_REJECTED;
        $order->save();

        $this->sms(
            $order->customer_phone,
            'Your complaint for order '.$order->reference.' was reviewed and marked invalid.'
            .($this->supportPhone() ? ' For escalation call '.$this->supportPhone().'.' : '')
        );

        return back()->with('status', 'order-feedback-ignored');
    }

    private function sms(?string $phone, string $message): void
    {
        if (! $phone) {
            return;
        }

        SendOutboundMessage::dispatch($phone, $message, [
            'context_type' => 'order_feedback_admin_decision',
        ], true, null, 'notifications')->afterCommit();
    }

    private function supportPhone(): string
    {
        return (string) config('orders.support_phone', '');
    }
}
