@php
    $title = 'Invoices';
@endphp
@extends('layouts.dashboard')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="lp-page-heading">Invoices</h2>
            <p class="lp-page-description">Create and monitor one-time payment links.</p>
        </div>
        <a href="{{ route('invoices.create') }}" class="lp-button-brand">
            New invoice
        </a>
    </div>

    @if($invoices->count())
        @php($latestInvoice = $invoices->first())
        <section class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50/70 p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div><p class="lp-eyebrow text-emerald-700">Latest invoice</p><h3 class="mt-1 text-lg font-semibold text-slate-900">{{ $latestInvoice->title }}</h3><p class="mt-1 text-sm text-slate-600">{{ \App\Support\Money::format($latestInvoice->paid_total, $currency) }} paid of {{ \App\Support\Money::format($latestInvoice->total_amount, $currency) }} · {{ ucfirst($latestInvoice->status) }}</p></div>
                <a href="{{ route('invoices.show', $latestInvoice) }}" class="lp-button-brand">Open invoice</a>
            </div>
        </section>
    @endif

    <div class="lp-card mt-5 overflow-hidden">
        @if($invoices->count())<div class="border-b border-slate-200 px-6 py-4"><h3 class="font-semibold text-slate-900">All invoices</h3></div>@endif
        <div class="divide-y divide-slate-100">
            @forelse($invoices as $invoice)
                <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $invoice->title }}</p>
                        <p class="text-xs text-slate-500">Status: {{ $invoice->status }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <p class="text-sm text-slate-600">
                            {{ \App\Support\Money::format($invoice->paid_total, $currency) }} / {{ \App\Support\Money::format($invoice->total_amount, $currency) }}
                        </p>
                        <a href="{{ route('invoices.show', $invoice) }}" class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 hover:border-emerald-200 hover:text-emerald-700">
                            View
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center"><p class="text-sm font-semibold text-slate-800">No invoices yet</p><p class="mt-1 text-sm text-slate-500">Create a payment link for a customer or service.</p><a href="{{ route('invoices.create') }}" class="lp-button-brand mt-4">Create invoice</a></div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        {{ $invoices->links() }}
    </div>
@endsection
