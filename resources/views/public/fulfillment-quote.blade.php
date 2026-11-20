<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Delivery request · 8Kommerce</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="min-h-screen bg-slate-50 text-slate-950"><main class="mx-auto max-w-xl px-4 py-10">
    <a href="/" class="text-sm font-semibold text-emerald-800">8Kommerce</a>
    <section class="mt-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Delivery request</p><h1 class="mt-2 text-2xl font-semibold">{{ $quote->status === 'REQUESTED' ? 'The seller is checking your rider price' : ($quote->status === 'QUOTED' ? 'Your delivery price is ready' : str($quote->status)->headline()) }}</h1>
        @if(session('status'))<p class="mt-3 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-900">{{ session('status') }}</p>@endif
        @error('quote')<p class="mt-3 rounded-lg bg-rose-50 p-3 text-sm text-rose-800">{{ $message }}</p>@enderror
        <div class="mt-6 divide-y divide-slate-100 border-y border-slate-100">
            @foreach($quote->items as $item)<div class="flex justify-between gap-4 py-3 text-sm"><span>{{ $item['name'] }} × {{ $item['quantity'] }}</span><strong>{{ \App\Support\Money::format($item['line_total'], $quote->currency) }}</strong></div>@endforeach
            <div class="flex justify-between gap-4 py-3 text-sm"><span>Items</span><strong>{{ \App\Support\Money::format($quote->subtotal, $quote->currency) }}</strong></div>
            <div class="flex justify-between gap-4 py-3 text-sm"><span>Rider delivery</span><strong>{{ $quote->delivery_fee !== null ? \App\Support\Money::format($quote->delivery_fee, $quote->currency) : 'Waiting for seller' }}</strong></div>
            @if($quote->total)<div class="flex justify-between gap-4 py-4 text-lg"><span>Total</span><strong>{{ \App\Support\Money::format($quote->total, $quote->currency) }}</strong></div>@endif
        </div>
        <div class="mt-5 rounded-lg bg-slate-50 p-4 text-sm"><strong>Deliver to</strong><p class="mt-1 text-slate-600">{{ $quote->location }}@if($quote->landmark), {{ $quote->landmark }}@endif</p>@if($quote->seller_note)<p class="mt-3 text-slate-700">{{ $quote->seller_note }}</p>@endif</div>
        @if(in_array($quote->status, ['QUOTED', 'PAYMENT_PENDING'], true) && ! $quote->expires_at?->isPast())<form method="POST" action="{{ route('fulfillment-quotes.pay', $quote->public_token) }}" class="mt-5">@csrf<button class="w-full rounded-lg bg-emerald-800 px-5 py-3 font-semibold text-white hover:bg-emerald-900">{{ $quote->status === 'PAYMENT_PENDING' ? 'Continue payment' : 'Pay '.\App\Support\Money::format($quote->total, $quote->currency) }}</button></form><p class="mt-2 text-center text-xs text-slate-500">Price expires {{ $quote->expires_at->diffForHumans() }}.</p>@endif
    </section>
</main></body></html>
