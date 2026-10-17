@php
    $title = 'My orders';
@endphp
@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Customer tracking</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">My orders</h1>
            <p class="mt-1 text-sm text-slate-500">Orders paid with {{ $maskedPhone }}</p>
        </div>
        <form method="POST" action="{{ route('customer.orders.logout') }}">@csrf<button class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-rose-300 hover:text-rose-700">Sign out</button></form>
    </div>

    <nav class="grid grid-cols-3 gap-2 rounded-2xl bg-slate-100 p-1.5" aria-label="Order filters">
        @foreach(['active' => 'Active', 'completed' => 'Completed', 'issues' => 'Issues'] as $key => $label)
            <a href="{{ route('customer.orders.index', ['tab' => $key]) }}" class="rounded-xl px-3 py-2.5 text-center text-sm font-semibold {{ $tab === $key ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">{{ $label }} <span class="ml-1 text-xs">{{ $counts[$key] }}</span></a>
        @endforeach
    </nav>

    @forelse($orders as $order)
        @php
            $seller = $order->user?->sellerProfile;
            $tone = in_array($order->status, [\App\Models\Order::STATUS_CANNOT_FULFILL, \App\Models\Order::STATUS_DISPUTED_PENDING_ADMIN, \App\Models\Order::STATUS_COMPLAINT_REJECTED]) ? 'bg-rose-50 text-rose-700' : (in_array($order->status, [\App\Models\Order::STATUS_COMPLETED, \App\Models\Order::STATUS_REFUNDED]) ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700');
        @endphp
        <a href="{{ route('customer.orders.show', $order) }}" class="block rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div><p class="font-semibold text-slate-950">{{ $seller?->business_name ?? $order->user?->name ?? 'Seller' }}</p><p class="mt-1 text-xs text-slate-400">{{ $order->created_at?->format('M d, Y') }} · {{ $order->reference }}</p></div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $tone }}">{{ str_replace('_', ' ', ucfirst(strtolower($order->status))) }}</span>
            </div>
            <div class="mt-4 flex items-center justify-between gap-4 border-t border-slate-100 pt-4">
                <div class="min-w-0"><p class="truncate text-sm text-slate-600">{{ $order->items->pluck('product_name')->take(3)->join(', ') }}{{ $order->items->count() > 3 ? ' +' . ($order->items->count() - 3) : '' }}</p><p class="mt-1 text-xs text-slate-400">{{ $order->items->sum('quantity') }} item(s) · {{ ucfirst($order->fulfillment_type ?: ($order->delivery_required ? 'delivery' : 'pickup')) }}</p></div>
                <div class="shrink-0 text-right"><p class="font-semibold text-slate-950">{{ \App\Support\Money::format((string) $order->total, $currency) }}</p><p class="mt-1 text-xs font-semibold text-emerald-700">View details →</p></div>
            </div>
        </a>
    @empty
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center"><p class="text-lg font-semibold text-slate-800">No {{ $tab }} orders</p><p class="mt-2 text-sm text-slate-500">Orders in this category will appear here.</p></div>
    @endforelse

    {{ $orders->links() }}
</div>
@endsection
