<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerLiveSummaryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $successfulPayments = $user->payments()
            ->where('status', Payment::STATUS_SUCCESS)
            ->whereBetween('created_at', [$start, $end])
            ->get(['amount']);
        $amount = $successfulPayments->reduce(
            fn (string $sum, Payment $payment) => Money::add($sum, (string) $payment->amount),
            '0.00'
        );

        $orders = $user->orders()
            ->where('payment_status', Payment::STATUS_SUCCESS)
            ->whereBetween('created_at', [$start, $end]);
        $ordersCount = (clone $orders)->count();
        $newCustomers = (clone $orders)->get(['customer_phone', 'customer_name'])
            ->map(fn (Order $order) => trim((string) ($order->customer_phone ?: $order->customer_name)))
            ->filter()->unique()->count();

        $traffic = $user->analyticsEvents()
            ->whereIn('event_type', [
                AnalyticsEvent::TYPE_LISTING_VIEW,
                AnalyticsEvent::TYPE_PRODUCT_IMPRESSION,
                AnalyticsEvent::TYPE_PRODUCT_CLICK,
            ])
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $awaitingOrders = $user->orders()->where('status', Order::STATUS_PAID)->count();
        $stockAlerts = $user->products()->whereIn('status', [Product::STATUS_LOW_STOCK, Product::STATUS_SOLD_OUT])->count();
        $pendingPayments = $user->payments()->where('status', Payment::STATUS_PENDING)->count();
        $unreadActivity = $user->sellerNotifications()->whereNull('read_at')->count();
        $attention = $awaitingOrders + $stockAlerts + ($pendingPayments > 0 ? 1 : 0) + ($user->sellerProfile?->paystack_subaccount_code ? 0 : 1);

        $payload = [
            'updated_at' => now()->toIso8601String(),
            'values' => [
                'amount_received' => Money::format($amount, config('services.paystack.currency', 'GHS')),
                'orders_count' => number_format($ordersCount),
                'traffic_count' => number_format($traffic),
                'conversion_rate' => number_format($traffic > 0 ? ($ordersCount / $traffic) * 100 : 0, 1).'%',
                'new_customers' => number_format($newCustomers).' new customers',
                'attention_count' => $attention,
                'orders_awaiting' => $awaitingOrders,
                'stock_alerts' => $stockAlerts,
                'pending_payments' => $pendingPayments,
                'unread_activity' => $unreadActivity,
            ],
        ];

        $etag = '"'.sha1(json_encode($payload['values'])).'"';
        if ($request->header('If-None-Match') === $etag) {
            return response()->json(null, 304)->withHeaders(['ETag' => $etag, 'Cache-Control' => 'private, no-cache']);
        }

        return response()->json($payload)->withHeaders(['ETag' => $etag, 'Cache-Control' => 'private, no-cache']);
    }
}
