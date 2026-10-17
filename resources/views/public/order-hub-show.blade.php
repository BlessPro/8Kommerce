@php
    $title = 'Order details';
    $seller = $order->user?->sellerProfile;
    $sellerPhone = $seller?->phone;
@endphp
@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <a href="{{ route('customer.orders.index') }}" class="inline-flex text-sm font-semibold text-emerald-700 hover:underline">← Back to my orders</a>
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $seller?->business_name ?? $order->user?->name ?? 'Seller' }}</p><h1 class="mt-2 text-2xl font-semibold text-slate-950">Order {{ $order->reference }}</h1><p class="mt-2 text-sm text-slate-500">Placed {{ $order->created_at?->format('M d, Y · h:i A') }} for {{ $maskedPhone }}</p></div>
            <div class="text-right"><span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ str_replace('_', ' ', $order->status) }}</span><p class="mt-3 text-xl font-semibold text-slate-950">{{ \App\Support\Money::format((string) $order->total, $currency) }}</p></div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Progress</h2>
            <div class="mt-5 space-y-1">
                @foreach($timeline as $entry)
                    <div class="relative flex gap-4 pb-6 last:pb-0">
                        @if(!$loop->last)<span class="absolute left-[7px] top-4 h-full w-px bg-slate-200"></span>@endif
                        <span class="relative mt-1 h-4 w-4 shrink-0 rounded-full border-4 border-white {{ $entry['state'] === 'done' ? 'bg-emerald-500 ring-1 ring-emerald-200' : ($entry['state'] === 'failed' ? 'bg-rose-500 ring-1 ring-rose-200' : 'bg-amber-400 ring-1 ring-amber-200') }}"></span>
                        <div><p class="text-sm font-semibold text-slate-900">{{ $entry['title'] }}</p><p class="mt-1 text-sm leading-5 text-slate-500">{{ $entry['body'] }}</p><p class="mt-1 text-xs text-slate-400">{{ $entry['time'] ? $entry['time']->format('M d, Y · h:i A') : 'Pending update' }}</p></div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Products</h2>
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach($order->items as $item)
                        <div class="flex gap-4 py-4 first:pt-0 last:pb-0">
                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-slate-100">@if($item->product?->image_path)<img src="{{ asset('storage/'.$item->product->image_path) }}" alt="" class="h-full w-full object-cover">@endif</div>
                            <div class="min-w-0 flex-1"><p class="font-semibold text-slate-900">{{ $item->product_name }}</p><p class="mt-1 text-xs text-slate-500">Quantity {{ $item->quantity }} · follows the order status</p><p class="mt-2 text-sm text-slate-600">{{ \App\Support\Money::format((string) $item->unit_price, $currency) }} each</p></div>
                            <p class="shrink-0 font-semibold text-slate-900">{{ \App\Support\Money::format((string) $item->line_total, $currency) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:grid-cols-2">
                <div><h2 class="text-sm font-semibold text-slate-950">Fulfillment</h2><p class="mt-2 text-sm text-slate-600">{{ ucfirst($order->fulfillment_type ?: ($order->delivery_required ? 'delivery' : 'pickup')) }}</p>@if($order->customer_location)<p class="mt-1 text-sm text-slate-500">{{ $order->customer_location }}</p>@endif @if($order->delivery_note)<p class="mt-1 text-xs text-slate-400">{{ $order->delivery_note }}</p>@endif</div>
                <div><h2 class="text-sm font-semibold text-slate-950">Payment receipt</h2>@if($successfulPayment)<p class="mt-2 text-sm font-semibold text-emerald-700">Payment verified</p><p class="mt-1 break-all text-xs text-slate-500">{{ $successfulPayment->reference }}</p><p class="mt-1 text-xs text-slate-400">{{ $successfulPayment->paid_at?->format('M d, Y · h:i A') }}</p>@else<p class="mt-2 text-sm text-amber-700">Payment pending</p>@endif</div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-slate-950 p-6 text-white shadow-sm">
                <h2 class="text-lg font-semibold">Need help?</h2><p class="mt-1 text-sm text-slate-300">Contact {{ $seller?->business_name ?? 'the seller' }} about this order.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if($sellerPhone)<a href="tel:{{ $sellerPhone }}" class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-950">Call seller</a><a href="https://wa.me/{{ ltrim((string) \App\Support\Phone::normalize($sellerPhone, '+233'), '+') }}?text={{ urlencode('Hi, I need help with order '.$order->reference) }}" class="rounded-xl border border-white/30 px-4 py-2 text-sm font-semibold text-white">WhatsApp</a>@endif
                    @if($feedbackToken)<a href="{{ route('public.order.feedback.show', $feedbackToken->token) }}" class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white">Confirm delivery or report issue</a>@endif
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
