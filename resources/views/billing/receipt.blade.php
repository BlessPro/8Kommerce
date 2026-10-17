@php($title = 'Subscription receipt')
@extends('layouts.public')
@section('content')
<main class="mx-auto max-w-2xl rounded-lg border border-slate-200 bg-white p-6 sm:p-9">
    <div class="flex items-start justify-between gap-5 border-b border-slate-200 pb-6"><div><x-brand-mark class="h-10 w-auto"/><h1 class="mt-5 text-2xl font-semibold text-slate-950">Subscription receipt</h1><p class="mt-1 text-sm text-slate-500">{{ $invoice->reference ?: $invoice->provider_invoice_code ?: '8Kommerce billing' }}</p></div><button onclick="window.print()" class="lp-button-secondary print:hidden">Print</button></div>
    <dl class="mt-6 grid gap-5 text-sm sm:grid-cols-2"><div><dt class="text-slate-500">Billed to</dt><dd class="mt-1 font-semibold text-slate-900">{{ $user->name }}</dd><dd class="text-slate-600">{{ $invoice->subscription?->billing_email }}</dd></div><div><dt class="text-slate-500">Plan</dt><dd class="mt-1 font-semibold text-slate-900">{{ $invoice->subscription?->plan_type ?? 'Subscription' }}</dd></div><div><dt class="text-slate-500">Date</dt><dd class="mt-1 font-semibold text-slate-900">{{ ($invoice->paid_at ?: $invoice->created_at)->toFormattedDateString() }}</dd></div><div><dt class="text-slate-500">Status</dt><dd class="mt-1 font-semibold text-slate-900">{{ ucfirst($invoice->status) }}</dd></div></dl>
    <div class="mt-8 flex items-center justify-between border-y border-slate-200 py-5"><span class="font-medium text-slate-700">Monthly subscription</span><strong class="text-xl text-slate-950">{{ $invoice->currency }} {{ number_format((float) $invoice->amount, 2) }}</strong></div>
    <p class="mt-6 text-xs leading-5 text-slate-500">Payment processed securely by Paystack. Keep the reference above when contacting support.</p>
</main>
@endsection
