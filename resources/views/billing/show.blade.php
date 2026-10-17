@php
    $title = 'Billing';
    $planCards = [
        ['code' => \App\Models\User::PLAN_STARTER, 'cfg' => $plans['starter'] ?? []],
        ['code' => \App\Models\User::PLAN_GROWTH, 'cfg' => $plans['growth'] ?? []],
        ['code' => \App\Models\User::PLAN_ENTERPRISE, 'cfg' => $plans['enterprise'] ?? []],
    ];
@endphp

@extends('layouts.dashboard')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Billing</h2>
            <p class="mt-1 text-sm text-slate-600">Manage your subscription plan.</p>
        </div>
        <a href="{{ route('pricing') }}" class="rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-200 hover:text-emerald-700">
            View pricing
        </a>
    </div>

    @if(session('status') === 'plan-activated')
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            Plan activated.
        </div>
    @endif

    @if(session('status') === 'subscription-active')
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">Your subscription is active.</div>
    @elseif(session('status') === 'subscription-cancelled')
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-900">Renewal cancelled. Access continues until the end of the paid period.</div>
    @elseif(session('status') === 'subscription-reactivated')
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">Automatic renewal has been restored.</div>
    @endif
    @if(session('billing_required'))<div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">{{ session('billing_required') }}</div>@endif
    @if($errors->any())<div class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">{{ $errors->first() }}</div>@endif

    @if(! $recurringBillingEnabled && ! $manualActivationEnabled)
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
            <p class="font-semibold">Online subscription checkout is not active yet.</p>
            <p class="mt-1 text-amber-800">Your current access remains unchanged. Plan selection will open after recurring billing and renewal webhooks are configured.</p>
        </div>
    @endif

    @if($subscription)
        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Subscription</p><h3 class="mt-1 text-lg font-semibold text-slate-950">{{ $subscription->plan_type }} · {{ ucfirst(str_replace('_', ' ', $subscription->status)) }}</h3><p class="mt-1 text-sm text-slate-600">{{ $subscription->billing_email }}@if($subscription->current_period_end) · Paid access through {{ $subscription->current_period_end->toFormattedDateString() }}@endif</p>@if($subscription->status === 'past_due' && $subscription->grace_ends_at)<p class="mt-2 text-sm font-semibold text-rose-700">Payment failed. Grace period ends {{ $subscription->grace_ends_at->toFormattedDateString() }}.</p>@endif</div><div>@if($subscription->status === 'active')<form method="POST" action="{{ route('billing.cancel') }}" onsubmit="return confirm('Cancel automatic renewal? Your access will continue until the paid period ends.')">@csrf<button class="lp-button-secondary">Cancel renewal</button></form>@elseif($subscription->status === 'non_renewing')<form method="POST" action="{{ route('billing.reactivate') }}">@csrf<button class="lp-button-brand">Restore renewal</button></form>@endif</div></div>
        </section>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Current status</p>
            <div class="mt-3 space-y-2 text-sm text-slate-700">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Plan</span>
                    <span class="font-semibold text-slate-900">{{ $user->planDisplayName() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Trial</span>
                    <span class="font-semibold text-slate-900">
                        @if($user->isOnTrial())
                            Active (ends {{ $user->trial_ends_at?->toFormattedDateString() }})
                        @elseif($user->trialExpired())
                            Expired
                        @else
                            --
                        @endif
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Plan ends</span>
                    <span class="font-semibold text-slate-900">{{ $user->plan_ends_at?->toFormattedDateString() ?: '--' }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900">Switch plan</h3>
                <span class="text-xs font-semibold text-slate-500">Monthly subscription</span>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-3">
                @foreach($planCards as $plan)
                    @php
                        $cfg = $plan['cfg'];
                    @endphp
                    <div class="rounded-2xl border {{ $plan['code'] === \App\Models\User::PLAN_GROWTH ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-white' }} p-5">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">{{ $cfg['name'] ?? $plan['code'] }}</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $cfg['price_text'] ?? '' }}</p>
                        </div>
                        <ul class="mt-3 space-y-1 text-xs text-slate-600">
                            <li>Products: {{ data_get($cfg, 'limits.products', '--') }}</li>
                            <li>Orders: {{ data_get($cfg, 'limits.orders', '--') }}</li>
                            <li>Team: {{ data_get($cfg, 'limits.team_members', '--') }}</li>
                            <li>Fee: {{ data_get($cfg, 'transaction_fee', '--') }}</li>
                        </ul>
                        @if($plan['code'] === $user->normalizedPlanType() && $user->hasActivePlan())
                            <div class="mt-4 min-h-10 w-full rounded-md bg-emerald-50 px-4 py-2 text-center text-xs font-semibold text-emerald-800">Current plan</div>
                        @elseif($recurringBillingEnabled && in_array($plan['code'], $configuredPlans, true))
                            <form method="POST" action="{{ route('billing.checkout', ['plan' => $plan['code']]) }}" class="mt-4 space-y-2">
                                @csrf
                                <label class="block text-xs font-semibold text-slate-600">Billing email</label>
                                <input type="email" name="billing_email" required autocomplete="email" value="{{ old('billing_email', $subscription?->billing_email ?? $user->email) }}" placeholder="name@example.com" class="w-full rounded-md border-slate-300 text-sm">
                                <button class="lp-button-brand w-full text-xs">Subscribe securely</button>
                                <p class="text-[11px] leading-4 text-slate-500">Paystack recurring billing currently requires a supported card. This email is for billing, not login.</p>
                                @if($subscription?->grantsAccess())<p class="text-[11px] leading-4 text-amber-700">Changing plans charges the new monthly price immediately. We then cancel renewal on the previous plan.</p>@endif
                            </form>
                        @elseif($manualActivationEnabled)
                            <form method="POST" action="{{ route('billing.activate', ['plan' => $plan['code']]) }}" class="mt-4">
                                @csrf
                                <button class="lp-button-secondary w-full text-xs">
                                    Activate {{ $cfg['name'] ?? $plan['code'] }} locally
                                </button>
                            </form>
                        @else
                            <button type="button" disabled class="mt-4 min-h-10 w-full cursor-not-allowed rounded-md border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-400">
                                Subscription setup pending
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if($invoices->isNotEmpty())
        <section class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white"><div class="border-b border-slate-200 px-5 py-4"><h3 class="font-semibold text-slate-950">Billing history</h3></div><div class="divide-y divide-slate-100">@foreach($invoices as $invoice)<div class="grid gap-2 px-5 py-4 text-sm sm:grid-cols-[1fr_auto_auto_auto] sm:items-center"><div><p class="font-medium text-slate-800">{{ $invoice->reference ?: $invoice->provider_invoice_code ?: 'Subscription invoice' }}</p><p class="text-xs text-slate-500">{{ $invoice->created_at->toFormattedDateString() }}</p></div><span class="font-semibold text-slate-800">{{ $invoice->currency }} {{ number_format((float) $invoice->amount, 2) }}</span><span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">{{ ucfirst($invoice->status) }}</span><a href="{{ route('billing.receipt', $invoice) }}" class="text-xs font-semibold text-emerald-700">Receipt</a></div>@endforeach</div></section>
    @endif
@endsection
