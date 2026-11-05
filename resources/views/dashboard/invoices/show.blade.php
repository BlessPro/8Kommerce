@php
    $title = 'Invoice';
    $publicUrl = route('public.invoice', $invoice->token);
    $balance = max(0, (float) $invoice->total_amount - (float) $invoice->paid_total);
    $progress = min(100, ((float) $invoice->paid_total / max(0.01, (float) $invoice->total_amount)) * 100);
    $statusTone = in_array(strtolower($invoice->status), ['paid', 'completed'], true) ? 'success' : 'warning';
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header :title="$invoice->title" description="Invoice details, payment progress and the secure link to share with your customer.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="external-link" :href="$publicUrl" target="_blank">Open payment page</x-ui.button>
            <x-ui.button variant="ghost" icon="arrow-left" :href="route('invoices.index')">Invoices</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if(session('status') === 'invoice-created')
        <x-ui.alert tone="success" title="Invoice created" class="mt-5">The payment link is ready to copy and share.</x-ui.alert>
    @endif

    <section class="lp-card mt-6 overflow-hidden">
        <div class="grid gap-6 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
            <div><div class="flex flex-wrap items-center gap-2"><p class="lp-eyebrow">Invoice</p><x-ui.badge :tone="$statusTone">{{ str($invoice->status)->title() }}</x-ui.badge></div><p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">{{ $invoice->description ?: 'No description was added.' }}</p><div class="mt-5 h-2 max-w-2xl overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-600" style="width: {{ $progress }}%"></div></div><p class="mt-2 text-xs text-slate-500">{{ number_format($progress, 0) }}% paid</p></div>
            <div class="grid grid-cols-3 gap-5 lg:text-right"><div><p class="text-xs text-slate-500">Total</p><p class="mt-1 text-lg font-semibold text-slate-950">{{ \App\Support\Money::format($invoice->total_amount, $currency) }}</p></div><div><p class="text-xs text-slate-500">Paid</p><p class="mt-1 text-lg font-semibold text-emerald-700">{{ \App\Support\Money::format($invoice->paid_total, $currency) }}</p></div><div><p class="text-xs text-slate-500">Balance</p><p class="mt-1 text-lg font-semibold text-slate-950">{{ \App\Support\Money::format((string) $balance, $currency) }}</p></div></div>
        </div>
        <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6"><label for="invoice-share-link" class="lp-form-label">Customer payment link</label><div class="mt-2 flex gap-2"><input id="invoice-share-link" value="{{ $publicUrl }}" readonly class="lp-form-control min-w-0 flex-1 bg-white"><x-ui.button type="button" variant="secondary" icon="copy" onclick="navigator.clipboard.writeText(document.getElementById('invoice-share-link').value)">Copy</x-ui.button></div></div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
        <section class="lp-card overflow-hidden"><header class="border-b border-slate-200 px-5 py-4"><p class="lp-eyebrow">Payment history</p><h2 class="lp-section-title mt-1">Transactions</h2></header><div class="divide-y divide-slate-100">@forelse($invoice->payments as $payment)<article class="flex items-center justify-between gap-4 px-5 py-4"><div class="min-w-0"><p class="text-sm font-semibold text-slate-900">{{ \App\Support\Money::format($payment->amount, $currency) }}</p><p class="mt-0.5 truncate text-xs text-slate-500">{{ $payment->reference }}</p></div><div class="text-right"><x-ui.badge :tone="$payment->status === \App\Models\Payment::STATUS_SUCCESS ? 'success' : 'warning'">{{ str($payment->status)->title() }}</x-ui.badge><p class="mt-1 text-xs text-slate-400">{{ $payment->created_at->format('M j, Y') }}</p></div></article>@empty<x-ui.empty-state title="No payments yet" description="Payments will appear here after the customer uses the link." icon="credit-card" class="border-0" />@endforelse</div></section>
        <aside class="lp-card p-5"><p class="lp-eyebrow">Last 30 days</p><h2 class="lp-section-title mt-1">Link performance</h2><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-3"><dt class="text-slate-500">Views</dt><dd class="font-semibold text-slate-900">{{ $stats['views'] }} <span class="font-normal text-slate-400">({{ $stats['viewsUnique'] }} unique)</span></dd></div><div class="flex justify-between gap-3"><dt class="text-slate-500">Payment clicks</dt><dd class="font-semibold text-slate-900">{{ $stats['clicks'] }}</dd></div><div class="flex justify-between gap-3"><dt class="text-slate-500">Payments</dt><dd class="font-semibold text-slate-900">{{ $stats['payments'] }}</dd></div><div class="flex justify-between gap-3"><dt class="text-slate-500">Conversion</dt><dd class="font-semibold text-emerald-700">{{ number_format($stats['conversion'], 1) }}%</dd></div></dl></aside>
    </div>
@endsection
