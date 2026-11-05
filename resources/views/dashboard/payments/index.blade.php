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
    $paymentTone = fn (string $status) => match($status) {
        \App\Models\Payment::STATUS_SUCCESS => 'success',
        \App\Models\Payment::STATUS_PENDING => 'warning',
        \App\Models\Payment::STATUS_FAILED => 'danger',
        default => 'neutral',
    };
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header title="Payments" description="Track money received, identify transactions that need attention, and manage refund requests.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="download" :href="route('payments.export')">Export CSV</x-ui.button>
            <x-ui.button icon="plus" :href="route('invoices.create')">Create invoice</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if($errors->has('payment'))
        <x-ui.alert tone="danger" title="Payment action failed" class="mt-5">{{ $errors->first('payment') }}</x-ui.alert>
    @endif
    @if(session('status') === 'refund-requested')
        <x-ui.alert tone="success" title="Refund requested" class="mt-5">The request was recorded and will be handled through the refund workflow.</x-ui.alert>
    @endif

    <section class="mt-6 grid gap-3 sm:grid-cols-3" aria-label="Payment overview">
        <x-ui.stat-card label="Total received" :value="\App\Support\Money::format($totalReceived, $currency)" meta="Successful transactions" icon="credit-card" />
        <x-ui.stat-card label="Last 30 days" :value="\App\Support\Money::format($last30DaysReceived, $currency)" meta="Recent money received" icon="activity" />
        <x-ui.stat-card label="Pending payments" :value="$pendingCount" meta="Automatically rechecked" icon="warning" />
    </section>

    <section id="transactions-list" class="lp-card mt-6 overflow-hidden" aria-labelledby="transactions-heading">
        <header class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="lp-eyebrow">Money activity</p><h2 id="transactions-heading" class="lp-section-title mt-1">Transactions</h2></div>
                <nav class="flex gap-1 overflow-x-auto" aria-label="Payment filters">
                    @foreach($transactionFilters as $filterKey => $label)
                        <a href="{{ route('payments.index', ['filter' => $filterKey]) }}" class="shrink-0 rounded-md px-3 py-2 text-xs font-semibold {{ $activeFilter === $filterKey ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}" @if($activeFilter === $filterKey) aria-current="page" @endif>
                            {{ $label }} <span class="ml-1 text-[11px] opacity-70">{{ $transactionFilterCounts[$filterKey] ?? 0 }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </header>

        <div class="divide-y divide-slate-100">
            @forelse($payments as $payment)
                @php
                    $isRefundRequested = (bool) data_get($payment->raw_payload, 'refund_requested', false);
                @endphp
                <article class="grid gap-4 px-5 py-4 transition hover:bg-slate-50/70 md:grid-cols-[minmax(0,1fr)_auto_auto] md:items-center">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="lp-icon-box"><x-ui.icon name="credit-card" size="sm" /></span>
                        <div class="min-w-0"><p class="text-sm font-semibold tabular-nums text-slate-950">{{ \App\Support\Money::format($payment->amount, $currency) }}</p><p class="mt-0.5 truncate text-xs text-slate-500">{{ $payment->reference }}</p></div>
                    </div>
                    <div><x-ui.badge :tone="$isRefundRequested ? 'warning' : $paymentTone($payment->status)">{{ $isRefundRequested ? 'Refund requested' : str($payment->status)->title() }}</x-ui.badge><p class="mt-1 text-xs text-slate-400 md:text-right">{{ $payment->created_at->format('M j, Y · H:i') }}</p></div>
                    <div class="flex items-center gap-2 md:justify-end">
                        @if($payment->status === \App\Models\Payment::STATUS_SUCCESS && ! $isRefundRequested)
                            <form method="POST" action="{{ route('payments.refund.request', $payment) }}" onsubmit="return confirm('Request a refund for this payment?')">@csrf<x-ui.button type="submit" variant="danger" size="sm">Request refund</x-ui.button></form>
                        @else
                            <span class="text-xs text-slate-400">{{ $isRefundRequested ? 'Awaiting review' : 'No action needed' }}</span>
                        @endif
                    </div>
                </article>
            @empty
                <x-ui.empty-state title="No transactions found" description="Try another filter or return after receiving a payment." icon="credit-card" class="border-0" />
            @endforelse
        </div>
        @if($payments->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $payments->links() }}</div>@endif
    </section>
@endsection
