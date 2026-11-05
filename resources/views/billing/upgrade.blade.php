@php
    $title = 'Upgrade';
    $planCards = [
        ['code' => \App\Models\User::PLAN_STARTER, 'cfg' => $plans['starter'] ?? []],
        ['code' => \App\Models\User::PLAN_GROWTH, 'cfg' => $plans['growth'] ?? []],
        ['code' => \App\Models\User::PLAN_ENTERPRISE, 'cfg' => $plans['enterprise'] ?? []],
    ];
@endphp

@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header title="Choose a plan to continue" description="Your work is safe. Restore access by selecting the plan that matches how the business operates today." eyebrow="Upgrade required" class="mb-6">
        <x-slot:actions><x-ui.button :href="route('billing.show')" variant="secondary" icon="credit-card">Manage billing</x-ui.button></x-slot:actions>
    </x-ui.page-header>

    <div class="lp-card p-5 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="lp-eyebrow">Access status</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">
                    @if($user->isOnTrial())
                        Trial active (ends {{ $user->trial_ends_at?->toFormattedDateString() }})
                    @elseif($user->hasActivePlan())
                        Active plan: {{ $user->planDisplayName() }}
                    @else
                        No active plan
                    @endif
                </p>
            </div>
            @if(session('billing_required'))
                <x-ui.badge tone="warning">{{ session('billing_required') }}</x-ui.badge>
            @endif
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        @foreach($planCards as $plan)
            @php
                $cfg = $plan['cfg'];
            @endphp
            <div class="relative rounded-lg border {{ $plan['code'] === \App\Models\User::PLAN_GROWTH ? 'border-emerald-300 bg-emerald-50/30 ring-1 ring-emerald-200' : 'border-slate-200 bg-white' }} p-6">
                @if($plan['code'] === \App\Models\User::PLAN_GROWTH)<x-ui.badge tone="brand" class="absolute -top-3 left-5">Most popular</x-ui.badge>@endif
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">{{ $cfg['name'] ?? $plan['code'] }}</h3>
                    <span class="text-sm font-semibold text-slate-700">{{ $cfg['price_text'] ?? '' }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-600">{{ data_get($cfg, 'best_for', 'Subscription plan') }}</p>
                <ul class="mt-5 space-y-3 text-sm text-slate-700">
                    <li class="flex items-center justify-between gap-3"><span class="text-slate-500">Products</span><strong>{{ data_get($cfg, 'limits.products', '--') }}</strong></li>
                    <li class="flex items-center justify-between gap-3"><span class="text-slate-500">Orders</span><strong>{{ data_get($cfg, 'limits.orders', '--') }}</strong></li>
                    <li class="flex items-center justify-between gap-3"><span class="text-slate-500">Team members</span><strong>{{ data_get($cfg, 'limits.team_members', '--') }}</strong></li>
                    <li class="flex items-center justify-between gap-3"><span class="text-slate-500">Transaction fee</span><strong>{{ data_get($cfg, 'transaction_fee', '--') }}</strong></li>
                </ul>
                <form method="POST" action="{{ route('billing.activate', ['plan' => $plan['code']]) }}" class="mt-6">
                    @csrf
                    <x-ui.button type="submit" :variant="$plan['code'] === \App\Models\User::PLAN_GROWTH ? 'brand' : 'secondary'" class="w-full">Choose {{ $cfg['name'] ?? $plan['code'] }}</x-ui.button>
                </form>
            </div>
        @endforeach
    </div>
@endsection

