<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Services\PaystackService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $user->startTrialIfMissing();

        return view('billing.show', [
            'user' => $user,
            'plans' => config('plans'),
            'manualActivationEnabled' => (bool) config('billing.manual_activation_enabled') && app()->environment('local'),
            'recurringBillingEnabled' => (bool) config('billing.recurring_enabled'),
            'subscription' => $user->currentSubscription(),
            'invoices' => $user->subscriptionInvoices()->latest()->take(12)->get(),
            'configuredPlans' => collect(config('billing.plan_codes'))->filter()->keys()->all(),
        ]);
    }

    public function checkout(Request $request, string $plan, SubscriptionService $subscriptions, PaystackService $paystack): RedirectResponse
    {
        abort_unless(config('billing.recurring_enabled'), 404);
        $plan = strtoupper(trim($plan));
        $data = $request->validate(['billing_email' => ['required', 'email:rfc', 'max:254']]);
        abort_unless(in_array($plan, [User::PLAN_STARTER, User::PLAN_GROWTH, User::PLAN_ENTERPRISE], true), 404);

        $pending = $subscriptions->createPending($request->user(), $plan, $data['billing_email']);
        try {
            $checkout = $paystack->initializeSubscriptionCheckout(
                $pending->billing_email,
                $pending->provider_plan_code,
                $pending->checkout_reference,
                ['payment_type' => 'subscription', 'subscription_id' => $pending->id, 'user_id' => $pending->user_id, 'plan_type' => $pending->plan_type]
            );
        } catch (\Throwable $exception) {
            $pending->update(['status' => Subscription::STATUS_EXPIRED, 'metadata' => ['checkout_error' => true]]);
            Log::warning('Subscription checkout initialization failed', ['user_id' => $request->user()->id, 'plan' => $plan, 'message' => $exception->getMessage()]);
            return back()->withErrors(['billing' => 'Subscription checkout could not be started. Confirm the plan codes and try again.']);
        }

        $url = (string) ($checkout['authorization_url'] ?? '');
        if ($url === '') {
            return back()->withErrors(['billing' => 'Paystack did not return a checkout link.']);
        }
        return redirect()->away($url);
    }

    public function callback(Request $request, SubscriptionService $subscriptions, PaystackService $paystack): RedirectResponse
    {
        $reference = (string) $request->query('reference', '');
        $subscription = Subscription::query()->where('checkout_reference', $reference)->firstOrFail();
        abort_unless($subscription->user_id === $request->user()->id, 403);

        try {
            $verification = $paystack->verifyTransaction($reference);
            $subscriptions->activateFromCharge($subscription, (array) data_get($verification, 'data', []));
            $subscriptions->stopOlderRenewals($subscription->fresh(), $paystack);
        } catch (\Throwable $exception) {
            Log::warning('Subscription callback verification failed', ['subscription_id' => $subscription->id, 'message' => $exception->getMessage()]);
            return redirect()->route('billing.show')->withErrors(['billing' => 'We could not confirm the subscription yet. Paystack will retry through the secure webhook.']);
        }

        return redirect()->route('billing.show')->with('status', 'subscription-active');
    }

    public function cancel(Request $request, SubscriptionService $subscriptions, PaystackService $paystack): RedirectResponse
    {
        $subscription = $request->user()->currentSubscription();
        abort_unless($subscription, 404);
        try { $subscriptions->cancel($subscription, $paystack); }
        catch (\Throwable $exception) { return back()->withErrors(['billing' => $exception->getMessage()]); }
        return back()->with('status', 'subscription-cancelled');
    }

    public function reactivate(Request $request, SubscriptionService $subscriptions, PaystackService $paystack): RedirectResponse
    {
        $subscription = $request->user()->subscriptions()->where('status', Subscription::STATUS_NON_RENEWING)->latest()->firstOrFail();
        try { $subscriptions->reactivate($subscription, $paystack); }
        catch (\Throwable $exception) { return back()->withErrors(['billing' => $exception->getMessage()]); }
        return back()->with('status', 'subscription-reactivated');
    }

    public function receipt(Request $request, SubscriptionInvoice $invoice)
    {
        abort_unless($invoice->user_id === $request->user()->id, 403);
        $invoice->load('subscription');
        return view('billing.receipt', ['invoice' => $invoice, 'user' => $request->user()]);
    }

    public function upgrade(Request $request)
    {
        $user = $request->user();
        $user->startTrialIfMissing();
        return redirect()->route('billing.show')->with('billing_required', 'Choose a plan to continue.');
    }

    public function activate(Request $request, string $plan): RedirectResponse
    {
        abort_unless(config('billing.manual_activation_enabled') && app()->environment('local'), 404);

        $user = $request->user();
        $user->startTrialIfMissing();

        $plan = strtoupper(trim($plan));
        abort_unless(in_array($plan, [User::PLAN_STARTER, User::PLAN_GROWTH, User::PLAN_ENTERPRISE], true), 404);

        // Temporary activation flow: replace with MoMo subscription billing lifecycle.
        $user->plan_type = $plan;
        $user->plan_started_at = now();
        $user->plan_ends_at = now()->addDays(30);
        $user->save();

        return redirect()
            ->route('billing.show')
            ->with('status', 'plan-activated');
    }
}
