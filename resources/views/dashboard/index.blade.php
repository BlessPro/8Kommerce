@php
    $title = 'Overview';
    $listingUrl = $profile ? route('public.listing', $profile->public_slug) : null;
    $greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

@extends('layouts.dashboard')

@section('content')
    <section class="border-b border-slate-200 pb-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <p class="text-sm font-semibold text-emerald-700">{{ $greeting }}, {{ $profile?->business_name ?? 'Seller' }}</p>
                <h2 class="lp-page-heading mt-1">Store overview</h2>
                <p class="lp-page-description">Review this month's performance, handle anything that needs attention, and keep selling.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($listingUrl)
                    <a href="{{ $listingUrl }}" target="_blank" rel="noreferrer noopener" class="lp-button-secondary">View store</a>
                @endif
                <button type="button" class="js-quick-actions-open lp-button-brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Create new
                </button>
            </div>
        </div>
    </section>

    <section class="lp-card mt-5 grid grid-cols-2 overflow-hidden lg:grid-cols-4" aria-label="Store performance this month">
        @php
            $metrics = [
                ['label' => 'Amount received', 'value' => \App\Support\Money::format($amountReceived, $currency), 'hint' => 'Successful payments in '.now()->format('F'), 'tone' => 'bg-emerald-50 text-emerald-700'],
                ['label' => 'Orders', 'value' => number_format($ordersCount), 'hint' => 'Confirmed this month', 'tone' => 'bg-slate-100 text-slate-600'],
                ['label' => 'Store visits', 'value' => number_format($trafficCount), 'hint' => 'Views and product clicks', 'tone' => 'bg-slate-100 text-slate-600'],
                ['label' => 'Conversion', 'value' => number_format($salesConversionRate, 1).'%', 'hint' => number_format($newCustomersCount).' new customers', 'tone' => 'bg-slate-100 text-slate-600'],
            ];
        @endphp
        @foreach($metrics as $metric)
            <article class="border-b border-r border-slate-200 p-4 last:border-r-0 lg:border-b-0 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-500">{{ $metric['label'] }}</p>
                        <p class="mt-2 truncate text-xl font-semibold tabular-nums tracking-tight text-slate-950 sm:text-2xl">{{ $metric['value'] }}</p>
                    </div>
                    <span class="inline-flex h-3 w-3 shrink-0 rounded-full {{ $metric['tone'] }}"></span>
                </div>
                <p class="mt-2 text-xs text-slate-500">{{ $metric['hint'] }}</p>
            </article>
        @endforeach
    </section>

    @if($inventoryAlerts->isNotEmpty())
        <section class="mt-5 rounded-lg border border-amber-200 bg-[#fffaf0] p-4 sm:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.3 4.2 2.7 17.4A1.5 1.5 0 0 0 4 19.7h16a1.5 1.5 0 0 0 1.3-2.3L13.7 4.2a1.5 1.5 0 0 0-3.4 0Z"/></svg>
                    </span>
                    <div>
                        <h2 class="font-semibold text-amber-950">{{ $inventoryAlerts->count() }} product{{ $inventoryAlerts->count() === 1 ? '' : 's' }} need attention</h2>
                        <p class="mt-1 text-sm text-amber-800">Restock low inventory before customers reach checkout.</p>
                    </div>
                </div>
                <a href="{{ route('products.index', ['stock' => \App\Models\Product::STATUS_LOW_STOCK]) }}" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-amber-950 px-4 py-2 text-sm font-semibold text-white">Review inventory</a>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($inventoryAlerts as $item)
                    <a href="{{ route('products.edit', $item) }}" class="rounded-xl border border-amber-200/70 bg-white/80 px-4 py-3 transition hover:bg-white">
                        <div class="flex items-center justify-between gap-3">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $item->name }}</p>
                            <span class="text-xs font-semibold text-amber-700">Qty {{ $item->stock_quantity }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $item->status === \App\Models\Product::STATUS_SOLD_OUT ? 'Sold out' : 'Low stock' }} · Edit product</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-5 grid items-start gap-5 lg:grid-cols-[1.15fr_.85fr]">
        <article class="lp-card p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4">
                <div><p class="lp-eyebrow">Latest</p><h2 class="lp-section-title mt-1">Recent activity</h2></div>
                <a href="{{ route('notifications.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-600">View all</a>
            </div>
            <div class="mt-5 divide-y divide-[#e8ebe6]">
                @forelse($activity as $item)
                    <div class="flex items-start gap-3 py-4 first:pt-0 last:pb-0">
                        <span class="lp-icon-box mt-0.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16v10H4V7Zm0 3h16"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $item['title'] }}</p>
                                <span class="whitespace-nowrap text-xs text-slate-400">{{ $item['created_at']->diffForHumans() }}</span>
                            </div>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $item['subtitle'] }}</p>
                        </div>
                        @if($item['type'] === 'payment')
                            <p class="text-sm font-semibold text-emerald-700">{{ \App\Support\Money::format($item['amount'], $currency) }}</p>
                        @endif
                    </div>
                @empty
                    <div class="py-10 text-center"><p class="text-sm font-medium text-slate-700">No activity yet</p><p class="mt-1 text-sm text-slate-400">New orders and payments will appear here.</p></div>
                @endforelse
            </div>
        </article>

        <article class="lp-card p-5 sm:p-6">
            <div><p class="lp-eyebrow">Customers</p><h2 class="lp-section-title mt-1">Your strongest relationships</h2></div>
            <div class="mt-5 border-y border-emerald-200 bg-[#f2f7f4] px-1 py-4">
                <p class="text-sm text-emerald-800">Average successful payment</p>
                <p class="mt-1 text-2xl font-semibold tracking-tight text-emerald-950">{{ \App\Support\Money::format($averagePayment, $currency) }}</p>
            </div>
            <div class="mt-4 space-y-1">
                @forelse($topCustomers as $customer)
                    <div class="flex items-center justify-between gap-4 rounded-md px-2 py-3 hover:bg-[#f4f6f4]">
                        <div class="min-w-0"><p class="truncate text-sm font-medium text-slate-800">{{ $customer['email'] }}</p><p class="text-xs text-slate-400">{{ $customer['count'] }} payment{{ $customer['count'] === 1 ? '' : 's' }}</p></div>
                        <span class="text-sm font-semibold text-slate-700">{{ \App\Support\Money::format($customer['total'], $currency) }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">Customer insights will appear after your first sale.</p>
                @endforelse
            </div>
            <a href="{{ route('customers.index') }}" class="mt-4 inline-flex text-sm font-semibold text-emerald-700 hover:text-emerald-600">View customers →</a>
        </article>
    </section>

    <section class="mt-5 grid gap-5 lg:grid-cols-2">
        <article class="lp-card p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4">
                <div><p class="lp-eyebrow">Get paid</p><h2 class="lp-section-title mt-1">Invoices ready to share</h2></div>
                <a href="{{ route('invoices.index') }}" class="text-sm font-semibold text-emerald-700">View all</a>
            </div>
            <div class="mt-5 divide-y divide-[#e8ebe6]">
                @forelse($recentInvoices as $invoice)
                    @php
                        $invoiceLink = route('public.invoice', $invoice->token);
                        $invoiceMessage = "Hi, here is your invoice for {$invoice->title}. Pay here: {$invoiceLink}";
                    @endphp
                    <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-900">{{ $invoice->title }}</p><p class="mt-1 text-xs capitalize text-slate-400">{{ $invoice->status }}</p></div>
                        <a href="{{ route('invoices.show', $invoice) }}" class="text-sm font-semibold text-slate-600">Open</a>
                        <button type="button" data-copy="{{ $invoiceMessage }}" class="lp-button-secondary min-h-9 px-3 py-1.5 text-xs">Copy link</button>
                    </div>
                @empty
                    <div class="py-7 text-center"><p class="text-sm text-slate-500">No invoices yet.</p><a href="{{ route('invoices.create') }}" class="mt-2 inline-flex text-sm font-semibold text-emerald-700">Create an invoice</a></div>
                @endforelse
            </div>
        </article>

        <article class="lp-card p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4">
                <div><p class="lp-eyebrow">Your catalog</p><h2 class="lp-section-title mt-1">Products ready to share</h2></div>
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-emerald-700">View all</a>
            </div>
            <div class="mt-5 divide-y divide-[#e8ebe6]">
                @forelse($recentProducts as $product)
                    @php
                        $productMessage = $listingUrl ? "Hi, you can pay for {$product->name} here: {$listingUrl}" : "Hi, you can pay for {$product->name} on our store page.";
                    @endphp
                    <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-900">{{ $product->name }}</p><p class="mt-1 text-xs text-slate-400">{{ \App\Support\Money::format($product->price, $currency) }}</p></div>
                        <a href="{{ route('products.edit', $product) }}" class="text-sm font-semibold text-slate-600">Edit</a>
                        <button type="button" data-copy="{{ $productMessage }}" class="lp-button-secondary min-h-9 px-3 py-1.5 text-xs">Copy link</button>
                    </div>
                @empty
                    <div class="py-7 text-center"><p class="text-sm text-slate-500">No products yet.</p><a href="{{ route('products.create') }}" class="mt-2 inline-flex text-sm font-semibold text-emerald-700">Add your first product</a></div>
                @endforelse
            </div>
        </article>
    </section>

    <script>
        document.querySelectorAll('[data-copy]').forEach((button) => {
            button.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(button.dataset.copy);
                    const original = button.textContent;
                    button.textContent = 'Copied';
                    setTimeout(() => { button.textContent = original; }, 1200);
                } catch (e) {
                    alert('Copy failed. Please copy manually.');
                }
            });
        });
    </script>
@endsection
