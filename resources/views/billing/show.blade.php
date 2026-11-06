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
    <x-ui.page-header title="Plan and billing" description="See what is active, compare limits, and manage renewal without losing track of access." eyebrow="Account plan" class="mb-6">
        <x-slot:actions><x-ui.button :href="route('pricing')" variant="secondary" icon="external-link">View public pricing</x-ui.button></x-slot:actions>
    </x-ui.page-header>

    @if(session('status') === 'plan-activated')
        <x-ui.alert tone="success" title="Plan activated">Your new plan and limits are ready to use.</x-ui.alert>
    @endif

    @if(session('status') === 'subscription-active')
        <x-ui.alert tone="success" title="Subscription active">Your recurring subscription is active.</x-ui.alert>
    @elseif(session('status') === 'subscription-cancelled')
        <x-ui.alert tone="warning" title="Renewal cancelled">Access continues until the end of the paid period.</x-ui.alert>
    @elseif(session('status') === 'subscription-reactivated')
        <x-ui.alert tone="success" title="Renewal restored">Automatic renewal has been restored.</x-ui.alert>
    @endif
    @if(session('billing_required'))<x-ui.alert tone="warning" class="mt-4">{{ session('billing_required') }}</x-ui.alert>@endif
    @if($errors->any())<x-ui.alert tone="danger" title="Billing action could not be completed" class="mt-4">{{ $errors->first() }}</x-ui.alert>@endif

    @if(! $recurringBillingEnabled && ! $manualRenewalEnabled && ! $manualActivationEnabled)
        <x-ui.alert tone="warning" title="Online checkout is not active yet" class="mt-4">
            Your current access remains unchanged. Plan selection will open after online billing is configured.
        </x-ui.alert>
    @endif

    @if($manualRenewalEnabled)
        <x-ui.alert tone="info" title="Pay monthly with your preferred channel" class="mt-4">
            Paystack will show the payment methods active for your account, including Mobile Money where available. Access lasts {{ config('billing.manual_period_days', 30) }} days and does not debit you automatically.
        </x-ui.alert>
    @endif

    @if($subscription)
        <section class="lp-card mb-6 p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><div class="flex flex-wrap items-center gap-2"><p class="lp-eyebrow">{{ $subscription->usesManualRenewal() ? 'Monthly access' : 'Recurring subscription' }}</p><x-ui.badge :tone="$subscription->status === 'active' ? 'success' : ($subscription->status === 'past_due' ? 'danger' : 'warning')">{{ ucfirst(str_replace('_', ' ', $subscription->status)) }}</x-ui.badge>@if($subscription->usesManualRenewal())<x-ui.badge tone="neutral">Manual renewal</x-ui.badge>@endif</div><h3 class="mt-2 text-xl font-semibold text-slate-950">{{ $subscription->plan_type }} plan</h3><p class="mt-1 text-sm text-slate-600">Billing notices go to {{ $subscription->billing_email }}@if($subscription->current_period_end) · Access paid through {{ $subscription->current_period_end->toFormattedDateString() }}@endif</p>@if($subscription->status === 'past_due' && $subscription->grace_ends_at)<p class="mt-2 text-sm font-semibold text-rose-700">Payment failed. Grace period ends {{ $subscription->grace_ends_at->toFormattedDateString() }}.</p>@endif</div><div>@if(! $subscription->usesManualRenewal() && $subscription->status === 'active')<form method="POST" action="{{ route('billing.cancel') }}" onsubmit="return confirm('Cancel automatic renewal? Your access will continue until the paid period ends.')">@csrf<x-ui.button type="submit" variant="secondary">Cancel renewal</x-ui.button></form>@elseif(! $subscription->usesManualRenewal() && $subscription->status === 'non_renewing')<form method="POST" action="{{ route('billing.reactivate') }}">@csrf<x-ui.button type="submit" icon="activity">Restore renewal</x-ui.button></form>@endif</div></div>
        </section>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lp-card p-6 lg:col-span-1">
            <div class="flex items-center justify-between"><div><p class="lp-eyebrow">Current access</p><h2 class="mt-1 text-lg font-semibold text-slate-950">{{ $user->planDisplayName() }}</h2></div><span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-800"><x-ui.icon name="credit-card" /></span></div>
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
            <div class="mt-5 border-t border-slate-200 pt-4"><p class="text-xs leading-5 text-slate-500">Plan changes take effect through the secure Paystack checkout. Your login email is not changed.</p></div>
        </div>

        <div class="lp-card p-5 sm:p-6 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900">Switch plan</h3>
                <span class="text-xs font-semibold text-slate-500">{{ $manualRenewalEnabled ? config('billing.manual_period_days', 30).'-day access' : 'Monthly subscription' }}</span>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-3">
                @foreach($planCards as $plan)
                    @php
                        $cfg = $plan['cfg'];
                    @endphp
                    <div class="relative rounded-lg border {{ $plan['code'] === \App\Models\User::PLAN_GROWTH ? 'border-emerald-300 bg-emerald-50/40' : 'border-slate-200 bg-white' }} p-5">
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
                        @if(! $manualRenewalEnabled && $plan['code'] === $user->normalizedPlanType() && $user->hasActivePlan())
                            <div class="mt-4 flex min-h-10 w-full items-center justify-center gap-1.5 rounded-md bg-emerald-100 px-4 py-2 text-center text-xs font-semibold text-emerald-900"><x-ui.icon name="circle-check" size="xs" />Current plan</div>
                        @elseif($manualRenewalEnabled || ($recurringBillingEnabled && in_array($plan['code'], $configuredPlans, true)))
                            <form method="POST" action="{{ route('billing.checkout', ['plan' => $plan['code']]) }}" class="mt-4 space-y-2">
                                @csrf
                                <label class="block text-xs font-semibold text-slate-600">Billing email</label>
                                <input type="email" name="billing_email" required autocomplete="email" value="{{ old('billing_email', $subscription?->billing_email ?? $user->email) }}" placeholder="name@example.com" class="w-full rounded-md border-slate-300 text-sm">
                                <button class="lp-button-brand w-full text-xs">{{ $manualRenewalEnabled ? ($plan['code'] === $user->normalizedPlanType() && $user->hasActivePlan() ? 'Renew securely' : 'Pay securely') : 'Subscribe securely' }}</button>
                                <p class="text-[11px] leading-4 text-slate-500">{{ $manualRenewalEnabled ? 'Choose an available Paystack payment method. You will approve each monthly renewal yourself.' : 'Paystack recurring billing currently requires a supported card.' }} This email is for billing, not login.</p>
                                @if($manualRenewalEnabled && $subscription?->grantsAccess() && $plan['code'] === $user->normalizedPlanType())
                                    <p class="text-[11px] leading-4 text-emerald-700">Renewing early adds {{ config('billing.manual_period_days', 30) }} days after your current access ends.</p>
                                @elseif($manualRenewalEnabled && $subscription?->grantsAccess())
                                    <p class="text-[11px] leading-4 text-amber-700">Changing plans applies immediately after verified payment and starts a new access period.</p>
                                @elseif($subscription?->grantsAccess())
                                    <p class="text-[11px] leading-4 text-amber-700">Changing plans charges the new monthly price immediately. We then cancel renewal on the previous plan.</p>
                                @endif
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
        <section class="lp-card mt-6 overflow-hidden"><div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><p class="lp-eyebrow">Receipts</p><h3 class="mt-1 font-semibold text-slate-950">Billing history</h3></div><x-ui.icon name="receipt" class="text-slate-400" /></div><div class="divide-y divide-slate-100">@foreach($invoices as $invoice)<div class="grid gap-2 px-5 py-4 text-sm sm:grid-cols-[1fr_auto_auto_auto] sm:items-center"><div><p class="font-medium text-slate-800">{{ $invoice->reference ?: $invoice->provider_invoice_code ?: 'Subscription invoice' }}</p><p class="text-xs text-slate-500">{{ $invoice->created_at->toFormattedDateString() }}</p></div><span class="font-semibold text-slate-800">{{ $invoice->currency }} {{ number_format((float) $invoice->amount, 2) }}</span><x-ui.badge :tone="$invoice->status === 'paid' ? 'success' : 'neutral'">{{ ucfirst($invoice->status) }}</x-ui.badge><a href="{{ route('billing.receipt', $invoice) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">Open receipt <x-ui.icon name="arrow-right" size="xs" /></a></div>@endforeach</div></section>
    @endif
@endsection
