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
