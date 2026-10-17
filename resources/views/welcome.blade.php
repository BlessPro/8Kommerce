<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="8Kommerce helps small businesses manage products, stock, orders, payments, and customer fulfilment from one practical workspace.">
    <title>8Kommerce — Run your business from one place</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <meta name="theme-color" content="#073B4C">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link href="https://fonts.bunny.net/css?family=manrope:600,700,800&family=public-sans:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="lp-application bg-[#f4f7f7] font-sans text-slate-950 antialiased">
    @php
        $starter = config('plans.starter', []);
        $growth = config('plans.growth', []);
        $enterprise = config('plans.enterprise', []);
    @endphp

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95">
        <div class="mx-auto flex h-16 max-w-[1380px] items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="8Kommerce home">
                <x-brand-mark class="h-9 w-auto" />
                <span><span class="font-display block text-base font-semibold leading-none text-[#073B4C]">8Kommerce</span><span class="mt-1 block text-[11px] leading-none text-slate-500">Business, under control</span></span>
            </a>
            <nav class="hidden items-center gap-7 text-sm font-semibold text-slate-600 md:flex" aria-label="Main navigation">
                <a href="#platform" class="hover:text-emerald-700">Platform</a>
                <a href="#how-it-works" class="hover:text-emerald-700">How it works</a>
                <a href="#marketplace" class="hover:text-emerald-700">Marketplace</a>
                <a href="{{ route('pricing') }}" class="hover:text-emerald-700">Pricing</a>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="lp-button-secondary hidden sm:inline-flex">Sign in</a>
                <a href="{{ route('register') }}" class="lp-button-brand">Start free</a>
            </div>
        </div>
    </header>

    <main>
        <section class="border-b border-slate-200 bg-[#edf3ee]">
            <div class="mx-auto grid max-w-[1380px] gap-10 px-4 py-14 sm:px-6 md:py-20 lg:grid-cols-[0.82fr_1.18fr] lg:items-center lg:gap-16 lg:px-8 lg:py-24">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-800">Built for independent businesses</p>
                    <h1 class="mt-4 max-w-xl text-4xl font-semibold leading-[1.04] tracking-[-0.04em] sm:text-5xl lg:text-[3.75rem]">Every product, payment, and order in its place.</h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-slate-600">8Kommerce gives growing sellers one dependable workspace for stock, customers, checkout, and fulfilment—without enterprise complexity.</p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row"><a href="{{ route('register') }}" class="lp-button-brand px-6 py-3">Create your store</a><a href="#platform" class="lp-button-secondary px-6 py-3">See how it works</a></div>
                    <div class="mt-8 grid max-w-lg grid-cols-3 border-y border-emerald-900/15 py-4 text-sm">
                        <div><strong class="block">9 days</strong><span class="text-slate-500">free access</span></div>
                        <div class="border-l border-emerald-900/15 pl-4"><strong class="block">One link</strong><span class="text-slate-500">to start selling</span></div>
                        <div class="border-l border-emerald-900/15 pl-4"><strong class="block">Mobile first</strong><span class="text-slate-500">for daily work</span></div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-slate-300 bg-white shadow-[0_24px_60px_rgba(20,45,35,0.12)]" aria-label="8Kommerce dashboard preview">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3"><div class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span><span class="text-xs font-semibold text-slate-700">Store overview</span></div><span class="text-[11px] text-slate-400">Today</span></div>
                    <div class="grid md:grid-cols-[0.34fr_0.66fr]">
                        <div class="hidden border-r border-slate-200 bg-slate-50 p-4 md:block"><p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Workspace</p><div class="mt-4 space-y-1 text-xs font-semibold"><div class="border-l-2 border-emerald-700 bg-emerald-50 px-3 py-2 text-emerald-800">Overview</div><div class="px-3 py-2 text-slate-500">Products</div><div class="px-3 py-2 text-slate-500">Orders</div><div class="px-3 py-2 text-slate-500">Customers</div><div class="px-3 py-2 text-slate-500">Payments</div></div></div>
                        <div class="p-4 sm:p-6">
                            <div class="flex items-end justify-between gap-4"><div><p class="text-xs text-slate-500">Good morning</p><p class="text-lg font-semibold">Business overview</p></div><span class="rounded bg-emerald-700 px-3 py-2 text-xs font-semibold text-white">Add product</span></div>
                            <div class="mt-5 grid grid-cols-3 divide-x divide-slate-200 rounded-md border border-slate-200"><div class="p-3 sm:p-4"><p class="text-[10px] uppercase tracking-wider text-slate-400">Revenue</p><p class="mt-1 text-base font-semibold sm:text-xl">GHS 4,820</p></div><div class="p-3 sm:p-4"><p class="text-[10px] uppercase tracking-wider text-slate-400">Orders</p><p class="mt-1 text-base font-semibold sm:text-xl">38</p></div><div class="p-3 sm:p-4"><p class="text-[10px] uppercase tracking-wider text-slate-400">Low stock</p><p class="mt-1 text-base font-semibold text-amber-700 sm:text-xl">4</p></div></div>
                            <div class="mt-5 overflow-hidden rounded-md border border-slate-200"><div class="grid grid-cols-[1fr_auto_auto] gap-3 bg-slate-50 px-3 py-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400"><span>Recent order</span><span>Status</span><span>Total</span></div>@foreach([['Ama K.', 'Ready', 'GHS 260'], ['Kojo D.', 'Processing', 'GHS 145'], ['Esi M.', 'Paid', 'GHS 390']] as $row)<div class="grid grid-cols-[1fr_auto_auto] items-center gap-3 border-t border-slate-100 px-3 py-3 text-xs"><span class="font-semibold">{{ $row[0] }}</span><span class="rounded bg-emerald-50 px-2 py-1 font-semibold text-emerald-800">{{ $row[1] }}</span><span class="font-semibold">{{ $row[2] }}</span></div>@endforeach</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-b border-slate-200 bg-white"><div class="mx-auto grid max-w-[1380px] px-4 sm:grid-cols-3 sm:px-6 lg:px-8"><div class="border-b border-slate-200 py-5 sm:border-b-0 sm:border-r sm:pr-8"><p class="text-sm font-semibold">Sell from one shareable link</p><p class="mt-1 text-sm text-slate-500">No website setup required.</p></div><div class="border-b border-slate-200 py-5 sm:border-b-0 sm:border-r sm:px-8"><p class="text-sm font-semibold">Know what needs attention</p><p class="mt-1 text-sm text-slate-500">Stock and orders stay visible.</p></div><div class="py-5 sm:pl-8"><p class="text-sm font-semibold">Keep customers informed</p><p class="mt-1 text-sm text-slate-500">Track fulfilment from payment to delivery.</p></div></div></section>

        <section id="platform" class="bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl"><p class="lp-eyebrow text-emerald-700">One practical operating system</p><h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Built around the work you do every day.</h2><p class="mt-4 text-base leading-7 text-slate-600">The important information stays visible, the next action stays obvious, and every tool follows the same workflow.</p></div>
                <div class="mt-10 grid border-y border-slate-200 md:grid-cols-2">
                    @foreach([['01', 'Catalogue and stock', 'Publish products quickly, monitor quantities, and spot low-stock items before they become missed sales.'], ['02', 'Orders and fulfilment', 'Move paid orders through processing, ready, shipped, and delivered stages with a complete timeline.'], ['03', 'Payments and invoices', 'Accept checkout payments, issue invoice links, and review transaction activity in one place.'], ['04', 'Customers and follow-up', 'See who buys, what they ordered, and the information needed to serve them well.']] as $index => $feature)
                        <article class="grid grid-cols-[auto_1fr] gap-5 border-b border-slate-200 py-7 md:px-7 {{ $index % 2 === 0 ? 'md:border-r md:pl-0' : 'md:pr-0' }} {{ $index > 1 ? 'md:border-b-0' : '' }}"><span class="text-xs font-semibold text-emerald-700">{{ $feature[0] }}</span><div><h3 class="text-lg font-semibold">{{ $feature[1] }}</h3><p class="mt-2 max-w-md text-sm leading-6 text-slate-600">{{ $feature[2] }}</p></div></article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="how-it-works" class="border-y border-slate-200 bg-slate-950 py-16 text-white sm:py-20">
            <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8"><div class="grid gap-10 lg:grid-cols-[0.7fr_1.3fr]"><div><p class="text-xs font-semibold uppercase tracking-wider text-emerald-300">Start simply</p><h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">From signup to first order in three clear steps.</h2></div><div class="grid gap-px overflow-hidden rounded-lg border border-white/15 bg-white/15 md:grid-cols-3">@foreach([['1', 'Set up your store', 'Add business details and your first products.'], ['2', 'Share your link', 'Send customers one storefront link anywhere you sell.'], ['3', 'Run the order', 'Confirm payment, update progress, and complete delivery.']] as $step)<div class="bg-slate-950 p-6"><span class="text-sm font-semibold text-emerald-300">{{ $step[0] }}</span><h3 class="mt-8 text-lg font-semibold">{{ $step[1] }}</h3><p class="mt-2 text-sm leading-6 text-slate-400">{{ $step[2] }}</p></div>@endforeach</div></div></div>
        </section>

        <section id="marketplace" class="bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="lp-eyebrow text-emerald-700">Live on 8Kommerce</p><h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Meet businesses already selling.</h2></div><a href="{{ route('marketplace.sellers') }}" class="lp-button-secondary self-start">Browse marketplace</a></div>
                <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @forelse($featuredSellers as $seller)
                        <article class="rounded-lg border border-slate-200 bg-white p-5 transition-colors hover:border-slate-400"><div class="flex items-start justify-between gap-4"><span class="flex h-10 w-10 items-center justify-center rounded-md bg-slate-900 text-sm font-bold text-white">{{ strtoupper(mb_substr($seller['business_name'], 0, 2)) }}</span><span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">{{ $seller['category'] }}</span></div><h3 class="mt-6 text-lg font-semibold">{{ $seller['business_name'] }}</h3><p class="mt-1 text-sm text-slate-500">{{ $seller['product_count'] }} products · {{ number_format($seller['rating'], 1) }} rating</p><a href="{{ route('public.listing', $seller['slug']) }}" class="mt-5 inline-flex text-sm font-semibold text-emerald-700 hover:text-emerald-900">Visit store <span aria-hidden="true" class="ml-1">→</span></a></article>
                    @empty
                        <div class="lp-empty-state col-span-full">Marketplace sellers will appear here.</div>
                    @endforelse
                </div>
                @if($topProducts->isNotEmpty())
                    <div class="mt-12 grid gap-6 border-t border-slate-200 pt-10 lg:grid-cols-[0.65fr_1.35fr] lg:items-start"><div><p class="lp-eyebrow text-emerald-700">Marketplace picks</p><h3 class="mt-3 text-2xl font-semibold">Products customers can buy right now.</h3><p class="mt-3 text-sm leading-6 text-slate-600">Explore real catalogue listings from active sellers.</p></div><div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@foreach($topProducts->take(3) as $product)<a href="{{ route('public.product', ['product_slug' => $product['slug']]) }}" class="group overflow-hidden rounded-lg border border-slate-200 bg-white hover:border-slate-400">@if($product['image_path'])<img src="{{ asset('storage/'.$product['image_path']) }}" alt="{{ $product['name'] }}" class="aspect-[4/3] w-full object-cover">@else<div class="flex aspect-[4/3] items-center justify-center bg-slate-100 text-xs text-slate-400">No image</div>@endif<div class="p-4"><p class="font-semibold group-hover:text-emerald-700">{{ $product['name'] }}</p><div class="mt-2 flex justify-between gap-3 text-sm"><span class="text-slate-500">{{ $product['seller_name'] }}</span><span class="font-semibold">{{ \App\Support\Money::format((string) $product['price'], $currency) }}</span></div></div></a>@endforeach</div></div>
                @endif
            </div>
        </section>

        <section class="border-y border-slate-200 bg-[#f4f7f7] py-16 sm:py-20">
            <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8"><div class="grid gap-8 lg:grid-cols-[0.65fr_1.35fr]"><div><p class="lp-eyebrow text-emerald-700">Clear monthly plans</p><h2 class="mt-3 text-3xl font-semibold tracking-tight">Start small. Move up when the business does.</h2><p class="mt-4 text-sm leading-6 text-slate-600">Every plan includes your storefront, orders, checkout, and customer tools.</p><a href="{{ route('pricing') }}" class="lp-button-secondary mt-6">Compare all features</a></div><div class="grid gap-4 sm:grid-cols-3">@foreach([['Starter', $starter, false], ['Growth', $growth, true], ['Enterprise', $enterprise, false]] as $plan)<article class="rounded-lg border p-5 {{ $plan[2] ? 'border-emerald-700 bg-white' : 'border-slate-200 bg-white' }}"><div class="flex items-center justify-between gap-2"><h3 class="font-semibold">{{ $plan[0] }}</h3>@if($plan[2])<span class="rounded bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-800">Popular</span>@endif</div><p class="mt-5 text-2xl font-semibold">GHS {{ data_get($plan[1], 'price_monthly', '—') }}<span class="text-sm font-normal text-slate-500"> / month</span></p><p class="mt-3 text-sm text-slate-500">{{ data_get($plan[1], 'best_for', '') }}</p></article>@endforeach</div></div></div>
        </section>

        <section class="bg-white py-16 sm:py-20">
            <div class="mx-auto grid max-w-[1380px] gap-8 px-4 sm:px-6 lg:grid-cols-[0.65fr_1.35fr] lg:px-8"><div><p class="lp-eyebrow text-emerald-700">Common questions</p><h2 class="mt-3 text-3xl font-semibold tracking-tight">Before you get started.</h2></div><div class="divide-y divide-slate-200 border-y border-slate-200">@foreach([['Do I need a website?', 'No. 8Kommerce gives you a public store link you can share on WhatsApp, Instagram, or anywhere customers find you.'], ['Can customers track their orders?', 'Yes. Customers securely access their order hub with their phone number and a one-time verification code.'], ['Can I manage stock on my phone?', 'Yes. The seller workspace is responsive and designed for everyday mobile use.'], ['How do I start?', 'Create an account, verify your phone, set your PIN, add products, and share your store link.']] as $faq)<details class="group py-5"><summary class="flex cursor-pointer list-none items-center justify-between gap-5 font-semibold"><span>{{ $faq[0] }}</span><span class="text-emerald-700 group-open:rotate-45">+</span></summary><p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">{{ $faq[1] }}</p></details>@endforeach</div></div>
        </section>

        <section class="bg-[#073B4C] py-14 text-white"><div class="mx-auto flex max-w-[1380px] flex-col gap-7 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8"><div><p class="text-sm font-semibold text-[#A9D4DE]">Ready to bring the business together?</p><h2 class="font-display mt-2 text-3xl font-semibold tracking-tight">Open your 8Kommerce workspace.</h2></div><div class="flex flex-col gap-3 sm:flex-row"><a href="{{ route('register') }}" class="inline-flex min-h-10 items-center justify-center rounded-md bg-white px-5 py-3 text-sm font-semibold text-[#073B4C] hover:bg-[#FFF8F2]">Start free</a><a href="{{ route('login') }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-white/30 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10">Seller sign in</a></div></div></section>
    </main>

    <footer class="border-t border-white/10 bg-[#032B3A] text-slate-300"><div class="mx-auto grid max-w-[1380px] gap-10 px-4 py-10 sm:px-6 md:grid-cols-[1fr_auto_auto] lg:px-8"><div><x-brand-logo :reversed="true" class="h-8 text-base" /><p class="mt-3 max-w-sm text-sm leading-6 text-slate-400">Practical commerce software for independent businesses.</p></div><div><p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Product</p><div class="mt-3 space-y-2 text-sm"><a href="#platform" class="block hover:text-white">Platform</a><a href="{{ route('pricing') }}" class="block hover:text-white">Pricing</a><a href="{{ route('marketplace.sellers') }}" class="block hover:text-white">Marketplace</a></div></div><div><p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Company</p><div class="mt-3 space-y-2 text-sm"><a href="{{ route('legal.privacy') }}" class="block hover:text-white">Privacy</a><a href="{{ route('legal.terms') }}" class="block hover:text-white">Terms</a><a href="{{ route('login') }}" class="block hover:text-white">Sign in</a></div></div></div><div class="mx-auto max-w-[1380px] border-t border-white/10 px-4 py-5 text-xs text-slate-500 sm:px-6 lg:px-8">© {{ now()->year }} 8Kommerce. All rights reserved.</div></footer>
</body>
</html>
