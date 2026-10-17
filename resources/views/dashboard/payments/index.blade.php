@php
    $title = 'Payments';
    $transactionFilters = [
        'all' => 'All',
        'new' => 'New',
        'success' => 'Successful',
        'pending' => 'Pending',
        'failed' => 'Failed',
        'refund_requested' => 'Refund requested',
    ];
@endphp
@extends('layouts.dashboard')

@section('content')
    <div>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="lp-page-heading">Payments</h2>
                <p class="lp-page-description">Monitor settlement activity, transaction status, and refund requests.</p>
            </div>
            <a href="{{ route('payments.export') }}" class="lp-button-secondary" title="Export">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 15.75v2.25A2.25 2.25 0 0 0 6.75 20.25h10.5A2.25 2.25 0 0 0 19.5 18v-2.25"/></svg>
                Export CSV
            </a>
        </div>

        <div class="lp-card mt-5 grid overflow-hidden sm:grid-cols-3">
            <div class="border-b border-slate-200 p-4 sm:border-b-0 sm:border-r sm:p-5">
                <p class="lp-eyebrow">Total received</p>
                <p class="mt-2 text-xl font-semibold tabular-nums text-slate-900 sm:text-2xl">{{ \App\Support\Money::format($totalReceived, $currency) }}</p>
            </div>
            <div class="border-b border-slate-200 p-4 sm:border-b-0 sm:border-r sm:p-5">
                <p class="lp-eyebrow">Last 30 days</p>
                <p class="mt-2 text-xl font-semibold tabular-nums text-slate-900 sm:text-2xl">{{ \App\Support\Money::format($last30DaysReceived, $currency) }}</p>
            </div>
            <div class="p-4 sm:p-5">
                <p class="lp-eyebrow">Pending</p>
                <p class="mt-2 text-xl font-semibold tabular-nums text-slate-900 sm:text-2xl">{{ $pendingCount }}</p>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button type="button" class="lp-button-secondary" onclick="document.getElementById('transactions-list')?.scrollIntoView({behavior: 'smooth', block: 'start'});">
                Review transactions
            </button>
            <a href="{{ route('invoices.create') }}" class="lp-button-brand">
                New invoice
            </a>
        </div>

        @if($errors->has('payment'))
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
                {{ $errors->first('payment') }}
            </div>
        @endif
        @if(session('status') === 'refund-requested')
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                Refund request submitted. We will process it shortly.
            </div>
        @endif

        <div id="transactions-list" class="lp-card mt-5 overflow-hidden">
            <details class="group" {{ $activeFilter !== 'all' ? 'open' : '' }}>
                <summary class="flex cursor-pointer list-none items-center justify-between border-b border-slate-200 px-4 py-3">
                    <h3 class="text-sm font-semibold text-slate-900">Transactions</h3>
                    <span class="inline-flex items-center gap-2 text-xs font-medium text-slate-500">
                        {{ $transactionFilters[$activeFilter] ?? 'All' }}
                        <svg class="h-4 w-4 transition group-open:rotate-90" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                    </span>
                </summary>

                <div class="flex gap-1 overflow-x-auto border-b border-slate-200 px-4 py-2">
                    @foreach($transactionFilters as $filterKey => $label)
                        <a
                            href="{{ route('payments.index', ['filter' => $filterKey]) }}"
                            class="shrink-0 rounded px-3 py-1.5 text-xs font-semibold {{ $activeFilter === $filterKey ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}"
                        >
                            {{ $label }} <span class="ml-1 text-[11px] text-slate-400">{{ $transactionFilterCounts[$filterKey] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            </details>

            <div class="divide-y divide-slate-200">
                @forelse($payments as $payment)
                    @php
                        $isRefundRequested = (bool) data_get($payment->raw_payload, 'refund_requested', false);
                    @endphp
                    <div class="bg-white px-4 py-3 hover:bg-slate-50">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ \App\Support\Money::format($payment->amount, $currency) }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-500">{{ $payment->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="rounded-full px-2 py-1 text-[11px] font-semibold
                                    {{ $payment->status === \App\Models\Payment::STATUS_SUCCESS ? 'bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $payment->status === \App\Models\Payment::STATUS_PENDING ? 'bg-amber-50 text-amber-700' : '' }}
                                    {{ $payment->status === \App\Models\Payment::STATUS_FAILED ? 'bg-rose-50 text-rose-700' : '' }}
                                ">{{ $payment->status }}</span>
                                @if($isRefundRequested)
                                    <p class="mt-1 text-[11px] font-semibold text-fuchsia-600">REFUND REQUESTED</p>
                                @endif
                            </div>
                        </div>
                        <p class="mt-2 break-all text-[11px] text-slate-500">Ref: {{ $payment->reference }}</p>
                        <div class="mt-3 flex items-center gap-2 overflow-x-auto pb-1">
                            @if($payment->status === \App\Models\Payment::STATUS_SUCCESS && ! $isRefundRequested)
                                <form method="POST" action="{{ route('payments.refund.request', $payment) }}">
                                    @csrf
                                    <button type="submit" class="rounded-full border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50">
                                        Request refund
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('payments.index', ['filter' => $activeFilter]) }}" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600">
                                Details
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center"><p class="text-sm font-semibold text-slate-800">No transactions found</p><p class="mt-1 text-sm text-slate-500">Try another filter or return after receiving a payment.</p></div>
                @endforelse
            </div>
        </div>

        <div class="mt-5">
            {{ $payments->links() }}
        </div>
    </div>
@endsection
