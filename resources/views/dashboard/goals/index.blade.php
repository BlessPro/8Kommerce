@php
    $title = 'Goals';
    $revenueDirection = $revenueGrowth >= 0 ? 'up' : 'down';
    $ordersDirection = $ordersGrowth >= 0 ? 'up' : 'down';
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header title="Goals and performance" description="A practical monthly view of what is growing, what needs attention, and where to focus next.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="chart-bar" :href="route('insights.index')">Advanced insights</x-ui.button>
            <x-ui.button icon="speakerphone" :href="route('promotions.index')">Promote a product</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mt-3 flex items-center gap-2 text-xs text-slate-500"><x-ui.icon name="calendar" size="xs" /><span>{{ $monthStart->format('M j') }} – {{ $monthEnd->format('M j, Y') }}</span><span>·</span><span>Targets adapt to your sales history</span></div>

    <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Business growth summary">
        <x-ui.stat-card label="Revenue growth" :value="($businessSummary['revenueGrowth'] >= 0 ? '+' : '').number_format($businessSummary['revenueGrowth'], 1).'%'" meta="Compared with last month" icon="activity" />
        <x-ui.stat-card label="Orders growth" :value="($businessSummary['ordersGrowth'] >= 0 ? '+' : '').number_format($businessSummary['ordersGrowth'], 1).'%'" meta="Compared with last month" icon="shopping-bag" />
        <x-ui.stat-card label="Top product" :value="$businessSummary['topProduct']" :meta="number_format($businessSummary['topProductShare'] ?? 0, 1).'% of revenue'" icon="package" />
        <x-ui.stat-card label="Best sales day" :value="$businessSummary['topDay']" meta="Your strongest day this month" icon="calendar" />
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-2" aria-label="Monthly targets">
        <article class="lp-card p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4"><div><p class="lp-eyebrow">Revenue target</p><h2 class="mt-2 text-2xl font-semibold tabular-nums text-slate-950">{{ \App\Support\Money::format((string) $thisMonthRevenue, $currency) }}</h2></div><x-ui.badge :tone="$revenueDirection === 'up' ? 'success' : 'danger'">{{ $revenueGrowth >= 0 ? '+' : '' }}{{ number_format($revenueGrowth, 1) }}%</x-ui.badge></div>
            <div class="mt-5 h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-600" style="width: {{ max(0, min(100, $revenueProgress)) }}%"></div></div>
            <div class="mt-2 flex justify-between gap-3 text-xs text-slate-500"><span>{{ number_format($revenueProgress, 1) }}% complete</span><span>Target {{ \App\Support\Money::format((string) $revenueTarget, $currency) }}</span></div>
            <p class="mt-4 border-t border-slate-100 pt-4 text-xs text-slate-500">Last month: {{ \App\Support\Money::format((string) $lastMonthRevenue, $currency) }}</p>
        </article>
        <article class="lp-card p-5 sm:p-6">
            @php
                $orderProgress = $ordersTarget > 0 ? min(100, ($ordersThisMonth / $ordersTarget) * 100) : 0;
            @endphp
            <div class="flex items-start justify-between gap-4"><div><p class="lp-eyebrow">Order target</p><h2 class="mt-2 text-2xl font-semibold tabular-nums text-slate-950">{{ $ordersThisMonth }} orders</h2></div><x-ui.badge :tone="$ordersDirection === 'up' ? 'success' : 'danger'">{{ $ordersGrowth >= 0 ? '+' : '' }}{{ number_format($ordersGrowth, 1) }}%</x-ui.badge></div>
            <div class="mt-5 h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-sky-600" style="width: {{ $orderProgress }}%"></div></div>
            <div class="mt-2 flex justify-between gap-3 text-xs text-slate-500"><span>{{ number_format($orderProgress, 1) }}% complete</span><span>Target {{ $ordersTarget }}</span></div>
            <p class="mt-4 border-t border-slate-100 pt-4 text-xs text-slate-500">Last month: {{ $ordersLastMonth }} orders</p>
        </article>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
        <article class="lp-card overflow-hidden">
            <header class="border-b border-slate-200 px-5 py-4"><p class="lp-eyebrow">What is working</p><h2 class="lp-section-title mt-1">Best-performing products</h2></header>
            <div class="divide-y divide-slate-100">
                @forelse($topProducts as $row)
                    <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-4 px-5 py-4"><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800">{{ $row['name'] }}</p><p class="mt-1 text-xs text-slate-500">{{ $row['units'] }} units · {{ number_format($row['share'], 1) }}% share</p></div><span class="text-sm font-semibold tabular-nums text-slate-950">{{ \App\Support\Money::format((string) $row['revenue'], $currency) }}</span></div>
                @empty
                    <x-ui.empty-state title="No product sales yet" description="Your strongest products will appear after successful orders." icon="package" class="border-0" />
                @endforelse
            </div>
        </article>
        <article class="lp-card overflow-hidden">
            <header class="border-b border-slate-200 px-5 py-4"><p class="lp-eyebrow">Needs attention</p><h2 class="lp-section-title mt-1">Low-converting products</h2></header>
            <div class="divide-y divide-slate-100">
                @forelse($weakProducts as $row)
                    <div class="flex items-center justify-between gap-4 px-5 py-4"><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800">{{ $row['name'] }}</p><p class="mt-1 text-xs text-slate-500">{{ $row['views'] }} views · {{ $row['purchases'] }} purchases</p></div><x-ui.badge tone="danger">{{ number_format($row['conversion'], 1) }}%</x-ui.badge></div>
                @empty
                    <x-ui.empty-state title="No weak products detected" description="No product is currently below the low-conversion threshold." icon="circle-check" class="border-0" />
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-3">
        <article class="lp-card p-5"><p class="lp-eyebrow">Customer mix</p><h2 class="lp-section-title mt-1">Relationships</h2><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><dt class="text-slate-500">New customers</dt><dd class="font-semibold text-slate-900">{{ $customerInsights['new'] }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Returning</dt><dd class="font-semibold text-slate-900">{{ $customerInsights['returning'] }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Return rate</dt><dd class="font-semibold text-emerald-700">{{ number_format($customerInsights['returningRate'], 1) }}%</dd></div></dl></article>
        <article class="lp-card p-5"><p class="lp-eyebrow">Sales rhythm</p><h2 class="lp-section-title mt-1">Strongest and weakest days</h2><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><dt class="text-slate-500">Best day</dt><dd class="font-semibold text-emerald-700">{{ $bestSalesDay['day'] }} · {{ $bestSalesDay['count'] }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Weakest day</dt><dd class="font-semibold text-rose-700">{{ $worstSalesDay['day'] }} · {{ $worstSalesDay['count'] }}</dd></div></dl></article>
        <article class="lp-card p-5"><p class="lp-eyebrow">Recommended focus</p><h2 class="lp-section-title mt-1">Your next move</h2><p class="mt-4 text-sm leading-6 text-slate-600">@if($revenueGrowth < 0)Revenue is behind last month. Share a proven product and follow up with returning customers.@elseif(($businessSummary['topProductShare'] ?? 0) > 30)Your top product drives a large share of revenue. Keep it stocked and promote related items.@else Keep building traffic and make product benefits clearer to improve conversion.@endif</p></article>
    </section>
@endsection
