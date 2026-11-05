@php
    $title = 'Home';
    $listingUrl = $profile ? route('public.listing', $profile->public_slug) : null;
    $greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 17 ? 'Good afternoon' : 'Good evening');
    $attentionCount = $ordersAwaitingDecision->count() + $inventoryAlerts->count() + ($pendingPaymentCount > 0 ? 1 : 0) + ($profile?->paystack_subaccount_code ? 0 : 1);
@endphp

@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header
        :eyebrow="$greeting.', '.($profile?->business_name ?? 'Seller')"
        title="What needs your attention today"
        description="Handle urgent work first, then review how the business is performing."
    >
        <x-slot:actions>
            @if($listingUrl)
                <x-ui.button variant="secondary" icon="external-link" :href="$listingUrl" target="_blank" rel="noreferrer noopener">View store</x-ui.button>
            @endif
            <x-ui.button variant="brand" icon="circle-plus" class="js-quick-actions-open">Create new</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="mt-6" aria-labelledby="attention-heading">
        <div class="flex items-center justify-between gap-4">
            <div><p class="lp-eyebrow">Priority queue</p><h2 id="attention-heading" class="lp-section-title mt-1">{{ $attentionCount ? $attentionCount.' item'.($attentionCount === 1 ? '' : 's').' to review' : 'You are caught up' }}</h2></div>
            <a href="{{ route('notifications.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-[#073B4C]">Open activity <x-ui.icon name="arrow-right" size="sm" /></a>
        </div>

        @if($attentionCount)
            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach($ordersAwaitingDecision as $order)
                    <a href="{{ route('notifications.index') }}#order-{{ $order->id }}" class="lp-card group flex items-start gap-3 p-4 hover:border-[#B7CBD0]">
                        <span class="lp-icon-box bg-[#EAF1F3] text-[#073B4C]"><x-ui.icon name="shopping-bag" /></span>
                        <span class="min-w-0 flex-1"><span class="flex items-start justify-between gap-2"><strong class="text-sm text-slate-900">Accept order {{ $order->reference }}</strong><x-ui.badge tone="warning">New</x-ui.badge></span><small class="mt-1 block truncate text-xs text-slate-500">{{ $order->customer_name ?: 'Customer' }} · {{ \App\Support\Money::format((string) $order->total, $currency) }}</small></span>
                    </a>
                @endforeach

                @if($inventoryAlerts->isNotEmpty())
                    <a href="{{ route('products.index', ['stock' => \App\Models\Product::STATUS_LOW_STOCK]) }}" class="lp-card flex items-start gap-3 border-amber-200 bg-amber-50/60 p-4 hover:bg-amber-50">
                        <span class="lp-icon-box bg-amber-100 text-amber-800"><x-ui.icon name="warning" /></span>
                        <span class="min-w-0 flex-1"><strong class="text-sm text-slate-900">{{ $inventoryAlerts->count() }} product{{ $inventoryAlerts->count() === 1 ? '' : 's' }} need stock attention</strong><small class="mt-1 block text-xs text-slate-600">Review low-stock and sold-out products.</small></span>
                    </a>
                @endif

                @if($pendingPaymentCount > 0)
                    <a href="{{ route('payments.index', ['filter' => 'pending']) }}" class="lp-card flex items-start gap-3 border-blue-200 bg-blue-50/60 p-4 hover:bg-blue-50">
                        <span class="lp-icon-box bg-blue-100 text-blue-700"><x-ui.icon name="credit-card" /></span>
                        <span class="min-w-0 flex-1"><strong class="text-sm text-slate-900">{{ $pendingPaymentCount }} payment{{ $pendingPaymentCount === 1 ? '' : 's' }} still pending</strong><small class="mt-1 block text-xs text-slate-600">Check whether confirmation is still in progress.</small></span>
                    </a>
                @endif

                @if(! $profile?->paystack_subaccount_code)
                    <a href="{{ route('profile.edit') }}" class="lp-card flex items-start gap-3 border-rose-200 bg-rose-50/60 p-4 hover:bg-rose-50">
                        <span class="lp-icon-box bg-rose-100 text-rose-700"><x-ui.icon name="alert-circle" /></span>
                        <span class="min-w-0 flex-1"><strong class="text-sm text-slate-900">Finish payout setup</strong><small class="mt-1 block text-xs text-slate-600">Connect a verified MoMo or bank account.</small></span>
                    </a>
                @endif
            </div>
        @else
            <x-ui.alert tone="success" title="Everything important is handled" class="mt-4">New orders, payment issues and stock warnings will appear here.</x-ui.alert>
        @endif
    </section>

    <section class="mt-7" aria-labelledby="performance-heading">
        <div class="flex items-end justify-between gap-4"><div><p class="lp-eyebrow">This month</p><h2 id="performance-heading" class="lp-section-title mt-1">Business snapshot</h2></div><a href="{{ route('goals.index') }}" class="text-sm font-semibold text-[#073B4C]">View goals</a></div>
        <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-ui.stat-card label="Amount received" :value="\App\Support\Money::format($amountReceived, $currency)" :meta="'Successful payments in '.now()->format('F')" icon="credit-card" />
            <x-ui.stat-card label="Orders" :value="number_format($ordersCount)" meta="Confirmed this month" icon="shopping-bag" />
            <x-ui.stat-card label="Store visits" :value="number_format($trafficCount)" meta="Store and product activity" icon="eye" />
            <x-ui.stat-card label="Conversion" :value="number_format($salesConversionRate, 1).'%'" :meta="number_format($newCustomersCount).' new customers'" icon="chart-bar" />
        </div>
    </section>

    <section class="mt-7 grid items-start gap-5 lg:grid-cols-[1.15fr_.85fr]">
        <article class="lp-card p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4"><div><p class="lp-eyebrow">Latest</p><h2 class="lp-section-title mt-1">Recent activity</h2></div><a href="{{ route('notifications.index') }}" class="text-sm font-semibold text-[#073B4C]">View all</a></div>
            <div class="mt-5 divide-y divide-[#E8EBE6]">
                @forelse($activity as $item)
                    <div class="flex items-start gap-3 py-4 first:pt-0 last:pb-0">
                        <span class="lp-icon-box mt-0.5"><x-ui.icon :name="$item['type'] === 'payment' ? 'credit-card' : 'bell'" size="sm" /></span>
                        <div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-4"><p class="text-sm font-semibold text-slate-900">{{ $item['title'] }}</p><span class="whitespace-nowrap text-xs text-slate-400">{{ $item['created_at']->diffForHumans() }}</span></div><p class="mt-1 truncate text-sm text-slate-500">{{ $item['subtitle'] }}</p></div>
                        @if($item['type'] === 'payment')<p class="text-sm font-semibold text-emerald-700">{{ \App\Support\Money::format($item['amount'], $currency) }}</p>@endif
                    </div>
                @empty
                    <x-ui.empty-state title="No activity yet" description="New orders and payments will appear here." icon="activity" class="border-0 px-3 py-8" />
                @endforelse
            </div>
        </article>

        <article class="lp-card p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4"><div><p class="lp-eyebrow">Customers</p><h2 class="lp-section-title mt-1">Strong relationships</h2></div><a href="{{ route('customers.index') }}" class="text-sm font-semibold text-[#073B4C]">View all</a></div>
            <div class="mt-5 border-y border-[#D5E1E4] bg-[#F4F7F7] px-3 py-4"><p class="text-sm text-slate-600">Average successful payment</p><p class="mt-1 text-2xl font-semibold tracking-tight text-slate-950">{{ \App\Support\Money::format($averagePayment, $currency) }}</p></div>
            <div class="mt-3 divide-y divide-[#E8EBE6]">
                @forelse($topCustomers as $customer)
                    <div class="flex items-center justify-between gap-4 py-3"><div class="min-w-0"><p class="truncate text-sm font-medium text-slate-800">{{ $customer['email'] }}</p><p class="text-xs text-slate-400">{{ $customer['count'] }} payment{{ $customer['count'] === 1 ? '' : 's' }}</p></div><span class="text-sm font-semibold text-slate-700">{{ \App\Support\Money::format($customer['total'], $currency) }}</span></div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">Customer insights appear after your first sale.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-5 grid gap-5 lg:grid-cols-2">
        <article class="lp-card p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4"><div><p class="lp-eyebrow">Get paid</p><h2 class="lp-section-title mt-1">Invoices ready to share</h2></div><a href="{{ route('invoices.index') }}" class="text-sm font-semibold text-[#073B4C]">View all</a></div>
            <div class="mt-5 divide-y divide-[#E8EBE6]">
                @forelse($recentInvoices as $invoice)
                    @php($invoiceMessage = "Hi, here is your invoice for {$invoice->title}. Pay here: ".route('public.invoice', $invoice->token))
                    <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0"><span class="lp-icon-box h-8 w-8"><x-ui.icon name="file-invoice" size="sm" /></span><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-900">{{ $invoice->title }}</p><p class="mt-1 text-xs capitalize text-slate-400">{{ $invoice->status }}</p></div><a href="{{ route('invoices.show', $invoice) }}" class="text-sm font-semibold text-slate-600">Open</a><button type="button" data-copy="{{ $invoiceMessage }}" class="lp-button lp-button-secondary lp-button-sm">Copy link</button></div>
                @empty
                    <x-ui.empty-state title="No invoices yet" description="Create a payment link for a customer or service." icon="file-invoice" class="border-0 py-7"><x-slot:action><x-ui.button variant="secondary" size="sm" :href="route('invoices.create')">Create invoice</x-ui.button></x-slot:action></x-ui.empty-state>
                @endforelse
            </div>
        </article>

        <article class="lp-card p-5 sm:p-6">
            <div class="flex items-center justify-between gap-4"><div><p class="lp-eyebrow">Catalogue</p><h2 class="lp-section-title mt-1">Products ready to share</h2></div><a href="{{ route('products.index') }}" class="text-sm font-semibold text-[#073B4C]">View all</a></div>
            <div class="mt-5 divide-y divide-[#E8EBE6]">
                @forelse($recentProducts as $product)
                    @php($productMessage = $listingUrl ? "Hi, you can pay for {$product->name} here: {$listingUrl}" : "Hi, you can pay for {$product->name} on our store page.")
                    <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0"><span class="lp-icon-box h-8 w-8"><x-ui.icon name="package" size="sm" /></span><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-900">{{ $product->name }}</p><p class="mt-1 text-xs text-slate-400">{{ \App\Support\Money::format($product->price, $currency) }}</p></div><a href="{{ route('products.edit', $product) }}" class="text-sm font-semibold text-slate-600">Edit</a><button type="button" data-copy="{{ $productMessage }}" class="lp-button lp-button-secondary lp-button-sm">Copy link</button></div>
                @empty
                    <x-ui.empty-state title="No products yet" description="Add your first product and begin sharing your storefront." icon="package" class="border-0 py-7"><x-slot:action><x-ui.button variant="secondary" size="sm" :href="route('products.create')">Add product</x-ui.button></x-slot:action></x-ui.empty-state>
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
                } catch (_) {
                    button.textContent = 'Copy failed';
                }
            });
        });
    </script>
@endsection
