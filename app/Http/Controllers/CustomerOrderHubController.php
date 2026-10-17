<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\CustomerTrackingOtpService;
use App\Services\CustomerTrackingService;
use App\Services\OrderTimelineService;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class CustomerOrderHubController extends Controller
{
    public function login(Request $request, CustomerTrackingService $tracking): View|RedirectResponse
    {
        if ($this->resolvedPhone($request, $tracking)) {
            return redirect()->route('customer.orders.index');
        }

        return view('public.order-hub-login');
    }

    public function verify(Request $request, CustomerTrackingService $tracking, CustomerTrackingOtpService $otp): RedirectResponse
    {
        $data = $request->validate([
            'phone_number' => ['required', 'string', 'max:25'],
            'otp' => ['required', 'digits:6'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $phone = Phone::normalize($data['phone_number'], '+233');
        if (! $phone || ! $tracking->hasOrders($phone) || ! $otp->verify($phone, $data['otp'])) {
            return back()->withErrors(['credentials' => 'Phone number or verification code is incorrect or expired.'])->withInput($request->only('phone_number'));
        }

        $request->session()->regenerate();
        $request->session()->put(CustomerTrackingService::SESSION_PHONE, $phone);
        $request->session()->put(CustomerTrackingService::SESSION_VERIFIED_AT, now()->timestamp);

        if ($request->boolean('remember', true)) {
            Cookie::queue(cookie(
                CustomerTrackingService::COOKIE,
                $tracking->issueTrustedToken($phone),
                60 * 24 * CustomerTrackingService::TRUST_DAYS,
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'lax'
            ));
        }

        return redirect()->route('customer.orders.index');
    }

    public function index(Request $request, CustomerTrackingService $tracking): View|RedirectResponse
    {
        $phone = $this->resolvedPhone($request, $tracking);
        if (! $phone) {
            return redirect()->route('customer.orders.login');
        }

        $active = [Order::STATUS_PENDING_PAYMENT, Order::STATUS_PAID, Order::STATUS_ACCEPTED, Order::STATUS_PROCESSING, Order::STATUS_READY, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED];
        $completed = [Order::STATUS_COMPLETED];
        $issues = [Order::STATUS_CANNOT_FULFILL, Order::STATUS_DISPUTED_PENDING_ADMIN, Order::STATUS_REFUNDED, Order::STATUS_COMPLAINT_REJECTED];
        $groups = compact('active', 'completed', 'issues');
        $tab = array_key_exists((string) $request->query('tab'), $groups) ? (string) $request->query('tab') : 'active';

        $base = Order::query()->where('customer_phone', $phone)->where('status', '!=', Order::STATUS_TRASHED);
        $counts = collect($groups)->map(fn (array $statuses) => (clone $base)->whereIn('status', $statuses)->count());
        $orders = (clone $base)
            ->with(['items.product', 'user.sellerProfile'])
            ->whereIn('status', $groups[$tab])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('public.order-hub-index', [
            'orders' => $orders,
            'counts' => $counts,
            'tab' => $tab,
            'maskedPhone' => $tracking->maskedPhone($phone),
            'currency' => config('services.paystack.currency', 'GHS'),
        ]);
    }

    public function show(Request $request, Order $order, CustomerTrackingService $tracking, OrderTimelineService $timeline): View|RedirectResponse
    {
        $phone = $this->resolvedPhone($request, $tracking);
        if (! $phone) {
            return redirect()->route('customer.orders.login');
        }
        abort_unless(hash_equals($phone, (string) $order->customer_phone), 404);

        $order->load(['items.product', 'user.sellerProfile', 'payments' => fn ($query) => $query->orderBy('created_at'), 'feedbackTokens' => fn ($query) => $query->latest()]);
        $feedbackToken = $order->feedbackTokens->first(fn ($token) => ! $token->used_at && (! $token->expires_at || $token->expires_at->isFuture()));

        return view('public.order-hub-show', [
            'order' => $order,
            'timeline' => $timeline->build($order),
            'successfulPayment' => $order->payments->firstWhere('status', Payment::STATUS_SUCCESS),
            'feedbackToken' => $feedbackToken,
            'maskedPhone' => $tracking->maskedPhone($phone),
            'currency' => config('services.paystack.currency', 'GHS'),
        ]);
    }

    public function sendOtp(Request $request, CustomerTrackingService $tracking, CustomerTrackingOtpService $otp): RedirectResponse
    {
        $data = $request->validate(['phone_number' => ['required', 'string', 'max:25']]);
        $phone = Phone::normalize($data['phone_number'], '+233');
        if ($phone && $tracking->hasOrders($phone)) {
            $otp->request($phone);
        }

        return back()->withInput(['phone_number' => $data['phone_number']])
            ->with('tracking-otp-sent', 'If that number has orders, a verification code will arrive shortly.');
    }

    public function logout(Request $request, CustomerTrackingService $tracking): RedirectResponse
    {
        $tracking->revokeTrustedToken($request->cookie(CustomerTrackingService::COOKIE));
        $request->session()->forget([CustomerTrackingService::SESSION_PHONE, CustomerTrackingService::SESSION_VERIFIED_AT]);
        Cookie::queue(Cookie::forget(CustomerTrackingService::COOKIE));

        return redirect()->route('customer.orders.login');
    }

    private function resolvedPhone(Request $request, CustomerTrackingService $tracking): ?string
    {
        $phone = $request->session()->get(CustomerTrackingService::SESSION_PHONE);
        if (! $phone) {
            $phone = $tracking->resolveTrustedToken($request->cookie(CustomerTrackingService::COOKIE));
        }

        if (! $phone || ! $tracking->hasOrders((string) $phone)) {
            return null;
        }

        return (string) $phone;
    }
}
