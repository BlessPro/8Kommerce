@php
    $title = 'Payment successful';
@endphp
@extends('layouts.public')

@section('content')
    <div class="mx-auto max-w-2xl rounded-3xl border border-emerald-100 bg-white/90 p-8 text-center shadow-sm">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
            OK
        </div>
        <h1 class="mt-4 text-3xl font-semibold text-slate-900">Payment successful</h1>
        <p class="mt-2 text-sm text-slate-600">Your payment is verified. Save the receipt and tracking reference below.</p>

        @if($payment)
            @php
                $sellerName = $payment->invoice?->user?->sellerProfile?->business_name
                    ?? $payment->product?->user?->sellerProfile?->business_name
                    ?? 'Seller';
                $itemName = $payment->invoice?->title ?? $payment->product?->name ?? 'payment';
                $message = "Hi {$sellerName}, I just paid {$currency} ".number_format((float) $payment->amount, 2, '.', ',')." for {$itemName}. Reference: {$payment->reference}.";
                $orderTrackingUrl = $payment->order?->reference
                    ? route('customer.orders.login')
                    : null;
            @endphp

            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white text-left">
                <div class="border-b border-slate-100 bg-[#f0f2ec] px-5 py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Receipt</p><p class="mt-1 font-semibold text-slate-900">{{ $sellerName }}</p></div>
                        <p class="text-lg font-semibold text-emerald-700">{{ \App\Support\Money::format((string) $payment->amount, $currency) }}</p>
                    </div>
                </div>
                <dl class="grid gap-3 px-5 py-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-400">Payment reference</dt><dd class="mt-1 break-all font-medium text-slate-800">{{ $payment->reference }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Paid</dt><dd class="mt-1 font-medium text-slate-800">{{ ($payment->paid_at ?? $payment->updated_at)?->format('M d, Y · h:i A') }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Payment status</dt><dd class="mt-1 font-semibold text-emerald-700">Verified</dd></div>
                    @if($payment->order)<div><dt class="text-xs text-slate-400">Order reference</dt><dd class="mt-1 break-all font-medium text-slate-800">{{ $payment->order->reference }}</dd></div>@endif
                </dl>
                @if($payment->order?->items?->isNotEmpty())
                    <div class="border-t border-slate-100 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Items</p>
                        <div class="mt-2 divide-y divide-slate-100">
                            @foreach($payment->order->items as $item)
                                <div class="flex items-center justify-between gap-4 py-2 text-sm">
                                    <span class="text-slate-700">{{ $item->product_name }} × {{ $item->quantity }}</span>
                                    <span class="font-medium text-slate-900">{{ \App\Support\Money::format((string) $item->line_total, $currency) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 text-left">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Copy message to seller</p>
                <textarea readonly class="mt-3 w-full rounded-xl border-slate-200 bg-white p-3 text-sm text-slate-700" rows="4">{{ $message }}</textarea>
            </div>
            @if($orderTrackingUrl)
                <a href="{{ $orderTrackingUrl }}" class="mt-4 inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">
                    Track all my orders
                </a>
            @endif
        @else
            @if($reference)
                <p class="mt-6 text-sm text-slate-500">Reference: {{ $reference }}</p>
            @endif
        @endif
    </div>
    @if(! empty($listingUrl))
        <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50/70 p-6 text-center shadow-sm">
            <p class="text-sm text-slate-600">You can return to the listing to pick another product.</p>
            <a href="{{ $listingUrl }}" class="mt-4 inline-flex items-center justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                Back to storefront
            </a>
        </div>
    @endif
@endsection
