<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="8Kommerce helps small businesses manage products, stock, orders, payments, and customer fulfilment from one practical workspace.">
    <title>8Kommerce — Run your business from one place</title>
    @php
        $landingTitle = '8Kommerce — Sell beautifully. Run everything behind it.';
        $landingDescription = 'Create a trusted storefront and manage products, stock, orders, payments, delivery, and customers from one practical workspace.';
        $landingUrl = url()->current();
        $landingImage = url('/images/8kommerce-social-preview-v1.jpg');
    @endphp
    <link rel="canonical" href="{{ $landingUrl }}">
    <meta property="og:title" content="{{ $landingTitle }}">
    <meta property="og:description" content="{{ $landingDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $landingUrl }}">
    <meta property="og:site_name" content="8Kommerce">
    <meta property="og:image" content="{{ $landingImage }}">
    <meta property="og:image:secure_url" content="{{ $landingImage }}">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="8Kommerce storefront and business management platform">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $landingTitle }}">
    <meta name="twitter:description" content="{{ $landingDescription }}">
    <meta name="twitter:image" content="{{ $landingImage }}">
    <meta name="twitter:image:alt" content="8Kommerce storefront and business management platform">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <meta name="theme-color" content="#073B4C">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">
    <link href="https://fonts.bunny.net/css?family=manrope:600,700,800&family=public-sans:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="lp-application bg-[#f4f7f7] font-sans text-slate-950 antialiased">
    @php
        $starter = config('plans.starter', []);
        $growth = config('plans.growth', []);
        $enterprise = config('plans.enterprise', []);
        $liveStoreUrl = $featuredSellers->isNotEmpty()
            ? route('public.listing', $featuredSellers->first()['slug'])
            : route('marketplace.sellers');
    @endphp

    <header class="sticky top-0 z-40 border-b border-slate-200/90 bg-white/95 backdrop-blur-lg">
        <div class="mx-auto flex h-16 max-w-[1380px] items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="8Kommerce home">
                <x-brand-mark class="h-9 w-auto" />
                <span><span class="font-display block text-base font-semibold leading-none text-[#073B4C]">8Kommerce</span><span class="mt-1 block text-[11px] leading-none text-slate-500">Business, under control</span></span>
            </a>
            <nav class="hidden items-center gap-7 text-sm font-semibold text-slate-600 md:flex" aria-label="Main navigation">
                <a href="#platform" class="hover:text-emerald-700">Platform</a>
                <a href="#how-it-works" class="hover:text-emerald-700">How it works</a>
                <a href="#marketplace" class="hover:text-emerald-700">Marketplace</a>
                <a href="{{ route('discover.index') }}" class="hover:text-emerald-700">Discover</a>
                <a href="{{ route('pricing') }}" class="hover:text-emerald-700">Pricing</a>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="lp-button-secondary hidden sm:inline-flex">Sign in</a>
                <a href="{{ route('register') }}" class="lp-button-brand">Start free</a>
            </div>
        </div>
    </header>

    <main>
        <section class="landing-hero border-b border-white/10">
            <div class="mx-auto grid max-w-[1380px] gap-10 px-4 py-14 sm:px-6 md:py-20 lg:grid-cols-[0.82fr_1.18fr] lg:items-center lg:gap-12 lg:px-8 lg:py-24">
                <div>
                    <p class="landing-hero__eyebrow">Commerce built for real daily work</p>
                    <h1 class="landing-hero__title mt-4 max-w-xl text-4xl font-semibold leading-[1.02] tracking-[-0.045em] sm:text-5xl lg:text-[4rem]">Sell beautifully. Run everything behind it.</h1>
                    <p class="landing-hero__copy mt-6 max-w-xl text-lg leading-8">Open a storefront customers trust, take secure payments, and keep products, orders, delivery, and customer updates moving from one workspace.</p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row"><a href="{{ route('register') }}" class="landing-hero__primary">Start your 9-day free trial</a><a href="{{ $liveStoreUrl }}" class="landing-hero__secondary">See a live storefront</a></div>
                    <div class="landing-hero__facts mt-9 grid max-w-lg grid-cols-3 py-4 text-sm">
                        <div><strong class="block">9 days</strong><span>free access</span></div>
                        <div><strong class="block">One link</strong><span>to start selling</span></div>
                        <div><strong class="block">Built mobile-first</strong><span>for sellers and buyers</span></div>
                    </div>
                </div>

                <x-landing-phone-showcase :products="$topProducts" :currency="$currency" />
            </div>
        </section>

        <section class="border-b border-slate-200 bg-white"><div class="mx-auto grid max-w-[1380px] px-4 sm:grid-cols-3 sm:px-6 lg:px-8"><div class="border-b border-slate-200 py-5 sm:border-b-0 sm:border-r sm:pr-8"><p class="text-sm font-semibold">Sell from one shareable link</p><p class="mt-1 text-sm text-slate-500">No website setup required.</p></div><div class="border-b border-slate-200 py-5 sm:border-b-0 sm:border-r sm:px-8"><p class="text-sm font-semibold">Know what needs attention</p><p class="mt-1 text-sm text-slate-500">Stock and orders stay visible.</p></div><div class="py-5 sm:pl-8"><p class="text-sm font-semibold">Keep customers informed</p><p class="mt-1 text-sm text-slate-500">Track fulfilment from payment to delivery.</p></div></div></section>

        <section id="store-designs" class="landing-template-section border-b border-slate-200 py-16 sm:py-20">
            <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-[0.72fr_1.28fr] lg:items-end">
                    <div><p class="lp-eyebrow text-[#7a1f3d]">A storefront that feels like yours</p><h2 class="mt-3 max-w-lg text-3xl font-semibold tracking-[-0.035em] sm:text-4xl">Start with a strong design. Make it unmistakably your brand.</h2></div>
                    <div class="lg:justify-self-end"><p class="max-w-xl text-base leading-7 text-slate-600">Choose a purpose-built layout, then add your logo, colours, cover image, products, delivery choices, and pickup points. Your catalogue stays intact when the design changes.</p><a href="{{ route('register') }}" class="mt-5 inline-flex text-sm font-semibold text-[#075643]">Design your storefront <span class="ml-2" aria-hidden="true">→</span></a></div>
                </div>

                <div class="landing-template-grid mt-10">
                    <article class="landing-template-card landing-template-card--fashion is-active" data-template-card tabindex="0" role="button" aria-pressed="true" aria-label="Preview Fashion storefront style">
                        <div class="landing-template-preview"><div class="landing-template-bar"><strong>FORME</strong><span>⌕　♡　▢</span></div><div class="landing-template-cover"><span>THE NEW EDIT</span><strong>Wear often.<br>Keep longer.</strong></div><div class="landing-template-products"><span></span><span></span></div></div>
                        <div class="landing-template-meta"><span>01</span><div><h3>Fashion</h3><p>Editorial imagery and a product-led grid for clothing, accessories, and lifestyle brands.</p></div></div>
                    </article>
                    <article class="landing-template-card landing-template-card--market" data-template-card tabindex="0" role="button" aria-pressed="false" aria-label="Preview Marketplace storefront style">
                        <div class="landing-template-preview"><div class="landing-template-bar"><strong>daywell.</strong><span>⌕　▢</span></div><div class="landing-template-search">⌕ &nbsp; Search all products</div><div class="landing-template-categories"><span>Electronics</span><span>Home</span><span>Kitchen</span></div><div class="landing-template-products"><span></span><span></span></div></div>
                        <div class="landing-template-meta"><span>02</span><div><h3>Marketplace</h3><p>Search, categories, comparison-friendly cards, and clear stock information for broad catalogues.</p></div></div>
                    </article>
                    <article class="landing-template-card landing-template-card--perfume" data-template-card tabindex="0" role="button" aria-pressed="false" aria-label="Preview Perfume storefront style">
                        <div class="landing-template-preview"><div class="landing-template-bar"><strong>MAISON AURA</strong><span>⌕　▢</span></div><div class="landing-template-cover"><span>THE ART OF FEELING</span><strong>A scent that<br>feels like you.</strong></div><div class="landing-template-products"><span></span><span></span></div></div>
                        <div class="landing-template-meta"><span>03</span><div><h3>Perfume</h3><p>A refined, sensory storefront for fragrance, beauty, gifts, and carefully curated products.</p></div></div>
                    </article>
                </div>
            </div>
        </section>

        <section id="platform" class="landing-story bg-white py-16 sm:py-24" data-landing-story>
            <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl" data-reveal><p class="lp-eyebrow text-emerald-700">One practical operating system</p><h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">The screen changes with the work. The workflow stays familiar.</h2><p class="mt-4 text-base leading-7 text-slate-600">Move from a product going live to a completed delivery without stitching together five different tools.</p></div>
                <div class="landing-story__layout mt-12">
                    <div class="landing-story__steps">
                        @foreach([['products', '01', 'Catalogue and stock', 'Publish products quickly, monitor quantities, and catch low-stock items before they become missed sales.'], ['orders', '02', 'Orders and fulfilment', 'Move paid orders through processing, ready, shipped, and delivered stages with a complete timeline.'], ['payments', '03', 'Payments and invoices', 'Accept checkout payments, issue invoice links, and understand what has been paid at a glance.'], ['customers', '04', 'Customers and follow-up', 'Keep the context needed to answer questions, update customers, and encourage repeat orders.']] as $index => $feature)
                            <article class="landing-story__step {{ $index === 0 ? 'is-active' : '' }}" data-story-step="{{ $feature[0] }}" tabindex="0"><span>{{ $feature[1] }}</span><div><h3>{{ $feature[2] }}</h3><p>{{ $feature[3] }}</p></div></article>
                        @endforeach
                    </div>
                    <div class="landing-story__stage" aria-live="polite">
                        <div class="landing-story__glow"></div>
                        <div class="landing-story__window">
                            <div class="landing-story__window-bar"><span><i></i><i></i><i></i></span><small>8Kommerce workspace</small><b>Live</b></div>
                            <div class="landing-story__window-body">
                                <aside><strong>8K</strong><span class="is-current"></span><span></span><span></span><span></span></aside>
                                <div class="landing-story__screen" data-story-screen>
                                    <div class="landing-story__screen-head"><div><small>CATALOGUE</small><h3>Products and stock</h3></div><button type="button" tabindex="-1">Add product</button></div>
                                    <div class="landing-story__metrics"><span><small>Products</small><strong>48</strong></span><span><small>Low stock</small><strong>4</strong></span><span><small>Store status</small><strong>Live</strong></span></div>
                                    <div class="landing-story__rows"><span><i></i><b>Essential cotton shirt</b><em>12 in stock</em></span><span><i></i><b>Everyday leather tote</b><em>8 in stock</em></span><span><i></i><b>Amber signature scent</b><em>3 left</em></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="landing-steps border-y border-slate-200 bg-slate-950 py-16 text-white sm:py-20" data-landing-steps>
            <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8"><div class="grid gap-10 lg:grid-cols-[0.7fr_1.3fr]"><div><p class="text-xs font-semibold uppercase tracking-wider text-emerald-300">Start simply</p><h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">From signup to first order in three clear steps.</h2></div><div class="grid gap-px overflow-hidden rounded-lg border border-white/15 bg-white/15 md:grid-cols-3">@foreach([['1', 'Set up your store', 'Add business details and your first products.'], ['2', 'Share your link', 'Send customers one storefront link anywhere you sell.'], ['3', 'Run the order', 'Confirm payment, update progress, and complete delivery.']] as $step)<div class="bg-slate-950 p-6"><span class="text-sm font-semibold text-emerald-300">{{ $step[0] }}</span><h3 class="mt-8 text-lg font-semibold">{{ $step[1] }}</h3><p class="mt-2 text-sm leading-6 text-slate-400">{{ $step[2] }}</p></div>@endforeach</div></div></div>
        </section>

        <section id="marketplace" class="landing-marketplace py-16 sm:py-24">
            <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8">
                <div class="landing-section-heading" data-reveal><div><p class="lp-eyebrow text-[#8fd7c0]"><span class="landing-live-dot"></span> Live on 8Kommerce</p><h2 class="mt-3 max-w-2xl text-3xl font-semibold tracking-[-.035em] text-white sm:text-4xl">Independent businesses. Real products. Ready to discover.</h2></div><a href="{{ route('marketplace.sellers') }}" class="landing-marketplace__browse">Browse every store <span aria-hidden="true">→</span></a></div>

                <div class="landing-seller-grid mt-10">
                    @forelse($featuredSellers as $index => $seller)
                        <a href="{{ route('public.listing', $seller['slug']) }}" class="landing-seller-card" data-reveal style="--seller-index: {{ $index }}">
                            <div class="landing-seller-card__art"><span>{{ strtoupper(mb_substr($seller['business_name'], 0, 2)) }}</span><i></i><i></i></div>
                            <div class="landing-seller-card__body"><div class="flex items-center justify-between gap-3"><small>{{ $seller['category'] }}</small><em>Store live</em></div><h3>{{ $seller['business_name'] }}</h3><p>{{ $seller['product_count'] }} {{ \Illuminate\Support\Str::plural('product', $seller['product_count']) }} <span>·</span> @if($seller['rating']['public']) {{ number_format($seller['rating']['average'], 1) }} ★ @else New seller @endif</p><span class="landing-seller-card__visit">Visit storefront <b aria-hidden="true">↗</b></span></div>
                        </a>
                    @empty
                        <div class="lp-empty-state col-span-full">Marketplace sellers will appear here.</div>
                    @endforelse
                </div>

                @if($topProducts->isNotEmpty())
                    <div class="landing-market-picks mt-16"><div class="landing-market-picks__intro" data-reveal><p class="lp-eyebrow text-[#8fd7c0]">Marketplace picks</p><h3>From their shelves to your screen.</h3><p>Browse real products available from sellers building their businesses on 8Kommerce.</p><a href="{{ route('discover.index') }}">Explore products <span aria-hidden="true">→</span></a></div><div class="landing-market-products">@foreach($topProducts->take(3) as $product)<a href="{{ route('public.product', ['product_slug' => $product['slug']]) }}" class="landing-market-product" data-reveal><div class="landing-market-product__image">@if($product['image_path'])<img src="{{ asset('storage/'.$product['image_path']) }}" alt="{{ $product['name'] }}">@else<div><span>{{ strtoupper(mb_substr($product['name'], 0, 1)) }}</span></div>@endif<span class="landing-market-product__arrow">↗</span></div><div><small>{{ $product['seller_name'] }}</small><h4>{{ $product['name'] }}</h4><strong>{{ \App\Support\Money::format((string) $product['price'], $currency) }}</strong></div></a>@endforeach</div></div>
                @endif
            </div>
        </section>

        <section id="plans" class="landing-pricing border-y border-slate-200 py-16 sm:py-24">
            <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center" data-reveal><p class="lp-eyebrow text-[#7a1f3d]">Simple plans, room to grow</p><h2 class="mt-3 text-3xl font-semibold tracking-[-.035em] sm:text-4xl">Start where the business is today.</h2><p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-600">Every plan includes your storefront, orders, secure checkout, customer tools, and unlimited orders. Move up when you need more products, promotion, or support.</p></div>
                <div class="landing-plan-grid mt-10">@foreach([['Starter', $starter, false], ['Growth', $growth, true], ['Enterprise', $enterprise, false]] as $plan)<article class="landing-plan {{ $plan[2] ? 'is-featured' : '' }}" data-reveal>@if($plan[2])<span class="landing-plan__badge">Best place to start growing</span>@endif<div class="landing-plan__head"><div><p>{{ data_get($plan[1], 'best_for', '') }}</p><h3>{{ $plan[0] }}</h3></div><span>{{ data_get($plan[1], 'transaction_fee') }}<small>transaction fee</small></span></div><p class="landing-plan__price"><span>GHS</span>{{ data_get($plan[1], 'price_monthly', '—') }}<small>/ month</small></p><a href="{{ route('pricing') }}" class="landing-plan__action">Choose {{ $plan[0] }} <span aria-hidden="true">→</span></a><ul>@foreach(array_slice(data_get($plan[1], 'features', []), 0, 4) as $feature)<li><span>✓</span>{{ $feature }}</li>@endforeach</ul></article>@endforeach</div>
                <div class="landing-pricing__footer"><span>9-day free trial</span><i></i><span>Change plans as you grow</span><i></i><span>Secure Paystack checkout</span><a href="{{ route('pricing') }}">Compare every feature →</a></div>
            </div>
        </section>

        <section id="questions" class="landing-faq bg-white py-16 sm:py-24">
            <div class="mx-auto grid max-w-[1380px] gap-10 px-4 sm:px-6 lg:grid-cols-[.68fr_1.32fr] lg:px-8">
                <div data-reveal><p class="lp-eyebrow text-emerald-700">Questions, answered clearly</p><h2 class="mt-3 max-w-md text-3xl font-semibold tracking-[-.035em] sm:text-4xl">Know what happens before you start.</h2><p class="mt-4 max-w-md text-sm leading-6 text-slate-600">No website team is required. Start with your phone, your products, and the customers you already reach.</p><div class="landing-faq__prompt"><span>Still deciding?</span><strong>See the complete plan comparison.</strong><a href="{{ route('pricing') }}">View pricing <span aria-hidden="true">→</span></a></div></div>
                <div class="landing-faq__list">@foreach([['Do I need a website?', 'No. 8Kommerce gives you a public store link you can share on WhatsApp, Instagram, or anywhere customers find you.'], ['Can customers track their orders?', 'Yes. Customers securely access their order hub with their phone number and a one-time verification code.'], ['Can I manage stock on my phone?', 'Yes. The seller workspace is responsive and designed for everyday mobile use.'], ['How do I start?', 'Create an account, verify your phone, set your PIN, add products, choose a storefront design, and share your store link.']] as $index => $faq)<details {{ $index === 0 ? 'open' : '' }}><summary><span><small>0{{ $index + 1 }}</small>{{ $faq[0] }}</span><i aria-hidden="true">+</i></summary><div><p>{{ $faq[1] }}</p></div></details>@endforeach</div>
            </div>
        </section>

        <section class="landing-final-cta text-white" data-reveal><div class="landing-final-cta__orb"></div><div class="mx-auto grid max-w-[1380px] gap-8 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1fr_auto] lg:items-end lg:px-8"><div><p class="text-xs font-semibold uppercase tracking-[.16em] text-[#A9D4DE]">Your business is ready for its next chapter</p><h2 class="font-display mt-4 max-w-3xl text-4xl font-semibold tracking-[-.04em] sm:text-5xl">Your next order could start here.</h2><p class="mt-4 max-w-xl text-base leading-7 text-white/65">Build the storefront, share one link, and manage everything that happens after a customer taps buy.</p></div><div class="flex flex-col gap-3 sm:flex-row lg:flex-col"><a href="{{ route('register') }}" class="landing-final-cta__primary">Start your 9-day free trial <span aria-hidden="true">→</span></a><a href="{{ route('pricing') }}" class="landing-final-cta__secondary">Explore pricing</a></div></div></section>
    </main>

    <footer class="border-t border-white/10 bg-[#032B3A] text-slate-300"><div class="mx-auto grid max-w-[1380px] gap-10 px-4 py-10 sm:px-6 md:grid-cols-[1fr_auto_auto] lg:px-8"><div><x-brand-logo :reversed="true" class="h-8 text-base" /><p class="mt-3 max-w-sm text-sm leading-6 text-slate-400">Practical commerce software for independent businesses.</p></div><div><p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Product</p><div class="mt-3 space-y-2 text-sm"><a href="#platform" class="block hover:text-white">Platform</a><a href="{{ route('pricing') }}" class="block hover:text-white">Pricing</a><a href="{{ route('marketplace.sellers') }}" class="block hover:text-white">Marketplace</a><a href="{{ route('discover.index') }}" class="block hover:text-white">Discover</a></div></div><div><p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Company</p><div class="mt-3 space-y-2 text-sm"><a href="{{ route('legal.privacy') }}" class="block hover:text-white">Privacy</a><a href="{{ route('legal.terms') }}" class="block hover:text-white">Terms</a><a href="{{ route('login') }}" class="block hover:text-white">Sign in</a></div></div></div><div class="mx-auto max-w-[1380px] border-t border-white/10 px-4 py-5 text-xs text-slate-500 sm:px-6 lg:px-8">© {{ now()->year }} 8Kommerce. All rights reserved.</div></footer>
    @include('components.page-loader')
</body>
</html>
