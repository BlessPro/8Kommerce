@php
    $title = 'Invoices';
    $latestInvoice = $invoices->first();
    $invoiceTone = fn (string $status) => match(strtolower($status)) {
        'paid', 'completed' => 'success',
        'pending', 'partial' => 'warning',
        'cancelled', 'failed' => 'danger',
        default => 'neutral',
    };
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header title="Invoices" description="Create shareable payment links and follow each invoice from sent to paid.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="credit-card" :href="route('payments.index')">Payments</x-ui.button>
            <x-ui.button icon="plus" :href="route('invoices.create')">New invoice</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if($latestInvoice)
        @php
            $latestTotal = max(0.01, (float) $latestInvoice->total_amount);
            $latestProgress = min(100, ((float) $latestInvoice->paid_total / $latestTotal) * 100);
        @endphp
        <section class="lp-card mt-6 border-emerald-200 p-5 sm:p-6" aria-labelledby="latest-invoice-heading">
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                <div>
                    <div class="flex flex-wrap items-center gap-2"><p class="lp-eyebrow text-emerald-700">Latest invoice</p><x-ui.badge :tone="$invoiceTone($latestInvoice->status)">{{ str($latestInvoice->status)->title() }}</x-ui.badge></div>
                    <h2 id="latest-invoice-heading" class="mt-2 text-xl font-semibold text-slate-950">{{ $latestInvoice->title }}</h2>
                    <div class="mt-4 h-2 max-w-xl overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-600" style="width: {{ $latestProgress }}%"></div></div>
                    <p class="mt-2 text-sm text-slate-600">{{ \App\Support\Money::format($latestInvoice->paid_total, $currency) }} paid of {{ \App\Support\Money::format($latestInvoice->total_amount, $currency) }}</p>
                </div>
                <x-ui.button icon="arrow-right" :href="route('invoices.show', $latestInvoice)">Open invoice</x-ui.button>
            </div>
        </section>
    @endif

    <section class="lp-card mt-6 overflow-hidden" aria-labelledby="invoice-list-heading">
        <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><p class="lp-eyebrow">Payment links</p><h2 id="invoice-list-heading" class="lp-section-title mt-1">All invoices</h2></div><x-ui.badge>{{ $invoices->total() }} total</x-ui.badge></header>
        <div class="divide-y divide-slate-100">
            @forelse($invoices as $invoice)
                @php
                    $remaining = max(0, (float) $invoice->total_amount - (float) $invoice->paid_total);
                @endphp
                <article class="grid gap-3 px-5 py-4 transition hover:bg-slate-50/70 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center">
                    <div class="flex min-w-0 items-center gap-3"><span class="lp-icon-box"><x-ui.icon name="file-invoice" size="sm" /></span><div class="min-w-0"><a href="{{ route('invoices.show', $invoice) }}" class="truncate text-sm font-semibold text-slate-900 hover:text-emerald-700">{{ $invoice->title }}</a><p class="mt-0.5 text-xs text-slate-500">Created {{ $invoice->created_at->format('M j, Y') }}</p></div></div>
                    <div class="sm:text-right"><p class="text-sm font-semibold tabular-nums text-slate-900">{{ \App\Support\Money::format($invoice->paid_total, $currency) }} / {{ \App\Support\Money::format($invoice->total_amount, $currency) }}</p><p class="text-xs text-slate-500">{{ \App\Support\Money::format((string) $remaining, $currency) }} remaining</p></div>
                    <div class="flex items-center gap-3 sm:justify-end"><x-ui.badge :tone="$invoiceTone($invoice->status)">{{ str($invoice->status)->title() }}</x-ui.badge><a href="{{ route('invoices.show', $invoice) }}" aria-label="Open {{ $invoice->title }}" class="text-emerald-700"><x-ui.icon name="arrow-right" size="sm" /></a></div>
                </article>
            @empty
                <x-ui.empty-state title="No invoices yet" description="Create a payment link for a customer, custom order or service." icon="file-invoice" class="border-0">
                    <x-slot:action><x-ui.button icon="plus" :href="route('invoices.create')">Create invoice</x-ui.button></x-slot:action>
                </x-ui.empty-state>
            @endforelse
        </div>
        @if($invoices->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $invoices->links() }}</div>@endif
    </section>
@endsection
