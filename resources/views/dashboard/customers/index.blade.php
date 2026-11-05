@php
    $title = 'Customers';
    $statusTone = fn (string $status) => match($status) {
        'Loyal' => 'success',
        'Inactive' => 'danger',
        default => 'info',
    };
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header title="Customers" description="Understand who buys from you, recognise loyal customers, and follow recent activity.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="shopping-bag" :href="route('products.orders')">View orders</x-ui.button>
            <x-ui.button icon="plus" :href="route('invoices.create')">Create payment link</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Customer overview">
        <x-ui.stat-card label="Total customers" :value="$overview['totalCustomers']" :meta="'+' . $overview['newThisMonth'] . ' this month'" icon="users" />
        <x-ui.stat-card label="Returning" :value="$overview['returningCustomers']" :meta="number_format($overview['returnRate'], 1) . '% return rate'" icon="activity" />
        <x-ui.stat-card label="Average value" :value="\App\Support\Money::format((string) $overview['avgCustomerValue'], $currency)" meta="Across paying customers" icon="credit-card" />
        <x-ui.stat-card label="Orders per customer" :value="number_format($overview['ordersPerCustomer'], 1)" meta="Average purchase frequency" icon="shopping-bag" />
    </section>

    <section class="lp-card mt-6 overflow-hidden" aria-labelledby="customer-list-heading">
        <header class="border-b border-slate-200 px-5 py-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="lp-eyebrow">Customer directory</p>
                    <h2 id="customer-list-heading" class="lp-section-title mt-1">People who have purchased</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $customers->total() }} {{ str('customer')->plural($customers->total()) }} in this view</p>
                </div>
                <form method="GET" action="{{ route('customers.index') }}" class="grid gap-3 sm:grid-cols-[170px_minmax(220px,1fr)_auto]">
                    <label>
                        <span class="lp-form-label">Segment</span>
                        <select name="filter" class="lp-form-control mt-1">
                            <option value="all" @selected($filter === 'all')>All customers</option>
                            <option value="new" @selected($filter === 'new')>New customers</option>
                            <option value="returning" @selected($filter === 'returning')>Returning</option>
                            <option value="inactive" @selected($filter === 'inactive')>Inactive</option>
                            <option value="top_spenders" @selected($filter === 'top_spenders')>Top spenders</option>
                        </select>
                    </label>
                    <label>
                        <span class="lp-form-label">Search</span>
                        <span class="relative mt-1 block">
                            <x-ui.icon name="search" size="sm" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                            <input type="search" name="search" value="{{ $search }}" placeholder="Name, phone or email" class="lp-form-control w-full pl-9" />
                        </span>
                    </label>
                    <x-ui.button type="submit" variant="secondary" icon="filter" class="self-end">Apply</x-ui.button>
                </form>
            </div>
        </header>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold text-slate-500">
                    <tr><th class="px-5 py-3">Customer</th><th class="px-4 py-3">Orders</th><th class="px-4 py-3">Total spent</th><th class="px-4 py-3">Last order</th><th class="px-4 py-3">Status</th><th class="px-5 py-3"><span class="sr-only">Open</span></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-700">{{ mb_strtoupper(mb_substr($customer['name'], 0, 1)) }}</span><div><a href="{{ route('customers.show', $customer['encoded_key']) }}" class="font-semibold text-slate-900 hover:text-emerald-700">{{ $customer['name'] }}</a><p class="mt-0.5 text-xs text-slate-500">{{ $customer['phone'] ?: ($customer['email'] ?: 'No contact details') }}</p></div></div>
                            </td>
                            <td class="px-4 py-4 tabular-nums text-slate-600">{{ $customer['orders_count'] }}</td>
                            <td class="px-4 py-4 font-medium tabular-nums text-slate-800">{{ \App\Support\Money::format((string) $customer['total_spent'], $currency) }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $customer['last_purchase_at']?->diffForHumans() ?? '—' }}</td>
                            <td class="px-4 py-4"><x-ui.badge :tone="$statusTone($customer['status'])">{{ $customer['status'] }}</x-ui.badge></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('customers.show', $customer['encoded_key']) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">View <x-ui.icon name="arrow-right" size="xs" /></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-ui.empty-state title="No customers found" description="Try a different search or segment. Customers appear here after successful payments." icon="users" class="border-0" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-100 md:hidden">
            @forelse($customers as $customer)
                <a href="{{ route('customers.show', $customer['encoded_key']) }}" class="block px-5 py-4">
                    <div class="flex items-start justify-between gap-3"><div class="flex min-w-0 items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-700">{{ mb_strtoupper(mb_substr($customer['name'], 0, 1)) }}</span><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-900">{{ $customer['name'] }}</p><p class="truncate text-xs text-slate-500">{{ $customer['phone'] ?: ($customer['email'] ?: 'No contact details') }}</p></div></div><x-ui.badge :tone="$statusTone($customer['status'])">{{ $customer['status'] }}</x-ui.badge></div>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-xs"><div><p class="text-slate-400">Orders</p><p class="mt-1 font-semibold text-slate-800">{{ $customer['orders_count'] }}</p></div><div><p class="text-slate-400">Spent</p><p class="mt-1 font-semibold text-slate-800">{{ \App\Support\Money::format((string) $customer['total_spent'], $currency) }}</p></div><div><p class="text-slate-400">Last order</p><p class="mt-1 font-semibold text-slate-800">{{ $customer['last_purchase_at']?->diffForHumans() ?? '—' }}</p></div></div>
                </a>
            @empty
                <x-ui.empty-state title="No customers found" description="Try a different search or segment." icon="users" class="border-0" />
            @endforelse
        </div>
        @if($customers->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $customers->links() }}</div>@endif
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1fr_1fr]" aria-label="Customer insights">
        <article class="lp-card p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4"><div><p class="lp-eyebrow">Relationship health</p><h2 class="lp-section-title mt-1">Customer segments</h2></div><x-ui.icon name="users" class="text-slate-400" /></div>
            <div class="mt-5 grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-slate-200 bg-slate-200">
                @foreach([
                    ['VIP customers', $segments['vip'], 'Spend over '.\App\Support\Money::format('500', $currency)],
                    ['Regulars', $segments['regular'], 'Two to four orders'],
                    ['New customers', $segments['new'], 'First purchase'],
                    ['Inactive', $segments['inactive'], 'No order in 30 days'],
                ] as [$label, $value, $meta])
                    <div class="bg-white p-4"><p class="text-xs font-medium text-slate-500">{{ $label }}</p><p class="mt-2 text-xl font-semibold text-slate-950">{{ $value }}</p><p class="mt-1 text-xs text-slate-400">{{ $meta }}</p></div>
                @endforeach
            </div>
        </article>

        <article class="lp-card p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4"><div><p class="lp-eyebrow">Highest lifetime value</p><h2 class="lp-section-title mt-1">Top customers</h2></div><x-ui.icon name="target" class="text-slate-400" /></div>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse($topCustomers->take(5) as $row)
                    <a href="{{ route('customers.show', $row['encoded_key']) }}" class="flex items-center justify-between gap-4 py-3"><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800">{{ $row['name'] }}</p><p class="text-xs text-slate-500">{{ $row['orders_count'] }} {{ str('order')->plural($row['orders_count']) }}</p></div><span class="shrink-0 text-sm font-semibold tabular-nums text-slate-900">{{ \App\Support\Money::format((string) $row['total_spent'], $currency) }}</span></a>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">No customer spend data yet.</p>
                @endforelse
            </div>
        </article>
    </section>
@endsection
