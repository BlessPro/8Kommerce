@php
    $title = 'Customer profile';
    $initial = mb_strtoupper(mb_substr($customer['name'], 0, 1));
    $statusTone = match($customer['status']) {
        'Loyal' => 'success',
        'Inactive' => 'danger',
        default => 'info',
    };
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header title="Customer profile" description="Purchase history and relationship details from completed transactions.">
        <x-slot:actions>
            @if($customer['phone'])
                <x-ui.button variant="secondary" icon="phone" href="tel:{{ $customer['phone'] }}">Call</x-ui.button>
                <x-ui.button variant="secondary" icon="whatsapp" href="https://wa.me/{{ preg_replace('/\D+/', '', $customer['phone']) }}" target="_blank" rel="noreferrer noopener">WhatsApp</x-ui.button>
            @endif
            <x-ui.button variant="ghost" icon="arrow-left" :href="route('customers.index')">Customers</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="lp-card mt-6 overflow-hidden">
        <div class="flex flex-col gap-5 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div class="flex min-w-0 items-center gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-lg font-semibold text-emerald-800">{{ $initial }}</span>
                <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h2 class="truncate text-xl font-semibold text-slate-950">{{ $customer['name'] }}</h2><x-ui.badge :tone="$statusTone">{{ $customer['status'] }}</x-ui.badge></div><p class="mt-1 truncate text-sm text-slate-500">{{ $customer['phone'] ?: ($customer['email'] ?: 'No contact details') }}</p></div>
            </div>
            <div class="grid grid-cols-3 gap-5 text-right">
                <div><p class="text-xs text-slate-500">Orders</p><p class="mt-1 text-lg font-semibold text-slate-950">{{ $customer['orders_count'] }}</p></div>
                <div><p class="text-xs text-slate-500">Total spent</p><p class="mt-1 text-lg font-semibold text-slate-950">{{ \App\Support\Money::format((string) $customer['total_spent'], $currency) }}</p></div>
                <div><p class="text-xs text-slate-500">Average</p><p class="mt-1 text-lg font-semibold text-slate-950">{{ \App\Support\Money::format((string) $customer['avg_order_value'], $currency) }}</p></div>
            </div>
        </div>
        <dl class="grid gap-px bg-slate-200 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['Phone', $customer['phone'] ?: 'Not provided'],
                ['Email', $customer['email'] ?: 'Not provided'],
                ['Location', $customer['location'] ?: 'Not provided'],
                ['Customer since', $customer['first_purchase_at']?->format('M j, Y') ?: '—'],
            ] as [$label, $value])
                <div class="bg-white px-5 py-4"><dt class="text-xs font-medium text-slate-500">{{ $label }}</dt><dd class="mt-1 truncate text-sm font-semibold text-slate-800">{{ $value }}</dd></div>
            @endforeach
        </dl>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(260px,0.5fr)] xl:items-start">
        <section class="lp-card overflow-hidden" aria-labelledby="purchase-history-heading">
            <header class="border-b border-slate-200 px-5 py-4"><p class="lp-eyebrow">Timeline</p><h2 id="purchase-history-heading" class="lp-section-title mt-1">Purchase history</h2></header>
            <div class="divide-y divide-slate-100">
                @forelse($customer['purchase_history'] as $row)
                    <article class="grid gap-3 px-5 py-4 sm:grid-cols-[110px_minmax(0,1fr)_auto] sm:items-center">
                        <time class="text-xs font-medium text-slate-500">{{ $row['date']->format('M j, Y') }}</time>
                        <div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800">{{ $row['product'] }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $row['status'] }}</p></div>
                        <span class="text-sm font-semibold tabular-nums text-slate-950">{{ \App\Support\Money::format((string) $row['amount'], $currency) }}</span>
                    </article>
                @empty
                    <x-ui.empty-state title="No purchases yet" description="Completed orders will appear in this timeline." icon="shopping-bag" class="border-0" />
                @endforelse
            </div>
        </section>

        <aside class="lp-card p-5 sm:p-6">
            <p class="lp-eyebrow">Buying pattern</p>
            <h2 class="lp-section-title mt-1">Favourite products</h2>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse($customer['favorite_products'] as $product)
                    <div class="flex items-center justify-between gap-3 py-3"><span class="min-w-0 truncate text-sm font-medium text-slate-700">{{ $product['name'] }}</span><x-ui.badge>{{ $product['count'] }} bought</x-ui.badge></div>
                @empty
                    <p class="py-6 text-sm text-slate-500">More purchase history is needed to identify favourites.</p>
                @endforelse
            </div>
            <div class="mt-5 border-t border-slate-200 pt-4"><p class="text-xs text-slate-500">Last purchase</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ $customer['last_purchase_at']?->format('M j, Y') ?: 'No purchase date' }}</p></div>
        </aside>
    </div>
@endsection
