@php
    $title = 'Activity';
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header title="Activity and order decisions" description="Handle new paid orders first, then review updates from across the store.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="shopping-bag" :href="route('products.orders')">All orders</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if(session('status') === 'order-updated')
        <x-ui.alert tone="success" title="Order updated" class="mt-5">The customer journey and activity history now reflect the new status.</x-ui.alert>
    @endif
    @if($errors->has('order'))
        <x-ui.alert tone="danger" title="Order could not be updated" class="mt-5">{{ $errors->first('order') }}</x-ui.alert>
    @endif

    <section class="mt-6" aria-labelledby="decision-heading">
        <div class="flex items-end justify-between gap-4"><div><p class="lp-eyebrow">Needs action</p><h2 id="decision-heading" class="lp-section-title mt-1">Orders awaiting your decision</h2></div><x-ui.badge :tone="$pendingOrders->isEmpty() ? 'success' : 'warning'">{{ $pendingOrders->count() }} waiting</x-ui.badge></div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            @forelse($pendingOrders as $order)
                <article id="order-{{ $order->id }}" class="lp-card scroll-mt-24 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-start gap-3"><span class="lp-icon-box"><x-ui.icon name="shopping-bag" /></span><div class="min-w-0"><p class="text-sm font-semibold text-slate-900">Order {{ $order->reference }}</p><p class="mt-1 truncate text-xs text-slate-500">{{ $order->customer_name ?: 'Customer' }} · {{ $order->customer_phone }}</p></div></div>
                        <div class="text-right"><x-ui.badge tone="warning">Paid · new</x-ui.badge><p class="mt-2 text-base font-semibold text-slate-900">{{ \App\Support\Money::format((string) $order->total, $currency ?? 'GHS') }}</p></div>
                    </div>

                    <div class="mt-4 grid gap-3 border-y border-slate-100 py-4 text-xs sm:grid-cols-2">
                        <div><p class="font-semibold text-slate-500">Fulfilment</p><p class="mt-1 text-slate-800">{{ $order->delivery_required ? 'Delivery' : 'Pickup' }}@if($order->delivery_note) · {{ $order->delivery_note }}@endif</p></div>
                        <div><p class="font-semibold text-slate-500">Products</p><p class="mt-1 text-slate-800">{{ $order->items->sum('quantity') }} item{{ $order->items->sum('quantity') === 1 ? '' : 's' }} across {{ $order->items->count() }} product{{ $order->items->count() === 1 ? '' : 's' }}</p></div>
                    </div>

                    <ul class="mt-3 space-y-2">
                        @foreach($order->items as $item)
                            <li class="flex items-center justify-between gap-4 text-xs"><span class="min-w-0 truncate text-slate-600">{{ $item->product_name }} × {{ $item->quantity }}</span><span class="shrink-0 font-semibold text-slate-800">{{ \App\Support\Money::format((string) $item->line_total, $currency ?? 'GHS') }}</span></li>
                        @endforeach
                    </ul>

                    <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
                        <form method="POST" action="{{ route('notifications.orders.reject', $order) }}" onsubmit="return confirm('Cancel this paid order and request a refund?')">@csrf<x-ui.button type="submit" variant="danger" size="sm" class="w-full sm:w-auto">Cancel & request refund</x-ui.button></form>
                        <form method="POST" action="{{ route('notifications.orders.accept', $order) }}">@csrf<x-ui.button type="submit" variant="success" icon="check" size="sm" class="w-full sm:w-auto">Accept order</x-ui.button></form>
                    </div>
                </article>
            @empty
                <x-ui.empty-state title="No paid orders need a decision" description="New paid orders will be collected here so they are difficult to miss." icon="circle-check" class="lg:col-span-2" />
            @endforelse
        </div>
    </section>

    <section class="mt-8" aria-labelledby="timeline-heading">
        <div class="flex items-end justify-between gap-4"><div><p class="lp-eyebrow">Store timeline</p><h2 id="timeline-heading" class="lp-section-title mt-1">Recent updates</h2></div><span class="text-xs text-slate-500">Newest first</span></div>
        <div class="lp-card mt-4 overflow-hidden">
            <div class="divide-y divide-slate-100">
                @forelse($notifications as $notification)
                    @php
                        $notificationIcon = str_contains(strtolower($notification->title), 'payment') ? 'credit-card' : (str_contains(strtolower($notification->title), 'order') ? 'shopping-bag' : 'bell');
                    @endphp
                    <article class="grid gap-3 px-5 py-4 sm:grid-cols-[auto_minmax(0,1fr)_auto] sm:items-start">
                        <span class="lp-icon-box"><x-ui.icon :name="$notificationIcon" size="sm" /></span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900">{{ $notification->title }}</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notification->body }}</p>

                            @if(is_array($notification->data) && count($notification->data))
                                <dl class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
                                    @foreach($notification->data as $key => $value)
                                        @if(! is_array($value) && ! is_object($value) && $value !== null && $value !== '')
                                            <div class="flex gap-1">
                                                <dt class="font-semibold text-slate-600">{{ str_replace('_', ' ', ucfirst((string) $key)) }}:</dt>
                                                <dd>{{ (string) $value }}</dd>
                                            </div>
                                        @endif
                                    @endforeach
                                </dl>
                            @endif
                        </div>
                        <time class="text-xs text-slate-400" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                    </article>
                @empty
                    <x-ui.empty-state title="No activity yet" description="Order, payment and system updates will appear here." icon="activity" class="border-0" />
                @endforelse
            </div>
            @if($notifications->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $notifications->links() }}</div>@endif
        </div>
    </section>
@endsection
