@php
    $title = 'Orders';
    $orderFilters = [
        'all' => ['label' => 'All orders', 'icon' => 'shopping-bag'],
        'action' => ['label' => 'Needs action', 'icon' => 'alert-circle'],
        'active' => ['label' => 'In progress', 'icon' => 'truck'],
        'completed' => ['label' => 'Completed', 'icon' => 'circle-check'],
        'issues' => ['label' => 'Issues', 'icon' => 'shield-lock'],
    ];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header
        title="Orders"
        description="Move paid orders from acceptance to delivery without losing track of the customer."
    >
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="activity" :href="route('notifications.index')">Activity</x-ui.button>
            <x-ui.button icon="plus" :href="route('invoices.create')">Create order link</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if(session('status') === 'order-updated')
        <x-ui.alert tone="success" title="Order updated" class="mt-5">The customer-facing order journey now shows the new stage.</x-ui.alert>
    @elseif(session('status') === 'orders-bulk-accepted')
        <x-ui.alert tone="success" title="Orders accepted" class="mt-5">{{ session('accepted_count') }} paid {{ str('order')->plural((int) session('accepted_count')) }} accepted. Customer SMS updates were queued.</x-ui.alert>
    @endif
    @if($errors->has('order_ids'))<x-ui.alert tone="danger" class="mt-5">{{ $errors->first('order_ids') }}</x-ui.alert>@endif

    @if($deliveryQuotes->isNotEmpty())
        <section class="mt-6 rounded-xl border border-amber-200 bg-amber-50/60 p-5" aria-labelledby="delivery-quotes-heading">
            <div class="flex items-start justify-between gap-4"><div><p class="lp-eyebrow text-amber-800">Rider delivery</p><h2 id="delivery-quotes-heading" class="mt-1 text-lg font-semibold text-slate-950">Delivery prices to confirm</h2><p class="mt-1 text-sm text-slate-600">Check the rider price, add it here, then the customer receives a secure payment link.</p></div><span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900">{{ $deliveryQuotes->where('status', 'REQUESTED')->count() }} waiting</span></div>
            <div class="mt-4 grid gap-3 lg:grid-cols-2">
                @foreach($deliveryQuotes as $quote)
                    <article class="rounded-lg border border-slate-200 bg-white p-4">
                        <div class="flex justify-between gap-3"><div><strong class="text-sm text-slate-950">{{ $quote->customer_name ?: 'Customer' }}</strong><p class="text-xs text-slate-500">{{ $quote->customer_phone }} · {{ $quote->location }}</p></div><span class="text-xs font-semibold {{ $quote->status === 'REQUESTED' ? 'text-amber-700' : 'text-emerald-700' }}">{{ str($quote->status)->headline() }}</span></div>
                        <p class="mt-3 text-sm text-slate-700">{{ collect($quote->items)->map(fn($item) => $item['name'].' ×'.$item['quantity'])->implode(', ') }}</p>
                        <p class="mt-1 text-xs text-slate-500">Landmark: {{ $quote->landmark ?: 'Not supplied' }} @if($quote->popular_stop) · Stop: {{ $quote->popular_stop }} @endif</p>
                        @if($quote->status === 'REQUESTED')
                            <form method="POST" action="{{ route('fulfillment-quotes.update', $quote) }}" class="mt-4 grid gap-3 sm:grid-cols-2">@csrf @method('PATCH')
                                <label class="text-xs font-semibold text-slate-700">Rider fee (GH₵)<input name="delivery_fee" type="number" min="0" step="0.01" required class="mt-1 w-full rounded-lg border-slate-300 text-sm"></label>
                                <label class="text-xs font-semibold text-slate-700">Link valid for<select name="valid_hours" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="12">12 hours</option><option value="24" selected>24 hours</option><option value="48">48 hours</option></select></label>
                                <label class="sm:col-span-2 text-xs font-semibold text-slate-700">Note to customer<input name="seller_note" maxlength="500" class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="Rider ETA or useful instructions"></label>
                                <div class="sm:col-span-2"><x-ui.button type="submit" size="sm" variant="success">Send price</x-ui.button></div>
                            </form>
                            <form method="POST" action="{{ route('fulfillment-quotes.reject', $quote) }}" class="mt-2">@csrf<x-ui.button type="submit" size="sm" variant="danger">Cannot deliver</x-ui.button></form>
                        @else
                            <p class="mt-3 text-sm font-semibold text-emerald-800">{{ \App\Support\Money::format($quote->delivery_fee, $quote->currency) }} sent · expires {{ $quote->expires_at?->diffForHumans() }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5" aria-label="Order overview">
        @foreach($orderFilters as $key => $item)
            <a
                href="{{ route('products.orders', ['filter' => $key]) }}"
                class="lp-card group flex items-center gap-3 p-4 transition {{ $filter === $key ? 'border-emerald-500 ring-2 ring-emerald-100' : 'hover:border-slate-300' }}"
                @if($filter === $key) aria-current="page" @endif
            >
                <span class="lp-icon-box {{ $key === 'issues' && ($orderSummary[$key] ?? 0) > 0 ? 'bg-rose-50 text-rose-700' : '' }}">
                    <x-ui.icon :name="$item['icon']" size="sm" />
                </span>
                <span class="min-w-0">
                    <span class="block text-xl font-semibold tabular-nums text-slate-950">{{ $orderSummary[$key] ?? 0 }}</span>
                    <span class="block truncate text-xs font-medium text-slate-500">{{ $item['label'] }}</span>
                </span>
            </a>
        @endforeach
    </section>

    <section class="mt-7" aria-labelledby="order-worklist-heading">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="lp-eyebrow">Fulfilment worklist</p>
                <h2 id="order-worklist-heading" class="lp-section-title mt-1">{{ $orderFilters[$filter]['label'] }}</h2>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600"><input type="checkbox" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" data-select-all-paid>Select paid orders</label>
                <form id="bulk-accept-form" method="POST" action="{{ route('notifications.orders.bulk-accept') }}">
                    @csrf
                    <x-ui.button type="submit" variant="success" size="sm" icon="check" disabled data-bulk-accept-button>Accept selected <span data-bulk-selected-count>0</span></x-ui.button>
                </form>
            </div>
        </div>

        @include('dashboard.products.partials.orders-by-customer')
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.querySelector('[data-select-all-paid]');
            const boxes = Array.from(document.querySelectorAll('[data-order-bulk-checkbox]'));
            const button = document.querySelector('[data-bulk-accept-button]');
            const count = document.querySelector('[data-bulk-selected-count]');
            const sync = function () {
                const selected = boxes.filter((box) => box.checked).length;
                if (count) count.textContent = String(selected);
                if (button) button.disabled = selected === 0;
                if (selectAll) {
                    selectAll.checked = boxes.length > 0 && selected === boxes.length;
                    selectAll.indeterminate = selected > 0 && selected < boxes.length;
                }
            };
            selectAll?.addEventListener('change', function () { boxes.forEach((box) => { box.checked = selectAll.checked; }); sync(); });
            boxes.forEach((box) => box.addEventListener('change', sync));
            sync();
        });
    </script>
@endsection
