@php
    $activeStorefrontTheme = in_array(($storefrontTheme ?? 'classic'), \App\Models\SellerProfile::STOREFRONT_THEMES, true)
        ? ($storefrontTheme ?? 'classic')
        : 'classic';
    $storefrontCopy = match ($activeStorefrontTheme) {
        'perfume' => ['eyebrow' => 'The art of feeling', 'intro' => 'Intimate, considered fragrances chosen to become part of your story.'],
        'market' => ['eyebrow' => 'Shop the catalogue', 'intro' => 'Everyday products, clear availability and secure checkout in one place.'],
        'fashion' => ['eyebrow' => 'The new edit', 'intro' => 'Considered pieces, everyday expression and details designed to be worn often.'],
        'studio' => ['eyebrow' => 'The collection', 'intro' => 'Thoughtfully selected pieces from an independent business.'],
        default => ['eyebrow' => 'Independent storefront', 'intro' => 'Browse available products and order securely from this business.'],
    };
    $storefrontSettings = $profile->resolvedStorefrontSettings();
    $accentPalette = $profile->storefrontAccentPalette();
    $marketCategories = $products->pluck('category')->filter(fn ($category) => filled($category))->map(fn ($category) => trim((string) $category))->unique(fn ($category) => mb_strtolower($category))->values();
    $storefrontClasses = collect([
        'storefront',
        'storefront-theme-'.$activeStorefrontTheme,
        'storefront-layout-'.$template,
        'storefront-image-'.$storefrontSettings['image_ratio'],
        'storefront-density-'.$storefrontSettings['grid_density'],
        'storefront-hero-layout-'.$storefrontSettings['hero_layout'],
        'storefront-font-'.$storefrontSettings['font_preset'],
        'storefront-cards-'.$storefrontSettings['card_style'],
        'storefront-card-corners-'.$storefrontSettings['card_corner'],
        'storefront-header-'.$storefrontSettings['header_alignment'],
        'storefront-buttons-'.$storefrontSettings['button_style'],
        $storefrontSettings['show_description'] ? null : 'storefront-hide-description',
        $storefrontSettings['show_stock'] ? null : 'storefront-hide-stock',
        $storefrontSettings['show_quick_add'] ? null : 'storefront-hide-quick-add',
        $storefrontSettings['show_whatsapp'] ? null : 'storefront-hide-whatsapp',
    ])->filter()->implode(' ');
@endphp
<div id="storefront-top" class="{{ $storefrontClasses }}" style="--store-accent: {{ $accentPalette['accent'] }}; --store-accent-dark: {{ $accentPalette['dark'] }}; --store-tint: {{ $accentPalette['tint'] }}; --store-secondary: {{ $accentPalette['secondary'] }}; --store-surface: {{ $accentPalette['surface'] }}; --store-ink: {{ $accentPalette['ink'] }};">
@if($storefrontSettings['announcement_text'] || ($isPreview ?? false))
    <div class="storefront-announcement" @if(! $storefrontSettings['announcement_text']) hidden @endif>
        @if($storefrontSettings['announcement_url'])
            <a href="{{ $storefrontSettings['announcement_url'] }}" target="_blank" rel="noopener noreferrer" data-storefront-announcement-copy>{{ $storefrontSettings['announcement_text'] }}</a>
        @else
            <span data-storefront-announcement-copy>{{ $storefrontSettings['announcement_text'] }}</span>
        @endif
    </div>
@endif
@if(($sellerRating['public'] ?? false) || (($sellerRating['count'] ?? 0) > 0 && ($isPreview ?? false)))
    <div class="storefront-rating-strip" aria-label="Verified customer rating">
        <span aria-hidden="true">★</span>
        <strong>{{ number_format((float) ($sellerRating['average'] ?? 0), 1) }}</strong>
        <small>{{ $sellerRating['count'] }} verified {{ \Illuminate\Support\Str::plural('review', $sellerRating['count']) }}</small>
    </div>
@endif
@if($activeStorefrontTheme === 'fashion')
    <header class="storefront-fashion-header">
        <div class="storefront-fashion-cover {{ $storefrontSettings['hero_image_path'] ? '' : 'is-empty' }}">
            @if($storefrontSettings['hero_image_path'])
                <img src="{{ asset('storage/'.$storefrontSettings['hero_image_path']) }}" alt="{{ $profile->business_name }} campaign">
            @else
                <span>Add a campaign cover in the design editor</span>
            @endif
        </div>
        <div class="storefront-fashion-header-row">
            <a href="#storefront-top" class="storefront-fashion-brand {{ $storefrontSettings['logo_path'] ? 'has-logo' : '' }}" aria-label="{{ $profile->business_name }} home">
                @if($storefrontSettings['logo_path'])
                    <img src="{{ asset('storage/'.$storefrontSettings['logo_path']) }}" alt="{{ $profile->business_name }} logo">
                @else
                    <span>{{ $profile->business_name }}</span>
                @endif
            </a>
            <nav aria-label="Store actions">
                @if($storefrontSettings['show_search'] || ($isPreview ?? false))
                    <a href="#storefront-search" aria-label="Search products"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg></a>
                @endif
                <button type="button" data-fashion-wishlist-open aria-label="Open wishlist"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"></path></svg><b data-fashion-wishlist-count hidden>0</b></button>
                <a href="#cart-section" aria-label="Open shopping bag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8Z"></path><path d="M9 10V7a3 3 0 0 1 6 0v3"></path></svg><b data-cart-count @if(($cart['count'] ?? 0) < 1) hidden @endif>{{ $cart['count'] ?? 0 }}</b></a>
            </nav>
        </div>
        <nav class="storefront-fashion-categories" aria-label="Fashion categories">
            <a href="#shop-section" class="is-active">New in</a>
            @foreach($marketCategories->take(3) as $category)<a href="#shop-section" data-fashion-category="{{ \Illuminate\Support\Str::lower($category) }}">{{ $category }}</a>@endforeach
            @if($marketCategories->isEmpty())<a href="#shop-section">Clothing</a><a href="#shop-section">Accessories</a><a href="#shop-section">The edit</a>@endif
        </nav>
    </header>
@elseif($activeStorefrontTheme === 'perfume')
    <header class="storefront-perfume-header">
        <a href="#shop-section" class="storefront-perfume-brand" aria-label="{{ $profile->business_name }} home">
            @if($storefrontSettings['logo_path'])
                <img src="{{ asset('storage/'.$storefrontSettings['logo_path']) }}" alt="{{ $profile->business_name }} logo">
            @else
                <span>{{ $profile->business_name }}</span>
            @endif
        </a>
        <nav aria-label="Store actions">
            @if($storefrontSettings['show_search'] || ($isPreview ?? false))
                <a href="#storefront-search" aria-label="Search products">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
                </a>
            @endif
            <a href="#cart-section" aria-label="Open cart">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L21 7H6"></path><circle cx="10" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle></svg>
                <span data-cart-count @if(($cart['count'] ?? 0) < 1) hidden @endif>{{ $cart['count'] ?? 0 }}</span>
            </a>
        </nav>
    </header>
@elseif($activeStorefrontTheme === 'market')
    <header class="storefront-market-header">
        <div class="storefront-market-header-row">
            <a href="#storefront-top" class="storefront-market-brand" aria-label="{{ $profile->business_name }} home">
                @if($storefrontSettings['logo_path'])
                    <img src="{{ asset('storage/'.$storefrontSettings['logo_path']) }}" alt="{{ $profile->business_name }} logo">
                @else
                    <span>{{ $profile->business_name }}</span>
                @endif
                <small>Curated marketplace</small>
            </a>
            <nav aria-label="Store actions">
                <a href="{{ route('customer.orders.login') }}" aria-label="Track orders"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3"></circle><path d="M6.5 20a5.5 5.5 0 0 1 11 0"></path></svg></a>
                <a href="#cart-section" aria-label="Open cart"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L21 7H6"></path><circle cx="10" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle></svg><span data-cart-count @if(($cart['count'] ?? 0) < 1) hidden @endif>{{ $cart['count'] ?? 0 }}</span></a>
            </nav>
        </div>
        @if($storefrontSettings['show_search'] || ($isPreview ?? false))
            <label id="marketplace-search" class="storefront-market-search" @if(! $storefrontSettings['show_search']) hidden @endif>
                <span class="sr-only">Search products</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                <input type="search" placeholder="Search all of {{ $profile->business_name }}" autocomplete="off" data-storefront-search>
            </label>
        @endif
        <nav class="storefront-market-tabs" aria-label="Marketplace sections">
            <a href="#storefront-top" aria-current="page">Discover</a>
            <a href="#market-categories">Categories</a>
            <a href="#shop-section">New in</a>
        </nav>
    </header>
@endif
@if($activeStorefrontTheme === 'perfume')
    <section class="storefront-perfume-hero">
        <div class="storefront-perfume-cover {{ $storefrontSettings['hero_image_path'] ? '' : 'is-empty' }}">
            @if($storefrontSettings['hero_image_path'])
                <img src="{{ asset('storage/'.$storefrontSettings['hero_image_path']) }}" alt="{{ $profile->business_name }} collection">
            @else
                <span>Add a cover image in the design editor</span>
            @endif
        </div>
        <div class="storefront-perfume-hero-copy">
            <p class="storefront-perfume-eyebrow">{{ $storefrontCopy['eyebrow'] }}</p>
            <h1 class="storefront-perfume-title" data-storefront-default="A collection that feels like you.">{{ $storefrontSettings['tagline'] ?: 'A collection that feels like you.' }}</h1>
            <p class="storefront-intro" data-storefront-default="{{ $storefrontCopy['intro'] }}">{{ $storefrontSettings['description'] ?: $storefrontCopy['intro'] }}</p>
            <a href="#shop-section" class="storefront-perfume-cta">Find your signature</a>
        </div>
    </section>
@elseif($activeStorefrontTheme === 'market')
    <section class="storefront-market-hero">
        <div class="storefront-market-hero-copy">
            <p><span></span>{{ $storefrontCopy['eyebrow'] }}</p>
            <h1 class="storefront-market-title" data-storefront-default="Everyday, upgraded.">{{ $storefrontSettings['tagline'] ?: 'Everyday, upgraded.' }}</h1>
            <span class="storefront-intro" data-storefront-default="{{ $storefrontCopy['intro'] }}">{{ $storefrontSettings['description'] ?: $storefrontCopy['intro'] }}</span>
            <div class="storefront-market-hero-actions">
                <a href="#shop-section">Explore the edit <span aria-hidden="true">&rarr;</span></a>
                <small>{{ $products->count() }} {{ $products->count() === 1 ? 'product' : 'products' }} available</small>
            </div>
        </div>
        <div class="storefront-market-cover {{ $storefrontSettings['hero_image_path'] ? '' : 'is-empty' }}">
            @if($storefrontSettings['hero_image_path'])
                <img src="{{ asset('storage/'.$storefrontSettings['hero_image_path']) }}" alt="{{ $profile->business_name }} collection">
            @else
                <span>Add a cover image in the design editor</span>
            @endif
            <div class="storefront-market-cover-note" aria-hidden="true">
                <span>Selected by</span>
                <strong>{{ $profile->business_name }}</strong>
            </div>
        </div>
    </section>
@elseif($activeStorefrontTheme !== 'fashion')
<div class="storefront-hero lp-card border-l-4 border-l-emerald-700 p-5 sm:p-6">
    <div class="storefront-hero-inner">
        <div class="storefront-hero-copy min-w-0">
            <div class="storefront-logo {{ $storefrontSettings['logo_path'] ? '' : 'storefront-logo-empty' }}" @if(! $storefrontSettings['logo_path']) hidden @endif>
                @if($storefrontSettings['logo_path'])
                    <img src="{{ asset('storage/'.$storefrontSettings['logo_path']) }}" alt="{{ $profile->business_name }} logo">
                @endif
            </div>
            <p class="storefront-tagline lp-eyebrow" data-storefront-default="{{ $storefrontCopy['eyebrow'] }}">{{ $storefrontSettings['tagline'] ?: $storefrontCopy['eyebrow'] }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">{{ $profile->business_name }}</h1>
            <p class="storefront-intro mt-2 max-w-2xl text-sm leading-6 text-slate-600" data-storefront-default="{{ $storefrontCopy['intro'] }}">{{ $storefrontSettings['description'] ?: $storefrontCopy['intro'] }}</p>
            @if($profile->phone)
                <p class="storefront-contact mt-3 text-xs font-semibold text-slate-500">Questions? {{ $profile->phone }}</p>
            @endif
        </div>
        <div class="storefront-hero-side">
            @if($storefrontSettings['hero_image_path'])
                <div class="storefront-hero-media"><img src="{{ asset('storage/'.$storefrontSettings['hero_image_path']) }}" alt=""></div>
            @endif
            @if(! $profile->paystack_subaccount_code)
                <span class="inline-flex self-start rounded-md bg-amber-100 px-3 py-2 text-xs font-semibold text-amber-800">Online payment unavailable</span>
            @else
                <span class="inline-flex self-start items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800"><span class="h-2 w-2 rounded-full bg-emerald-600"></span>Secure checkout</span>
            @endif
        </div>
    </div>
</div>
@endif

@php
    $fulfillmentSummary = $profile->resolvedFulfillmentOptions();
    $hasFulfillmentOptions = count($fulfillmentSummary) > 0;
@endphp
@if($hasFulfillmentOptions || ($isPreview ?? false))
    <section class="storefront-fulfillment-summary" data-storefront-fulfillment @if(! $hasFulfillmentOptions) hidden @endif>
        <div class="storefront-fulfillment-summary-head"><span>Delivery &amp; pickup</span><strong>Choose what works for you</strong></div>
        <div class="storefront-fulfillment-summary-grid">
            @foreach($fulfillmentSummary as $option)
                <article data-fulfillment-summary="{{ $option['key'] }}">
                    <span class="storefront-fulfillment-summary-icon" aria-hidden="true">{{ $option['type'] === 'pickup' ? 'P' : 'D' }}</span>
                    <div><strong>{{ $option['type'] === 'pickup' ? 'Pickup station' : 'Delivery area' }}</strong><small data-fulfillment-detail>{{ $option['label'] }}</small><p data-fulfillment-description @if(! $option['details']) hidden @endif>{{ $option['details'] }}</p></div>
                    <b data-fulfillment-price>{{ (float) $option['fee'] > 0 ? \App\Support\Money::format($option['fee'], $currency) : 'Free' }}</b>
                </article>
            @endforeach
        </div>
    </section>
@endif

@if($storefrontSettings['show_trust_bar'] || ($isPreview ?? false))
    <div class="storefront-trust-bar" data-storefront-trust @if(! $storefrontSettings['show_trust_bar']) hidden @endif>
        <span><strong>Secure payment</strong><small>Protected checkout</small></span>
        <span data-storefront-delivery @if(! $storefrontSettings['delivery_message']) hidden @endif><strong>Delivery</strong><small>{{ $storefrontSettings['delivery_message'] }}</small></span>
        <span data-storefront-returns @if(! $storefrontSettings['returns_message']) hidden @endif><strong>Returns</strong><small>{{ $storefrontSettings['returns_message'] }}</small></span>
        <span><strong>Direct support</strong><small>Contact the business</small></span>
    </div>
@endif

@if($errors->any())
    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
        <p class="font-semibold">Please correct the highlighted fields and try again.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-xs sm:text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if(session('status') === 'interest-captured')
    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        Thanks! The seller has been notified.
    </div>
@endif
@if(session('status') === 'cart-updated')
    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        Cart updated.
    </div>
@endif

@php
    $cartProductIds = collect($cart['items'] ?? [])
        ->map(fn ($item) => (int) data_get($item, 'product.id'))
        ->filter()
        ->values()
        ->all();
@endphp

@if($template === 'services')
    <div id="shop-section" class="storefront-shop lp-card mt-6 p-5 sm:p-6">
        <div class="flex items-end justify-between gap-4 border-b border-slate-200 pb-4">
            <div><p class="lp-eyebrow">Available now</p><h2 class="mt-1 text-xl font-semibold text-slate-900">Menu & services</h2></div>
            <span class="text-xs font-semibold text-slate-500">{{ $products->count() }} options</span>
        </div>
        <div class="storefront-service-list mt-2">
            @forelse($products as $product)
                <article class="storefront-service-card border-b border-slate-200 py-5 last:border-b-0">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        @if($product->image_path)
                            <a href="{{ route('public.product', ['product_slug' => $product->slug]) }}" class="storefront-service-media shrink-0 overflow-hidden rounded-md bg-slate-100">
                                <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            </a>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="text-lg font-semibold text-slate-900">{{ $product->name }}</h3>
                            <p class="storefront-product-description mt-1 max-w-2xl text-sm leading-6 text-slate-600">{{ $product->description ?: 'Contact the seller if you need more information.' }}</p>
                            <span class="storefront-product-stock mt-2 inline-flex rounded px-2 py-1 text-xs font-semibold {{ $product->statusBadgeClass() }}">{{ $product->statusLabel() }}</span>
                        </div>
                        <div class="storefront-service-price text-left sm:min-w-40 sm:text-right">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Price</p>
                            <p class="mt-1 text-xl font-semibold text-emerald-700">{{ \App\Support\Money::format($product->price, $currency) }}</p>
                        </div>
                    </div>
                    <details class="storefront-service-order {{ $product->image_path ? 'storefront-service-order-has-media' : '' }} mt-3" {{ $errors->any() ? 'open' : '' }}>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-md border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800">
                            <span>Order options</span>
                            <span class="text-xs font-medium text-slate-500">Add to cart or pay now <span aria-hidden="true">+</span></span>
                        </summary>
                    <form method="POST" action="{{ route('public.products.pay', [$profile->public_slug, $product]) }}" class="storefront-service-form mt-3 grid gap-3 border-l-2 border-slate-200 pl-4 sm:grid-cols-[1.2fr_1fr_1fr]">
                        @csrf
                        <input
                            name="phone_number"
                            value="{{ old('phone_number') }}"
                            placeholder="Phone number"
                            class="sm:col-span-3 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 @error('phone_number') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
                            data-strip-leading-zero="true"
                            inputmode="numeric"
                            autocomplete="tel"
                            required
                        />
                        @error('phone_number') <p class="sm:col-span-3 -mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        @include('public.partials.fulfillment-options', ['baseTotal' => (string) $product->price])
                        <input
                            name="location"
                            value="{{ old('location') }}"
                            placeholder="Location (optional)"
                            class="sm:col-span-3 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 @error('location') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
                            data-fulfillment-address
                        />
                        @error('location') <p class="sm:col-span-3 -mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        <textarea
                            name="note"
                            rows="2"
                            placeholder="Notes for seller (optional)"
                            class="sm:col-span-3 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 @error('note') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
                        >{{ old('note') }}</textarea>
                        @error('note') <p class="sm:col-span-3 -mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        <input type="hidden" name="phone_country" value="+233" />
                        <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}" />
                        @php
                            $canPay = $product->isPayable();
                            $isAddedToCart = in_array((int) $product->id, $cartProductIds, true);
                            $sellerPhone = $profile->phone ?: ($profile->user?->phone);
                            $sellerPhone = $sellerPhone ? \App\Support\Phone::normalize($sellerPhone, '+233') : null;
                            $productUrl = route('public.product', ['product_slug' => $product->slug]);
                            $chatMessage = "Hi, I am interested in {$product->name}.\nLink: {$productUrl}";
                            $chatUrl = $sellerPhone ? \App\Support\WhatsApp::chatUrl($sellerPhone, $chatMessage) : null;
                        @endphp
                        <div class="sm:col-span-3 flex flex-wrap items-center gap-3">
                            <input type="number" name="quantity" min="1" value="1" class="w-20 rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <button
                                type="submit"
                                formaction="{{ route('public.products.cart.add', [$profile->public_slug, $product]) }}"
                                formnovalidate
                                class="rounded-full border px-4 py-3 text-sm {{ $canPay ? ($isAddedToCart ? 'border-emerald-600 bg-emerald-50 font-bold text-emerald-700 hover:bg-emerald-100' : 'border-slate-200 font-semibold text-slate-700 hover:border-emerald-200 hover:text-emerald-700') : 'border-slate-200 font-semibold text-slate-400 cursor-not-allowed' }}"
                                {{ $canPay ? '' : 'disabled' }}
                            >
                                {{ $canPay ? ($isAddedToCart ? 'Added' : 'Add to cart') : 'Unavailable' }}
                            </button>
                            <button type="submit" class="rounded-full px-4 py-3 text-sm font-semibold text-white {{ ($canPay && ($paymentsEnabled ?? true)) ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-slate-300 cursor-not-allowed' }}" data-fulfillment-pay-button data-pay-label="Pay now" {{ ($profile->paystack_subaccount_code && $canPay && ($paymentsEnabled ?? true)) ? '' : 'disabled' }}>
                                @if(! ($paymentsEnabled ?? true))
                                    Payments disabled
                                @else
                                    {{ $canPay ? 'Pay now' : 'Unavailable' }}
                                @endif
                            </button>
                            @if($chatUrl)
                                <a
                                    href="{{ $chatUrl }}"
                                    target="_blank"
                                    rel="noreferrer noopener"
                                    aria-label="Chat on WhatsApp"
                                    title="Chat on WhatsApp"
                                    class="storefront-whatsapp-action inline-flex h-11 w-11 items-center justify-center rounded-full border border-emerald-200 text-emerald-700 hover:bg-emerald-50"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true">
                                        <path d="M20.5 3.5A11 11 0 0 0 3.54 17.14L2 22l4.99-1.5A11 11 0 1 0 20.5 3.5Zm-8.52 17a9.08 9.08 0 0 1-4.63-1.27l-.33-.19-2.97.89.9-2.9-.22-.35a9.09 9.09 0 1 1 7.25 3.82Zm4.98-6.84c-.27-.14-1.62-.8-1.87-.89-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.31.2-.58.07a7.46 7.46 0 0 1-2.19-1.35 8.24 8.24 0 0 1-1.52-1.9c-.16-.27-.02-.42.12-.56.12-.12.27-.31.41-.47.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.47-.07-.14-.61-1.48-.84-2.03-.22-.53-.45-.46-.61-.47h-.52c-.18 0-.47.07-.72.34s-.95.93-.95 2.27.98 2.63 1.11 2.81c.14.18 1.92 2.93 4.66 4.11.65.28 1.16.45 1.55.58.65.21 1.24.18 1.71.11.52-.08 1.62-.66 1.85-1.3.23-.64.23-1.18.16-1.3-.07-.12-.25-.2-.52-.34Z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </form>
                    </details>
                </article>
            @empty
                <div class="lp-empty-state">
                    No active services yet.
                </div>
            @endforelse
        </div>
    </div>
@else
    <div id="shop-section" class="storefront-shop lp-card mt-6 p-4 sm:p-5">
        <div class="storefront-market-collection-heading">
            @if($activeStorefrontTheme === 'perfume')
                <p class="storefront-perfume-eyebrow">Follow your nature</p>
            @elseif($activeStorefrontTheme === 'market')
                <p class="storefront-market-category-label">Browse the shop</p>
                <div id="market-categories" class="storefront-market-categories">
                    <button type="button" class="is-active" data-market-category="all">All products</button>
                    @foreach($marketCategories as $category)
                        <button type="button" data-market-category="{{ \Illuminate\Support\Str::lower($category) }}">{{ $category }}</button>
                    @endforeach
                </div>
                <h2>Popular right now</h2>
                <p>Useful products, clearly priced and ready to order.</p>
            @elseif($activeStorefrontTheme === 'fashion')
                @if($products->isNotEmpty())
                    <div class="storefront-fashion-editorial">
                        @foreach($products->take(2) as $editorialProduct)
                            <a href="{{ route('public.product', ['product_slug' => $editorialProduct->slug]) }}">
                                @if($editorialProduct->image_path)<img src="{{ asset('storage/'.$editorialProduct->image_path) }}" alt="">@else<div></div>@endif
                                <span>{{ $editorialProduct->category ?: $editorialProduct->name }} <b aria-hidden="true">↗</b></span>
                            </a>
                        @endforeach
                    </div>
                @endif
                <h2>Your next everyday favourites</h2>
                <p>{{ $storefrontSettings['description'] ?: $storefrontCopy['intro'] }}</p>
            @else
                <h2>Shop the collection</h2>
                <p>Explore available products selected for everyday shopping.</p>
            @endif
        </div>
        @if(($storefrontSettings['show_search'] || ($isPreview ?? false)) && $activeStorefrontTheme !== 'market')
            <label id="storefront-search" class="storefront-search" @if(! $storefrontSettings['show_search']) hidden @endif>
                <span class="sr-only">Search products</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                <input type="search" placeholder="Search products" autocomplete="off" data-storefront-search>
            </label>
        @endif
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="-mx-2 flex overflow-x-auto px-2 sm:mx-0 sm:px-0">
                @foreach($statusTabs ?? [] as $tab)
                    @php
                        $isActiveTab = ($statusFilter ?? 'all') === $tab['key'];
                        $tabLabel = $activeStorefrontTheme === 'perfume'
                            ? match ($tab['key']) {
                                'all' => 'All scents',
                                \App\Models\Product::STATUS_IN_STOCK => 'Available',
                                \App\Models\Product::STATUS_LOW_STOCK => 'Last chance',
                                \App\Models\Product::STATUS_PRE_ORDER => 'Coming soon',
                                default => $tab['label'],
                            }
                            : $tab['label'];
                        $tabUrl = ($isPreview ?? false)
                            ? route('public.preview', array_filter([
                                'theme' => $activeStorefrontTheme,
                                'status' => $tab['key'] !== 'all' ? $tab['key'] : null,
                            ]))
                            : route('public.listing', $profile->public_slug).'?'.http_build_query(array_filter([
                                'status' => $tab['key'] !== 'all' ? $tab['key'] : null,
                            ]));
                    @endphp
                    <a href="{{ $tabUrl }}" class="whitespace-nowrap border-b-2 px-3 py-2 text-sm font-semibold {{ $isActiveTab ? 'border-emerald-700 text-emerald-800' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">
                        {{ $tabLabel }}
                    </a>
                @endforeach
            </div>
            <span class="inline-flex items-center rounded-md bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">
                {{ $products->count() }} results
            </span>
        </div>

        @if($activeStorefrontTheme === 'perfume')
            <div class="storefront-perfume-collection-title">
                <h2>The signature collection</h2>
                <p>Explore the fragrances available from {{ $profile->business_name }}.</p>
            </div>
        @endif

        @if($activeStorefrontTheme === 'market')
            <div class="storefront-market-toolbar">
                <button type="button" data-market-filter-open>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h10M18 7h2M14 5v4M4 17h2M10 17h10M6 15v4M4 12h4M12 12h8M8 10v4"></path></svg>
                    Filters
                </button>
                <label><span class="sr-only">Sort products</span><select data-market-sort><option value="recommended">Sort: Recommended</option><option value="price_asc">Price: Low to high</option><option value="price_desc">Price: High to low</option><option value="name">Name: A to Z</option></select></label>
            </div>
            <p class="storefront-market-loading-status" aria-live="polite">Loading your selection…</p>
        @endif

        @if($activeStorefrontTheme === 'perfume')
            <p class="storefront-perfume-loading-status" aria-live="polite">Finding your fragrances…</p>
        @endif
        <div class="storefront-products-grid mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
            @forelse($products as $product)
                @php
                    $canPay = $product->isPayable();
                    $isAddedToCart = in_array((int) $product->id, $cartProductIds, true);
                    $sellerPhone = $profile->phone ?: ($profile->user?->phone);
                    $sellerPhone = $sellerPhone ? \App\Support\Phone::normalize($sellerPhone, '+233') : null;
                    $productUrl = route('public.product', ['product_slug' => $product->slug]);
                    $chatMessage = "Hi, I am interested in {$product->name}.\nLink: {$productUrl}";
                    $chatUrl = $sellerPhone ? \App\Support\WhatsApp::chatUrl($sellerPhone, $chatMessage) : null;
                    $statusDot = match ($product->status) {
                        \App\Models\Product::STATUS_LOW_STOCK => 'bg-amber-500',
                        \App\Models\Product::STATUS_PRE_ORDER => 'bg-indigo-500',
                        \App\Models\Product::STATUS_SOLD_OUT => 'bg-rose-500',
                        default => 'bg-emerald-600',
                    };
                    $isFeatured = in_array((int) $product->id, array_map('intval', $storefrontSettings['featured_product_ids']), true);
                    $productImageUrl = $product->image_path ? asset('storage/'.$product->image_path) : '';
                    $cartAction = route('public.products.cart.add', [$profile->public_slug, $product]);
                @endphp
                <article
                    class="storefront-product-card group overflow-hidden rounded-lg border border-slate-200 bg-white transition-colors hover:border-slate-400"
                    data-product-id="{{ $product->id }}"
                    data-product-search="{{ \Illuminate\Support\Str::lower($product->name.' '.$product->description.' '.$product->category) }}"
                    data-product-category="{{ \Illuminate\Support\Str::lower((string) $product->category) }}"
                    data-product-status-key="{{ $product->status }}"
                    data-product-price-value="{{ (float) $product->price }}"
                    data-product-original-order="{{ $loop->index }}"
                    @if(in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true))
                        data-perfume-product
                        data-product-name="{{ $product->name }}"
                        data-product-description="{{ $product->description ?: 'A considered selection from '.$profile->business_name.'.' }}"
                        data-product-price="{{ \App\Support\Money::format($product->price, $currency) }}"
                        data-product-status="{{ $product->statusLabel() }}"
                        data-product-image="{{ $productImageUrl }}"
                        data-product-url="{{ $productUrl }}"
                        data-cart-action="{{ $cartAction }}"
                        data-can-pay="{{ $canPay ? '1' : '0' }}"
                    @endif
                >
                    <div class="storefront-product-media relative bg-slate-100">
                        <div class="storefront-product-badges" aria-label="Product availability">
                            <span class="storefront-status-badge rounded bg-white/95 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-slate-700 shadow-sm">{{ $product->statusLabel() }}</span>
                            @if($isFeatured)<span class="storefront-featured-badge">Featured</span>@endif
                        </div>
                        <a href="{{ $productUrl }}" class="block" @if(in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true)) data-perfume-product-open @endif>
                            @if($product->image_path)
                                <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="storefront-product-image aspect-[4/5] w-full object-cover">
                            @else
                                <div class="storefront-product-image flex aspect-[4/5] items-center justify-center bg-slate-100 text-slate-400">
                                    <span class="text-xs uppercase tracking-[0.25em]">No image</span>
                                </div>
                            @endif
                        </a>
                        @if($chatUrl)
                            <a
                                href="{{ $chatUrl }}"
                                target="_blank"
                                rel="noreferrer noopener"
                                aria-label="Chat on WhatsApp"
                                title="Chat on WhatsApp"
                                class="storefront-whatsapp-action absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full border border-emerald-200 bg-white/95 text-emerald-700 shadow-sm hover:bg-emerald-50"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true">
                                    <path d="M20.5 3.5A11 11 0 0 0 3.54 17.14L2 22l4.99-1.5A11 11 0 1 0 20.5 3.5Zm-8.52 17a9.08 9.08 0 0 1-4.63-1.27l-.33-.19-2.97.89.9-2.9-.22-.35a9.09 9.09 0 1 1 7.25 3.82Zm4.98-6.84c-.27-.14-1.62-.8-1.87-.89-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.31.2-.58.07a7.46 7.46 0 0 1-2.19-1.35 8.24 8.24 0 0 1-1.52-1.9c-.16-.27-.02-.42.12-.56.12-.12.27-.31.41-.47.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.47-.07-.14-.61-1.48-.84-2.03-.22-.53-.45-.46-.61-.47h-.52c-.18 0-.47.07-.72.34s-.95.93-.95 2.27.98 2.63 1.11 2.81c.14.18 1.92 2.93 4.66 4.11.65.28 1.16.45 1.55.58.65.21 1.24.18 1.71.11.52-.08 1.62-.66 1.85-1.3.23-.64.23-1.18.16-1.3-.07-.12-.25-.2-.52-.34Z"/>
                                </svg>
                            </a>
                        @endif
                        <form method="POST" action="{{ route('public.products.cart.add', [$profile->public_slug, $product]) }}" class="storefront-market-quick-add">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button
                                type="submit"
                                aria-label="{{ $isAddedToCart ? 'Added to cart' : 'Quick add '.$product->name.' to cart' }}"
                                title="{{ $isAddedToCart ? 'Added to cart' : 'Quick add to cart' }}"
                                class="storefront-market-quick-add-button"
                                {{ $canPay ? '' : 'disabled' }}
                            >
                                {{ $canPay ? ($isAddedToCart ? '✓' : '+') : '—' }}
                            </button>
                        </form>
                        @if($activeStorefrontTheme === 'fashion')
                            <button type="button" class="storefront-fashion-wishlist" data-fashion-wishlist data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" aria-label="Save {{ $product->name }} to wishlist" aria-pressed="false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"></path></svg>
                            </button>
                        @endif
                    </div>
                    <div class="storefront-product-body space-y-3 p-3.5">
                        @if($activeStorefrontTheme === 'market')
                            <span class="storefront-market-card-category">{{ $product->category ?: 'Everyday find' }}</span>
                        @endif
                        <a href="{{ $productUrl }}" class="block text-sm font-semibold leading-5 text-slate-900 hover:text-emerald-700" @if(in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true)) data-perfume-product-open @endif>{{ $product->name }}</a>
                        <p class="storefront-product-description line-clamp-1 text-xs text-slate-500">{{ $product->description ?: $product->statusLabel() }}</p>
                        <div class="flex items-center justify-between gap-3">
                            <span class="storefront-product-stock inline-flex items-center gap-1.5 text-[11px] font-medium text-slate-500"><span class="h-2 w-2 rounded-full {{ $statusDot }}"></span>{{ $product->statusLabel() }}</span>
                            <span class="storefront-product-price text-base font-semibold text-slate-950">{{ \App\Support\Money::format($product->price, $currency) }}</span>
                        </div>
                        <div class="storefront-product-actions grid w-full grid-cols-3 gap-2 text-xs md:flex md:items-center">
                            <form method="POST" action="{{ route('public.products.cart.add', [$profile->public_slug, $product]) }}" class="storefront-primary-add w-full">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button
                                    type="submit"
                                    class="min-h-9 w-full rounded-lg border px-2.5 py-1 {{ $canPay ? ($isAddedToCart ? 'border-emerald-600 bg-emerald-50 font-bold text-emerald-700 hover:bg-emerald-100' : 'border-slate-200 font-semibold text-slate-600 hover:border-emerald-300 hover:text-emerald-700') : 'border-slate-200 font-semibold text-slate-400 cursor-not-allowed' }}"
                                    {{ $canPay ? '' : 'disabled' }}
                                >
                                    {{ $canPay ? ($isAddedToCart ? 'Added' : 'Add') : 'Unavailable' }}
                                </button>
                            </form>
                            <a href="{{ $productUrl }}" class="inline-flex min-h-9 w-full items-center justify-center rounded-lg border border-slate-200 px-2.5 py-1 font-semibold text-slate-600 hover:border-emerald-300 hover:text-emerald-700" @if($activeStorefrontTheme === 'fashion') data-perfume-product-open @endif>{{ $activeStorefrontTheme === 'fashion' ? ($canPay ? 'Quick view' : 'Sold out') : 'Details' }}</a>
                            @if($chatUrl)
                                <a
                                    href="{{ $chatUrl }}"
                                    target="_blank"
                                    rel="noreferrer noopener"
                                    aria-label="Chat on WhatsApp"
                                    title="Chat on WhatsApp"
                                    class="storefront-whatsapp-action inline-flex w-full items-center justify-center rounded-full border border-emerald-200 px-2.5 py-1 font-semibold text-emerald-700 hover:bg-emerald-50 md:h-8 md:w-8 md:p-0"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true">
                                        <path d="M20.5 3.5A11 11 0 0 0 3.54 17.14L2 22l4.99-1.5A11 11 0 1 0 20.5 3.5Zm-8.52 17a9.08 9.08 0 0 1-4.63-1.27l-.33-.19-2.97.89.9-2.9-.22-.35a9.09 9.09 0 1 1 7.25 3.82Zm4.98-6.84c-.27-.14-1.62-.8-1.87-.89-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.31.2-.58.07a7.46 7.46 0 0 1-2.19-1.35 8.24 8.24 0 0 1-1.52-1.9c-.16-.27-.02-.42.12-.56.12-.12.27-.31.41-.47.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.47-.07-.14-.61-1.48-.84-2.03-.22-.53-.45-.46-.61-.47h-.52c-.18 0-.47.07-.72.34s-.95.93-.95 2.27.98 2.63 1.11 2.81c.14.18 1.92 2.93 4.66 4.11.65.28 1.16.45 1.55.58.65.21 1.24.18 1.71.11.52-.08 1.62-.66 1.85-1.3.23-.64.23-1.18.16-1.3-.07-.12-.25-.2-.52-.34Z"/>
                                    </svg>
                                </a>
                            @else
                                <span class="storefront-whatsapp-action inline-flex w-full items-center justify-center rounded-full border border-slate-200 px-2.5 py-1 text-slate-400 md:h-8 md:w-8 md:p-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true">
                                        <path d="M20.5 3.5A11 11 0 0 0 3.54 17.14L2 22l4.99-1.5A11 11 0 1 0 20.5 3.5Zm-8.52 17a9.08 9.08 0 0 1-4.63-1.27l-.33-.19-2.97.89.9-2.9-.22-.35a9.09 9.09 0 1 1 7.25 3.82Zm4.98-6.84c-.27-.14-1.62-.8-1.87-.89-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.31.2-.58.07a7.46 7.46 0 0 1-2.19-1.35 8.24 8.24 0 0 1-1.52-1.9c-.16-.27-.02-.42.12-.56.12-.12.27-.31.41-.47.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.47-.07-.14-.61-1.48-.84-2.03-.22-.53-.45-.46-.61-.47h-.52c-.18 0-.47.07-.72.34s-.95.93-.95 2.27.98 2.63 1.11 2.81c.14.18 1.92 2.93 4.66 4.11.65.28 1.16.45 1.55.58.65.21 1.24.18 1.71.11.52-.08 1.62-.66 1.85-1.3.23-.64.23-1.18.16-1.3-.07-.12-.25-.2-.52-.34Z"/>
                                    </svg>
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="lp-empty-state col-span-full">
                    @if($activeStorefrontTheme === 'perfume')
                        <strong>Your next signature awaits.</strong>
                        <span>No fragrances are available in this selection yet.</span>
                        <a href="{{ ($isPreview ?? false) ? route('public.preview', ['theme' => 'perfume']) : route('public.listing', $profile->public_slug) }}">Explore all fragrances</a>
                    @elseif($activeStorefrontTheme === 'market')
                        <strong>No products here yet.</strong>
                        <span>Try another category or check back soon.</span>
                    @else
                        No products found in this filter.
                    @endif
                </div>
            @endforelse
            <div class="lp-empty-state col-span-full" data-storefront-search-empty hidden>
                @if($activeStorefrontTheme === 'perfume')
                    <strong>No matching fragrance.</strong><span>Try another name or scent note.</span>
                @elseif($activeStorefrontTheme === 'market')
                    <strong>No matches this time.</strong><span>Try a broader search, change the filters or explore another category.</span>
                @else
                    No products match your search.
                @endif
            </div>
        </div>
        @if($activeStorefrontTheme === 'market')
            <button type="button" class="storefront-market-load-more" data-market-load-more>Load more products</button>
            <dialog class="storefront-market-filter-dialog" data-market-filter-dialog aria-labelledby="market-filter-title">
                <div class="storefront-market-dialog-head"><h2 id="market-filter-title">Filter & sort</h2><button type="button" data-market-filter-close aria-label="Close filters">×</button></div>
                <form data-market-filter-form>
                    <fieldset>
                        <legend>Category</legend>
                        <label><input type="radio" name="market_category" value="all" checked> All products</label>
                        @foreach($marketCategories as $category)
                            <label><input type="radio" name="market_category" value="{{ \Illuminate\Support\Str::lower($category) }}"> {{ $category }}</label>
                        @endforeach
                    </fieldset>
                    <fieldset>
                        <legend>Price range</legend>
                        <div class="storefront-market-price-range"><input type="number" min="0" step="0.01" placeholder="Minimum" name="market_min_price"><input type="number" min="0" step="0.01" placeholder="Maximum" name="market_max_price"></div>
                    </fieldset>
                    <fieldset>
                        <legend>Availability</legend>
                        <label><input type="radio" name="market_availability" value="all" checked> All availability</label>
                        <label><input type="radio" name="market_availability" value="available"> Available now</label>
                        <label><input type="radio" name="market_availability" value="sold_out"> Sold out</label>
                    </fieldset>
                    <label class="storefront-market-dialog-sort"><span>Sort products</span><select name="market_dialog_sort"><option value="recommended">Recommended</option><option value="price_asc">Price, low to high</option><option value="price_desc">Price, high to low</option><option value="name">Name, A to Z</option></select></label>
                    <div class="storefront-market-filter-actions"><button type="button" data-market-filter-reset>Reset all</button><button type="submit">Show products</button></div>
                </form>
            </dialog>
        @endif
        @if(in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true))
            <dialog class="storefront-perfume-product-dialog {{ $activeStorefrontTheme === 'market' ? 'storefront-market-product-dialog' : '' }} {{ $activeStorefrontTheme === 'fashion' ? 'storefront-fashion-product-dialog' : '' }}" data-perfume-product-dialog aria-labelledby="storefront-product-title">
                <div class="storefront-perfume-dialog-head">
                    <h2 id="storefront-product-title" data-perfume-detail-name>Product details</h2>
                    <button type="button" data-perfume-product-close aria-label="Close product details">×</button>
                </div>
                <div class="storefront-perfume-detail">
                    <div class="storefront-perfume-detail-media" data-perfume-detail-media></div>
                    <div>
                        <span data-perfume-detail-status></span>
                        <p data-perfume-detail-description></p>
                        <strong data-perfume-detail-price></strong>
                    </div>
                </div>
                <form method="POST" action="{{ route('public.listing', $profile->public_slug) }}" data-perfume-detail-form>
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" data-perfume-detail-add>Add to bag</button>
                </form>
                <a href="#" data-perfume-detail-link>View complete product details</a>
                <p class="storefront-perfume-dispatch">Your selection is saved in your bag until checkout.</p>
            </dialog>
        @endif
        @if($activeStorefrontTheme === 'fashion')
            <dialog class="storefront-fashion-wishlist-dialog" data-fashion-wishlist-dialog aria-labelledby="fashion-wishlist-title">
                <div class="storefront-fashion-dialog-head"><h2 id="fashion-wishlist-title">Your wishlist</h2><button type="button" data-fashion-wishlist-close aria-label="Close wishlist">×</button></div>
                <div data-fashion-wishlist-items></div>
                <div class="storefront-fashion-empty-wishlist" data-fashion-wishlist-empty>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8Z"></path><path d="M9 10V7a3 3 0 0 1 6 0v3"></path></svg>
                    <h3>Make room for favourites.</h3>
                    <p>Nothing saved yet. Tap the heart on pieces you love and find them here.</p>
                    <button type="button" data-fashion-wishlist-close>Explore new arrivals</button>
                </div>
            </dialog>
        @endif
        @if($activeStorefrontTheme === 'perfume')
            <p class="storefront-perfume-fulfilment">
                Secure checkout
                @if($storefrontSettings['delivery_message']) · {{ $storefrontSettings['delivery_message'] }} @endif
                @if($storefrontSettings['returns_message']) · {{ $storefrontSettings['returns_message'] }} @endif
            </p>
        @endif
    </div>
@endif

@if(($paymentsEnabled ?? true))
    <div id="cart-section" class="storefront-cart lp-card mt-6 p-5 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                @if(in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true))
                    <h2 class="lp-section-title" data-cart-heading data-cart-heading-label="{{ $activeStorefrontTheme === 'market' ? 'Your cart' : 'Your bag' }}">{{ $activeStorefrontTheme === 'market' ? 'Your cart' : 'Your bag' }} ({{ $cart['count'] ?? 0 }})</h2>
                @else
                    <p class="lp-eyebrow">Your order</p><h2 class="lp-section-title mt-1">Cart and checkout</h2>
                @endif
            </div>
            @if(in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true))
                <button type="button" class="storefront-perfume-cart-close" data-perfume-cart-close aria-label="Close bag">×</button>
            @else
                <span class="text-sm text-slate-500" data-cart-heading data-cart-heading-kind="items">{{ $cart['count'] ?? 0 }} item(s)</span>
            @endif
        </div>

        @if(($cart['count'] ?? 0) > 0)
            <form method="POST" action="{{ route('public.cart.update', $profile->public_slug) }}" class="mt-4 space-y-3" data-cart-update-form>
                @csrf
                @foreach(($cart['items'] ?? collect()) as $item)
                    <div class="grid gap-3 border-b border-slate-200 py-3 first:pt-0 sm:grid-cols-[1fr_auto_auto] sm:items-center" data-cart-row data-cart-product-id="{{ $item['product']->id }}">
                        <div class="storefront-cart-product">
                            @if(in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true) && $item['product']->image_path)
                                <img src="{{ asset('storage/'.$item['product']->image_path) }}" alt="">
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $item['product']->name }}</p>
                                <p class="text-xs text-slate-500">{{ \App\Support\Money::format($item['unit_price'], $currency) }} each</p>
                            </div>
                        </div>
                        <div class="storefront-cart-quantity">
                            @if(in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true))<button type="button" data-quantity-step="-1" aria-label="Decrease quantity">−</button>@endif
                            <input type="number" min="0" max="{{ $item['product']->isInventoryManaged() ? max(0, (int) $item['product']->stock_quantity) : 100 }}" name="items[{{ $item['product']->id }}][quantity]" value="{{ $item['quantity'] }}" class="w-24 rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" data-cart-quantity aria-label="Quantity for {{ $item['product']->name }}">
                            @if(in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true))<button type="button" data-quantity-step="1" aria-label="Increase quantity">+</button>@endif
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <p class="text-sm font-semibold text-slate-900" data-cart-line-total>{{ \App\Support\Money::format($item['line_total'], $currency) }}</p>
                            <button
                                type="submit"
                                formaction="{{ route('public.products.cart.remove', [$profile->public_slug, $item['product']]) }}"
                                name="_method"
                                value="DELETE"
                                class="rounded-full border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                @endforeach
                <p class="text-xs text-slate-500" data-cart-auto-status aria-live="polite">Changes save automatically. Set quantity to 0 to remove an item.</p>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-700">Total</span>
                    <span class="text-lg font-semibold text-emerald-700" data-cart-total>{{ \App\Support\Money::format($cart['total'] ?? '0.00', $currency) }}</span>
                </div>
                <noscript>
                    <button type="submit" name="_method" value="PATCH" class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700">
                        Update cart
                    </button>
                </noscript>
            </form>

            <form method="POST" action="{{ route('public.cart.checkout', $profile->public_slug) }}" class="mt-4 grid gap-3 sm:grid-cols-2" data-cart-checkout-form>
                @csrf
                <div class="sm:col-span-2">
                    <input
                        name="phone_number"
                        value="{{ old('phone_number') }}"
                        placeholder="Phone number"
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 @error('phone_number') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
                        data-strip-leading-zero="true"
                        inputmode="numeric"
                        autocomplete="tel"
                        required
                    />
                    @error('phone_number') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                @include('public.partials.fulfillment-options', ['baseTotal' => (string) ($cart['total'] ?? '0.00')])
                <div class="sm:col-span-2">
                    <input
                        name="location"
                        value="{{ old('location') }}"
                        placeholder="Location (optional)"
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 @error('location') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
                        data-fulfillment-address
                    />
                    @error('location') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <textarea
                        name="note"
                        rows="2"
                        placeholder="Notes for seller (optional)"
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 @error('note') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror"
                    >{{ old('note') }}</textarea>
                    @error('note') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <input type="hidden" name="phone_country" value="+233" />
                <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}" />
                <button type="submit" class="rounded-full bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-500 sm:col-span-2" data-cart-checkout-button data-cart-checkout-prefix="{{ in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true) ? ($activeStorefrontTheme === 'market' ? 'Checkout · ' : 'Secure checkout · ') : '' }}">
                    {{ in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true) ? ($activeStorefrontTheme === 'market' ? 'Checkout · ' : 'Secure checkout · ').\App\Support\Money::format($cart['total'] ?? '0.00', $currency) : 'Pay total' }}
                </button>
            </form>
        @else
            @if(in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true))
                <div class="storefront-perfume-empty-bag">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8Z"></path><path d="M9 10V7a3 3 0 0 1 6 0v3"></path></svg>
                    <h3>{{ $activeStorefrontTheme === 'market' ? 'Your cart is ready when you are.' : ($activeStorefrontTheme === 'fashion' ? 'Your next favourite awaits.' : 'Your next signature awaits.') }}</h3>
                    <p>{{ $activeStorefrontTheme === 'market' ? 'Browse useful finds and add what works for you.' : ($activeStorefrontTheme === 'fashion' ? 'Your bag is empty. Explore the new edit and add a piece you love.' : 'Your bag is empty. Begin with the fragrance that feels most like you.') }}</p>
                    <button type="button" data-perfume-cart-close>{{ $activeStorefrontTheme === 'market' ? 'Continue shopping' : ($activeStorefrontTheme === 'fashion' ? 'Explore new arrivals' : 'Explore fragrances') }}</button>
                </div>
            @else
                <p class="mt-4 text-sm text-slate-500">Cart is empty. Add items above.</p>
            @endif
        @endif
    </div>

    @if(in_array($activeStorefrontTheme, ['perfume', 'market', 'fashion'], true))
        <button type="button" class="storefront-perfume-cart-overlay" data-perfume-cart-close aria-label="Close bag" hidden></button>
    @endif

    <a
        href="#cart-section"
        id="floating-cart-toggle"
        class="fixed bottom-5 right-5 z-40 inline-flex items-center gap-2 rounded-md bg-emerald-700 px-4 py-3 text-sm font-semibold text-white shadow-md transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-300"
    >
        <svg id="floating-icon-cart" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="9" cy="20" r="1"></circle>
            <circle cx="18" cy="20" r="1"></circle>
            <path d="M2 3h2l2.4 12.2a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.7L22 7H6"></path>
        </svg>
        <svg id="floating-icon-shop" xmlns="http://www.w3.org/2000/svg" class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9.5 4.5 4h15L21 9.5"></path>
            <path d="M4 10v9h16v-9"></path>
            <path d="M9 19v-6h6v6"></path>
        </svg>
        <span id="floating-cart-label">Cart</span>
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.getElementById('floating-cart-toggle');
            var cart = document.getElementById('cart-section');
            var shop = document.getElementById('shop-section');
            var label = document.getElementById('floating-cart-label');
            var cartIcon = document.getElementById('floating-icon-cart');
            var shopIcon = document.getElementById('floating-icon-shop');

            if (!toggle || !cart || !shop || !label || !cartIcon || !shopIcon) {
                return;
            }

            var atCart = false;
            var isRefreshing = false;

            function isCartInView() {
                var rect = cart.getBoundingClientRect();
                var viewport = window.innerHeight || document.documentElement.clientHeight;
                return rect.top <= viewport * 0.45 && rect.bottom >= viewport * 0.25;
            }

            function setMode(cartMode) {
                atCart = cartMode;
                toggle.setAttribute('href', cartMode ? '#shop-section' : '#cart-section');
                label.textContent = cartMode ? 'Go to Shop' : 'Cart';
                cartIcon.classList.toggle('hidden', cartMode);
                shopIcon.classList.toggle('hidden', !cartMode);
            }

            function syncMode() {
                setMode(isCartInView());
            }

            function skeletonCardsMarkup(count) {
                var cards = [];
                for (var i = 0; i < count; i++) {
                    cards.push(
                        '<article class="animate-pulse overflow-hidden rounded-xl border border-slate-200 bg-white">'
                        + '<div class="h-44 w-full bg-slate-200 sm:h-56"></div>'
                        + '<div class="space-y-3 p-3">'
                        + '<div class="h-3 w-3/4 rounded bg-slate-200"></div>'
                        + '<div class="h-3 w-1/2 rounded bg-slate-200"></div>'
                        + '<div class="h-8 w-full rounded-full bg-slate-200"></div>'
                        + '</div>'
                        + '</article>'
                    );
                }

                return cards.join('');
            }

            function showShopSkeleton() {
                var grid = shop.querySelector('div.mt-5.grid');
                if (!grid) {
                    return;
                }

                shop.classList.add('is-loading');
                grid.setAttribute('aria-busy', 'true');
                grid.innerHTML = skeletonCardsMarkup(6);
            }

            function refreshNodeRefs() {
                toggle = document.getElementById('floating-cart-toggle');
                cart = document.getElementById('cart-section');
                shop = document.getElementById('shop-section');
                label = document.getElementById('floating-cart-label');
                cartIcon = document.getElementById('floating-icon-cart');
                shopIcon = document.getElementById('floating-icon-shop');
            }

            async function refreshSectionsFromResponse(responseText, nextUrl) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(responseText, 'text/html');
                var nextShop = doc.getElementById('shop-section');
                var nextCart = doc.getElementById('cart-section');
                var nextToggle = doc.getElementById('floating-cart-toggle');
                var currentPerfumeHeader = document.querySelector('.storefront-perfume-header, .storefront-market-header, .storefront-fashion-header');
                var nextPerfumeHeader = doc.querySelector('.storefront-perfume-header, .storefront-market-header, .storefront-fashion-header');
                var currentPerfumeNav = document.querySelector('.storefront-mobile-nav');
                var nextPerfumeNav = doc.querySelector('.storefront-mobile-nav');

                if (nextShop && shop) {
                    shop.replaceWith(nextShop);
                }
                if (nextCart && cart) {
                    cart.replaceWith(nextCart);
                }
                if (nextToggle && toggle) {
                    toggle.replaceWith(nextToggle);
                }
                if (nextPerfumeHeader && currentPerfumeHeader) {
                    currentPerfumeHeader.replaceWith(nextPerfumeHeader);
                }
                if (nextPerfumeNav && currentPerfumeNav) {
                    currentPerfumeNav.replaceWith(nextPerfumeNav);
                }

                refreshNodeRefs();
                bindInteractions();
                bindFloatingToggle();
                syncMode();

                if (nextUrl) {
                    window.history.pushState({}, '', nextUrl);
                }
            }

            function trackTelemetry(eventName, payload, level) {
                if (window.lpTelemetry && typeof window.lpTelemetry.track === 'function') {
                    window.lpTelemetry.track(eventName, payload || {}, level || 'info');
                }
            }

            async function asyncRequest(url, options, showSkeleton, pushUrl, telemetryMeta) {
                if (isRefreshing) {
                    return;
                }
                isRefreshing = true;
                var keepPerfumeCartOpen = Boolean(cart?.classList.contains('is-open')) || ['add_to_cart', 'update_cart', 'remove_from_cart'].includes(telemetryMeta?.action);
                var startedAt = window.performance ? performance.now() : Date.now();

                if (showSkeleton) {
                    showShopSkeleton();
                }

                try {
                    var response = await fetch(url, Object.assign({
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                        credentials: 'same-origin',
                    }, options || {}));

                    var html = await response.text();
                    await refreshSectionsFromResponse(html, pushUrl || null);
                    if (keepPerfumeCartOpen) openPerfumeCart();
                    var endedAt = window.performance ? performance.now() : Date.now();
                    if (telemetryMeta && telemetryMeta.action) {
                        trackTelemetry('optimistic_action_success', {
                            screen: window.location.pathname,
                            action: telemetryMeta.action,
                            metricMs: Math.round(endedAt - startedAt),
                        });
                    }
                } catch (error) {
                    var endedAt = window.performance ? performance.now() : Date.now();
                    if (telemetryMeta && telemetryMeta.action) {
                        trackTelemetry('optimistic_action_failed', {
                            screen: window.location.pathname,
                            action: telemetryMeta.action,
                            metricMs: Math.round(endedAt - startedAt),
                            rollback: true,
                        }, 'warn');
                    }
                    window.location.reload();
                } finally {
                    isRefreshing = false;
                }
            }

            function bindFilterTabs() {
                if (!shop) {
                    return;
                }

                shop.querySelectorAll('a[href*="status="]').forEach(function (link) {
                    if (link.dataset.boundAsync === '1') {
                        return;
                    }
                    link.dataset.boundAsync = '1';
                    link.addEventListener('click', function (event) {
                        event.preventDefault();
                        asyncRequest(link.href, { method: 'GET' }, true, link.href, { action: 'filter_products' });
                    });
                });
            }

            function bindAddToCartForms() {
                if (!shop) {
                    return;
                }

                shop.querySelectorAll('form').forEach(function (form) {
                    if (form.closest('#cart-section') || form.dataset.boundAsyncCart === '1') {
                        return;
                    }
                    form.dataset.boundAsyncCart = '1';

                    // Fallback for browsers without event.submitter support.
                    form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                        if (button.dataset.boundClickSubmitter === '1') {
                            return;
                        }
                        button.dataset.boundClickSubmitter = '1';
                        button.addEventListener('click', function () {
                            form.__lastSubmitter = button;
                        });
                    });

                    form.addEventListener('submit', function (event) {
                        var submitter = event.submitter || form.__lastSubmitter || form.querySelector('button[type="submit"]');
                        if (!submitter) {
                            return;
                        }
                        var targetUrl = submitter.getAttribute('formaction') || form.action || '';
                        var isAddToCartAction = targetUrl.indexOf('/products/') !== -1 && targetUrl.indexOf('/cart') !== -1;
                        if (!isAddToCartAction) {
                            return;
                        }

                        event.preventDefault();

                        // Optimistic UI: make action feel instant.
                        submitter.disabled = true;
                        submitter.textContent = 'Added';
                        submitter.classList.add('border-emerald-600', 'bg-emerald-50', 'font-bold', 'text-emerald-700');

                        var payload = new FormData(form, submitter);
                        asyncRequest(targetUrl, { method: 'POST', body: payload }, false, null, { action: 'add_to_cart' });
                    });
                });
            }

            function bindCartForm() {
                if (!cart) {
                    return;
                }

                var updateForm = cart.querySelector('form[action*="/cart"]');
                if (!updateForm || updateForm.action.indexOf('/checkout') !== -1 || updateForm.dataset.boundAsync === '1') {
                    return;
                }
                updateForm.dataset.boundAsync = '1';

                var autoSaveTimer = null;
                var status = updateForm.querySelector('[data-cart-auto-status]');

                function setCartBusy(isBusy) {
                    updateForm.setAttribute('aria-busy', isBusy ? 'true' : 'false');
                    updateForm.querySelectorAll('[data-cart-quantity], button').forEach(function (control) {
                        control.disabled = isBusy;
                    });
                }

                function normalizeQuantity(input) {
                    if (input.value === '') return null;
                    var minimum = Number(input.min || 0);
                    var maximum = Number(input.max || 100);
                    var quantity = Math.trunc(Number(input.value));
                    if (!Number.isFinite(quantity)) return null;
                    quantity = Math.max(minimum, Math.min(maximum, quantity));
                    input.value = String(quantity);
                    return quantity;
                }

                function applyCartResponse(data) {
                    var items = data.items || {};

                    updateForm.querySelectorAll('[data-cart-row]').forEach(function (row) {
                        var productId = String(row.dataset.cartProductId || '');
                        var item = items[productId];
                        if (!item) {
                            row.remove();
                            var productCard = shop?.querySelector('[data-product-id="' + productId + '"]');
                            var addButton = productCard?.querySelector('.storefront-primary-add button');
                            if (addButton) {
                                addButton.disabled = false;
                                addButton.textContent = 'Add';
                            }
                            return;
                        }

                        var input = row.querySelector('[data-cart-quantity]');
                        var lineTotal = row.querySelector('[data-cart-line-total]');
                        if (input) input.value = String(item.quantity);
                        if (lineTotal) lineTotal.textContent = item.line_total;
                    });

                    document.querySelectorAll('[data-cart-heading]').forEach(function (node) {
                        var count = String(data.count || 0);
                        node.textContent = node.dataset.cartHeadingKind === 'items'
                            ? count + ' item(s)'
                            : (node.dataset.cartHeadingLabel || 'Cart') + ' (' + count + ')';
                    });
                    document.querySelectorAll('[data-cart-count]').forEach(function (node) {
                        node.textContent = String(data.count || 0);
                        node.hidden = Number(data.count || 0) < 1;
                    });

                    var total = updateForm.querySelector('[data-cart-total]');
                    if (total) total.textContent = data.total;

                    var checkoutForm = cart.querySelector('[data-cart-checkout-form]');
                    var checkoutButton = checkoutForm?.querySelector('[data-cart-checkout-button]');
                    if (checkoutButton) checkoutButton.textContent = (checkoutButton.dataset.cartCheckoutPrefix || '') + data.total;
                    var fulfillmentGroup = checkoutForm?.querySelector('[data-fulfillment-options]');
                    if (fulfillmentGroup) {
                        fulfillmentGroup.dataset.baseTotal = data.raw_total || '0.00';
                        if (typeof fulfillmentGroup.syncFulfillment === 'function') fulfillmentGroup.syncFulfillment();
                    }
                    if (checkoutForm) checkoutForm.hidden = Number(data.count || 0) < 1;

                    if (Number(data.count || 0) < 1) {
                        updateForm.hidden = true;
                        if (!cart.querySelector('[data-cart-empty-live]')) {
                            var empty = document.createElement('div');
                            empty.dataset.cartEmptyLive = '1';
                            empty.className = 'storefront-perfume-empty-bag';
                            empty.innerHTML = '<h3>Your cart is ready when you are.</h3><p>Your cart is empty. Continue shopping to add another product.</p>';
                            updateForm.insertAdjacentElement('afterend', empty);
                        }
                    }
                }

                async function saveCartAutomatically() {
                    if (isRefreshing) {
                        autoSaveTimer = window.setTimeout(saveCartAutomatically, 180);
                        return;
                    }

                    var inputs = Array.from(updateForm.querySelectorAll('[data-cart-quantity]'));
                    if (inputs.some(function (input) { return normalizeQuantity(input) === null; })) return;

                    var payload = new FormData(updateForm);
                    payload.set('_method', 'PATCH');
                    if (status) status.textContent = 'Updating cart…';
                    setCartBusy(true);
                    var startedAt = window.performance ? performance.now() : Date.now();

                    try {
                        var response = await fetch(updateForm.action, {
                            method: 'POST',
                            body: payload,
                            credentials: 'same-origin',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });
                        var data = await response.json();
                        if (!response.ok) {
                            var validationMessage = data.message || Object.values(data.errors || {}).flat()[0] || 'The cart could not be updated.';
                            throw new Error(validationMessage);
                        }

                        applyCartResponse(data);
                        if (status) status.textContent = 'Cart updated.';
                        trackTelemetry('optimistic_action_success', {
                            screen: window.location.pathname,
                            action: 'update_cart_background',
                            metricMs: Math.round((window.performance ? performance.now() : Date.now()) - startedAt),
                        });
                    } catch (error) {
                        if (status) status.textContent = error.message || 'The cart could not be updated. Try again.';
                        trackTelemetry('optimistic_action_failed', {
                            screen: window.location.pathname,
                            action: 'update_cart_background',
                            metricMs: Math.round((window.performance ? performance.now() : Date.now()) - startedAt),
                            rollback: false,
                        }, 'warn');
                    } finally {
                        setCartBusy(false);
                    }
                }

                function queueAutomaticSave(delay) {
                    window.clearTimeout(autoSaveTimer);
                    if (status) status.textContent = 'Saving changes…';
                    autoSaveTimer = window.setTimeout(saveCartAutomatically, delay || 280);
                }

                updateForm.querySelectorAll('[data-cart-quantity]').forEach(function (input) {
                    input.addEventListener('input', function () {
                        if (input.value !== '') queueAutomaticSave(420);
                    });
                    input.addEventListener('change', function () {
                        if (normalizeQuantity(input) !== null) queueAutomaticSave(180);
                    });
                });

                updateForm.querySelectorAll('button[type="submit"]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        updateForm.__lastSubmitter = button;
                    });
                });

                updateForm.addEventListener('submit', function (event) {
                    var submitter = event.submitter || updateForm.__lastSubmitter || null;
                    updateForm.__lastSubmitter = null;
                    event.preventDefault();
                    window.clearTimeout(autoSaveTimer);

                    if (!submitter || (submitter.value || '').toUpperCase() !== 'DELETE') {
                        saveCartAutomatically();
                        return;
                    }

                    var actionUrl = submitter.getAttribute('formaction') || updateForm.action;
                    var payload = new FormData(updateForm, submitter);

                    // Optimistic remove feedback.
                    var row = submitter.closest('[data-cart-row]');
                    if (row) {
                        row.style.opacity = '0.45';
                    }

                    asyncRequest(actionUrl, { method: 'POST', body: payload }, false, null, { action: 'remove_from_cart' });
                });
            }

            function openPerfumeCart() {
                if (!cart || !cart.closest('.storefront-theme-perfume, .storefront-theme-market, .storefront-theme-fashion')) return;
                var overlay = document.querySelector('.storefront-perfume-cart-overlay');
                cart.classList.add('is-open');
                cart.setAttribute('aria-modal', 'true');
                cart.setAttribute('role', 'dialog');
                if (overlay) overlay.hidden = false;
                document.body.classList.add('storefront-overlay-open');
                cart.querySelector('[data-perfume-cart-close]')?.focus();
            }

            function closePerfumeCart() {
                if (!cart) return;
                var overlay = document.querySelector('.storefront-perfume-cart-overlay');
                cart.classList.remove('is-open');
                cart.removeAttribute('aria-modal');
                cart.removeAttribute('role');
                if (overlay) overlay.hidden = true;
                document.body.classList.remove('storefront-overlay-open');
                shop?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            function bindPerfumeJourney() {
                var storefront = shop?.closest('.storefront-theme-perfume, .storefront-theme-market, .storefront-theme-fashion');
                if (!storefront) return;
                var isMarketplace = storefront.classList.contains('storefront-theme-market');

                storefront.querySelectorAll('a[href="#cart-section"]').forEach(function (link) {
                    if (link.dataset.perfumeCartBound === '1') return;
                    link.dataset.perfumeCartBound = '1';
                    link.addEventListener('click', function (event) { event.preventDefault(); openPerfumeCart(); });
                });
                document.querySelectorAll('[data-perfume-cart-close]').forEach(function (button) {
                    if (button.dataset.perfumeCloseBound === '1') return;
                    button.dataset.perfumeCloseBound = '1';
                    button.addEventListener('click', closePerfumeCart);
                });
                cart?.querySelectorAll('[data-quantity-step]').forEach(function (button) {
                    if (button.dataset.quantityBound === '1') return;
                    button.dataset.quantityBound = '1';
                    button.addEventListener('click', function () {
                        var input = button.parentElement?.querySelector('input[type="number"]');
                        if (!input) return;
                        var next = Math.max(Number(input.min || 0), Math.min(Number(input.max || 100), Number(input.value || 0) + Number(button.dataset.quantityStep || 0)));
                        input.value = String(next);
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                });

                var dialog = shop.querySelector('[data-perfume-product-dialog]');
                shop.querySelectorAll('[data-perfume-product-open]').forEach(function (link) {
                    if (link.dataset.perfumeProductBound === '1') return;
                    link.dataset.perfumeProductBound = '1';
                    link.addEventListener('click', function (event) {
                        if (!dialog || typeof dialog.showModal !== 'function') return;
                        event.preventDefault();
                        var product = link.closest('[data-perfume-product]');
                        if (!product) return;
                        dialog.querySelector('[data-perfume-detail-name]').textContent = product.dataset.productName || 'Product details';
                        dialog.querySelector('[data-perfume-detail-description]').textContent = product.dataset.productDescription || '';
                        dialog.querySelector('[data-perfume-detail-price]').textContent = product.dataset.productPrice || '';
                        dialog.querySelector('[data-perfume-detail-status]').textContent = product.dataset.productStatus || '';
                        var media = dialog.querySelector('[data-perfume-detail-media]');
                        media.replaceChildren();
                        if (product.dataset.productImage) {
                            var image = document.createElement('img');
                            image.src = product.dataset.productImage;
                            image.alt = product.dataset.productName || '';
                            media.appendChild(image);
                        }
                        var form = dialog.querySelector('[data-perfume-detail-form]');
                        form.action = product.dataset.cartAction || form.action;
                        var add = dialog.querySelector('[data-perfume-detail-add]');
                        var available = product.dataset.canPay === '1';
                        add.disabled = !available;
                        add.textContent = available
                            ? (isMarketplace ? 'Add to cart · ' : 'Add to bag · ') + (product.dataset.productPrice || '')
                            : 'Sold out';
                        dialog.querySelector('[data-perfume-detail-link]').href = product.dataset.productUrl || '#';
                        dialog.showModal();
                    });
                });
                var dialogClose = dialog?.querySelector('[data-perfume-product-close]');
                if (dialogClose && dialogClose.dataset.closeBound !== '1') {
                    dialogClose.dataset.closeBound = '1';
                    dialogClose.addEventListener('click', function () { dialog.close(); });
                }
                if (dialog && dialog.dataset.backdropBound !== '1') {
                    dialog.dataset.backdropBound = '1';
                    dialog.addEventListener('click', function (event) { if (event.target === dialog) dialog.close(); });
                }
            }

            function bindMarketplace() {
                var storefront = shop?.closest('.storefront-theme-market');
                if (!storefront || shop.dataset.marketBound === '1') return;
                shop.dataset.marketBound = '1';

                var category = 'all';
                var minPrice = null;
                var maxPrice = null;
                var availability = 'all';
                var visibleLimit = 6;
                var sort = 'recommended';
                var grid = shop.querySelector('.storefront-products-grid');
                var empty = shop.querySelector('[data-storefront-search-empty]');
                var loadMore = shop.querySelector('[data-market-load-more]');
                var sortSelect = shop.querySelector('[data-market-sort]');
                var dialog = shop.querySelector('[data-market-filter-dialog]');
                var filterForm = shop.querySelector('[data-market-filter-form]');

                function marketInputs() { return Array.from(storefront.querySelectorAll('[data-storefront-search]')); }
                function queryValue() { return String(marketInputs()[0]?.value || '').trim().toLowerCase(); }
                function applyMarketFilters() {
                    var cards = Array.from(grid?.querySelectorAll('[data-product-search]') || []);
                    var query = queryValue();
                    var matches = cards.filter(function (card) {
                        var price = Number(card.dataset.productPriceValue || 0);
                        var status = card.dataset.productStatusKey || '';
                        var isAvailable = ['in_stock', 'low_stock', 'pre_order'].includes(status);
                        return (!query || card.dataset.productSearch.includes(query))
                            && (category === 'all' || card.dataset.productCategory === category)
                            && (availability === 'all' || (availability === 'available' ? isAvailable : status === 'sold_out'))
                            && (minPrice === null || price >= minPrice)
                            && (maxPrice === null || price <= maxPrice);
                    });
                    matches.sort(function (left, right) {
                        if (sort === 'price_asc') return Number(left.dataset.productPriceValue) - Number(right.dataset.productPriceValue);
                        if (sort === 'price_desc') return Number(right.dataset.productPriceValue) - Number(left.dataset.productPriceValue);
                        if (sort === 'name') return left.dataset.productName.localeCompare(right.dataset.productName);
                        return Number(left.dataset.productOriginalOrder) - Number(right.dataset.productOriginalOrder);
                    });
                    matches.forEach(function (card) { grid.appendChild(card); });
                    cards.forEach(function (card) { card.hidden = true; });
                    matches.slice(0, visibleLimit).forEach(function (card) { card.hidden = false; });
                    if (empty) empty.hidden = matches.length !== 0;
                    if (loadMore) loadMore.hidden = matches.length <= visibleLimit;
                }

                marketInputs().forEach(function (input) {
                    input.dataset.marketSearch = '1';
                    input.addEventListener('input', function () {
                        marketInputs().forEach(function (other) { if (other !== input) other.value = input.value; });
                        visibleLimit = 6;
                        applyMarketFilters();
                    });
                });
                shop.querySelectorAll('[data-market-category]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        category = button.dataset.marketCategory || 'all';
                        visibleLimit = 6;
                        shop.querySelectorAll('[data-market-category]').forEach(function (item) { item.classList.toggle('is-active', item === button); });
                        var radio = Array.from(filterForm?.querySelectorAll('[name="market_category"]') || [])
                            .find(function (item) { return item.value === category; });
                        if (radio) radio.checked = true;
                        applyMarketFilters();
                    });
                });
                sortSelect?.addEventListener('change', function () { sort = sortSelect.value; applyMarketFilters(); });
                loadMore?.addEventListener('click', function () { visibleLimit += 4; applyMarketFilters(); });
                shop.querySelector('[data-market-filter-open]')?.addEventListener('click', function () { dialog?.showModal(); });
                dialog?.querySelector('[data-market-filter-close]')?.addEventListener('click', function () { dialog.close(); });
                dialog?.addEventListener('click', function (event) { if (event.target === dialog) dialog.close(); });
                filterForm?.addEventListener('submit', function (event) {
                    event.preventDefault();
                    category = String(new FormData(filterForm).get('market_category') || 'all');
                    var rawMin = filterForm.elements.market_min_price.value;
                    var rawMax = filterForm.elements.market_max_price.value;
                    minPrice = rawMin === '' ? null : Number(rawMin);
                    maxPrice = rawMax === '' ? null : Number(rawMax);
                    availability = String(new FormData(filterForm).get('market_availability') || 'all');
                    sort = filterForm.elements.market_dialog_sort.value;
                    if (sortSelect) sortSelect.value = sort;
                    visibleLimit = 6;
                    shop.querySelectorAll('[data-market-category]').forEach(function (item) { item.classList.toggle('is-active', item.dataset.marketCategory === category); });
                    dialog.close();
                    applyMarketFilters();
                });
                filterForm?.querySelector('[data-market-filter-reset]')?.addEventListener('click', function () {
                    filterForm.reset();
                    category = 'all'; minPrice = null; maxPrice = null; availability = 'all'; sort = 'recommended'; visibleLimit = 6;
                    if (sortSelect) sortSelect.value = sort;
                    shop.querySelectorAll('[data-market-category]').forEach(function (item) { item.classList.toggle('is-active', item.dataset.marketCategory === 'all'); });
                    applyMarketFilters();
                });
                applyMarketFilters();
            }

            function bindFashion() {
                var storefront = shop?.closest('.storefront-theme-fashion');
                if (!storefront || shop.dataset.fashionBound === '1') return;
                shop.dataset.fashionBound = '1';

                var storageKey = '8kommerce:fashion-wishlist:' + window.location.pathname;
                var dialog = shop.querySelector('[data-fashion-wishlist-dialog]');
                var saved = [];
                try { saved = JSON.parse(window.localStorage.getItem(storageKey) || '[]'); } catch (error) { saved = []; }
                if (!Array.isArray(saved)) saved = [];

                function persist() {
                    try { window.localStorage.setItem(storageKey, JSON.stringify(saved)); } catch (error) {}
                }
                function renderWishlist() {
                    storefront.querySelectorAll('[data-fashion-wishlist-count]').forEach(function (count) {
                        count.textContent = String(saved.length);
                        count.hidden = saved.length === 0;
                    });
                    storefront.querySelectorAll('[data-fashion-wishlist]').forEach(function (button) {
                        var active = saved.some(function (item) { return item.id === button.dataset.productId; });
                        button.classList.toggle('is-active', active);
                        button.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });
                    var items = dialog?.querySelector('[data-fashion-wishlist-items]');
                    var empty = dialog?.querySelector('[data-fashion-wishlist-empty]');
                    if (!items || !empty) return;
                    items.replaceChildren();
                    empty.hidden = saved.length > 0;
                    saved.forEach(function (item) {
                        var row = document.createElement('div');
                        row.className = 'storefront-fashion-wishlist-row';
                        var name = document.createElement('strong');
                        name.textContent = item.name;
                        var remove = document.createElement('button');
                        remove.type = 'button';
                        remove.textContent = 'Remove';
                        remove.addEventListener('click', function () {
                            saved = saved.filter(function (savedItem) { return savedItem.id !== item.id; });
                            persist();
                            renderWishlist();
                        });
                        row.append(name, remove);
                        items.appendChild(row);
                    });
                }

                storefront.querySelectorAll('[data-fashion-wishlist]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var id = button.dataset.productId;
                        var index = saved.findIndex(function (item) { return item.id === id; });
                        if (index >= 0) saved.splice(index, 1);
                        else saved.push({ id: id, name: button.dataset.productName || 'Saved item' });
                        persist();
                        renderWishlist();
                    });
                });
                storefront.querySelectorAll('[data-fashion-wishlist-open]').forEach(function (button) {
                    if (button.dataset.fashionWishlistBound === '1') return;
                    button.dataset.fashionWishlistBound = '1';
                    button.addEventListener('click', function () { dialog?.showModal(); });
                });
                dialog?.querySelectorAll('[data-fashion-wishlist-close]').forEach(function (button) {
                    button.addEventListener('click', function () { dialog.close(); });
                });
                dialog?.addEventListener('click', function (event) { if (event.target === dialog) dialog.close(); });
                storefront.querySelectorAll('.storefront-fashion-categories a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        var category = link.dataset.fashionCategory || 'all';
                        storefront.querySelectorAll('.storefront-fashion-categories a').forEach(function (item) { item.classList.toggle('is-active', item === link); });
                        var visible = 0;
                        shop.querySelectorAll('[data-product-search]').forEach(function (card) {
                            var matches = category === 'all' || card.dataset.productCategory === category;
                            card.hidden = !matches;
                            if (matches) visible += 1;
                        });
                        var empty = shop.querySelector('[data-storefront-search-empty]');
                        if (empty) empty.hidden = visible !== 0;
                    });
                });
                renderWishlist();
            }

            function bindInteractions() {
                bindFilterTabs();
                bindAddToCartForms();
                bindCartForm();
                bindPerfumeJourney();
                bindMarketplace();
                bindFashion();
            }

            function bindFloatingToggle() {
                if (!toggle || toggle.dataset.boundScroll === '1') {
                    return;
                }

                toggle.dataset.boundScroll = '1';
                toggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    var target = atCart ? shop : cart;
                    if (!target) {
                        return;
                    }
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    setTimeout(syncMode, 300);
                });
            }

            window.addEventListener('scroll', syncMode, { passive: true });
            window.addEventListener('resize', syncMode);
            window.addEventListener('popstate', function () {
                asyncRequest(window.location.href, { method: 'GET' }, true, null, { action: 'history_navigation_refresh' });
            });

            bindInteractions();
            bindFloatingToggle();
            syncMode();
        });
    </script>
@endif

@if($storefrontSettings['show_business_info'] || ($isPreview ?? false))
    <section class="storefront-business-info" data-storefront-business @if(! $storefrontSettings['show_business_info']) hidden @endif>
        <div>
            <strong>{{ $profile->business_name }}</strong>
            <span>Independent business on 8Kommerce</span>
        </div>
        <dl>
            <div data-storefront-address @if(! $storefrontSettings['business_address']) hidden @endif><dt>Location</dt><dd>{{ $storefrontSettings['business_address'] }}</dd></div>
            <div data-storefront-hours @if(! $storefrontSettings['business_hours']) hidden @endif><dt>Hours</dt><dd>{{ $storefrontSettings['business_hours'] }}</dd></div>
            @if($profile->phone)<div><dt>Contact</dt><dd>{{ $profile->phone }}</dd></div>@endif
        </dl>
    </section>
@endif

@if(($storefrontSettings['show_social_links'] && collect([$storefrontSettings['instagram_url'], $storefrontSettings['facebook_url'], $storefrontSettings['tiktok_url']])->filter()->isNotEmpty()) || ($isPreview ?? false))
    <footer class="storefront-social-footer" data-storefront-social @if(! $storefrontSettings['show_social_links'] || collect([$storefrontSettings['instagram_url'], $storefrontSettings['facebook_url'], $storefrontSettings['tiktok_url']])->filter()->isEmpty()) hidden @endif>
        <span>Follow {{ $profile->business_name }}</span>
        <nav aria-label="Social media">
            @if($storefrontSettings['instagram_url'])<a href="{{ $storefrontSettings['instagram_url'] }}" target="_blank" rel="noopener noreferrer">Instagram</a>@endif
            @if($storefrontSettings['facebook_url'])<a href="{{ $storefrontSettings['facebook_url'] }}" target="_blank" rel="noopener noreferrer">Facebook</a>@endif
            @if($storefrontSettings['tiktok_url'])<a href="{{ $storefrontSettings['tiktok_url'] }}" target="_blank" rel="noopener noreferrer">TikTok</a>@endif
        </nav>
    </footer>
@endif

@if($activeStorefrontTheme === 'fashion')
    <section class="storefront-fashion-care">
        <h2>{{ $storefrontSettings['tagline'] ?: 'Wear often. Keep longer.' }}</h2>
        <p>{{ $storefrontSettings['description'] ?: 'Considered materials, thoughtful fits and details you will notice every day.' }}</p>
        <strong>{{ $storefrontSettings['delivery_message'] ?: 'Reliable delivery' }} · {{ $storefrontSettings['returns_message'] ?: 'Clear returns' }}</strong>
        <span>Need a hand? Contact the store or track your order at any time.</span>
    </section>
    <footer class="storefront-fashion-footer">
        <strong>{{ $profile->business_name }}</strong>
        <p>{{ $storefrontSettings['description'] ?: 'Considered pieces. Everyday expression.' }}</p>
        <div><span>© {{ date('Y') }} {{ $profile->business_name }}</span><a href="{{ route('customer.orders.login') }}">Track your order</a><span>Powered by 8Kommerce</span></div>
    </footer>
    <nav class="storefront-mobile-nav storefront-fashion-mobile-nav" aria-label="Shop navigation">
        <a href="#storefront-top" aria-current="page"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 10 9-7 9 7v10H6v-10"></path><path d="M9 20v-6h6v6"></path></svg><span>Home</span></a>
        <a href="#shop-section"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="4" width="6" height="6" rx="1"></rect><rect x="14" y="4" width="6" height="6" rx="1"></rect><rect x="4" y="14" width="6" height="6" rx="1"></rect><rect x="14" y="14" width="6" height="6" rx="1"></rect></svg><span>Shop</span></a>
        <button type="button" data-fashion-wishlist-open><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"></path></svg><span>Wishlist</span></button>
        <a href="#cart-section" class="storefront-mobile-cart-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8Z"></path><path d="M9 10V7a3 3 0 0 1 6 0v3"></path></svg><b data-cart-count @if(($cart['count'] ?? 0) < 1) hidden @endif>{{ $cart['count'] ?? 0 }}</b><span>Bag</span></a>
        <a href="{{ route('customer.orders.login') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m4 7 8-4 8 4-8 4-8-4Z"></path><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"></path><path d="M12 11v10"></path></svg><span>Track</span></a>
    </nav>
@elseif($activeStorefrontTheme === 'perfume')
    <footer class="storefront-perfume-footer">
        <div class="storefront-perfume-footer-brand">
            <strong>{{ $profile->business_name }}</strong>
            <p>{{ $storefrontSettings['description'] ?: 'Thoughtfully selected products, made to stay with you.' }}</p>
        </div>
        <div class="storefront-perfume-footer-links">
            <div>
                <strong>Here to help</strong>
                @if($storefrontSettings['returns_message'])<span>{{ $storefrontSettings['returns_message'] }}</span>@endif
                @if($profile->phone)<a href="tel:{{ $profile->phone }}">Contact the store</a>@endif
            </div>
            <div>
                <strong>Your order</strong>
                <a href="{{ route('customer.orders.login') }}">Track your order</a>
                <a href="#cart-section">Secure checkout</a>
            </div>
        </div>
        <div class="storefront-perfume-footer-meta">
            <span>© {{ date('Y') }} {{ $profile->business_name }}</span>
            <span>Powered by 8Kommerce</span>
        </div>
    </footer>

    <nav class="storefront-mobile-nav" aria-label="Shop navigation">
        <a href="#storefront-top" aria-current="page">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 10 9-7 9 7v10H6v-10"></path><path d="M9 20v-6h6v6"></path></svg>
            <span>Home</span>
        </a>
        <a href="#shop-section">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="4" width="6" height="6" rx="1"></rect><rect x="14" y="4" width="6" height="6" rx="1"></rect><rect x="4" y="14" width="6" height="6" rx="1"></rect><rect x="14" y="14" width="6" height="6" rx="1"></rect></svg>
            <span>Browse</span>
        </a>
        <a href="#storefront-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
            <span>Search</span>
        </a>
        <a href="#cart-section" class="storefront-mobile-cart-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8Z"></path><path d="M9 10V7a3 3 0 0 1 6 0v3"></path></svg>
            <b data-cart-count @if(($cart['count'] ?? 0) < 1) hidden @endif>{{ $cart['count'] ?? 0 }}</b>
            <span>Cart</span>
        </a>
        <a href="{{ route('customer.orders.login') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m4 7 8-4 8 4-8 4-8-4Z"></path><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"></path><path d="M12 11v10"></path></svg>
            <span>Track</span>
        </a>
    </nav>
@elseif($activeStorefrontTheme === 'market')
    <section class="storefront-market-confidence" aria-label="Shopping benefits">
        <div><strong>Delivery that works for you</strong><span>{{ $storefrontSettings['delivery_message'] ?: 'Track every order from checkout to arrival.' }}</span></div>
        <div><strong>Shop with confidence</strong><span>{{ $storefrontSettings['returns_message'] ?: 'Clear product availability and direct seller support.' }}</span></div>
        <div><strong>Safe from cart to doorstep</strong><span>Secure payments through 8Kommerce checkout.</span></div>
    </section>
    <footer class="storefront-market-footer">
        <strong>{{ $profile->business_name }}</strong>
        <p>{{ $storefrontSettings['description'] ?: 'Good finds for everyday living.' }}</p>
        <div><span>© {{ date('Y') }} {{ $profile->business_name }}</span><span>Powered by 8Kommerce</span></div>
    </footer>
    <nav class="storefront-mobile-nav storefront-market-mobile-nav" aria-label="Shop navigation">
        <a href="#storefront-top" aria-current="page"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 10 9-7 9 7v10H6v-10"></path><path d="M9 20v-6h6v6"></path></svg><span>Home</span></a>
        <a href="#market-categories"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="4" width="6" height="6" rx="1"></rect><rect x="14" y="4" width="6" height="6" rx="1"></rect><rect x="4" y="14" width="6" height="6" rx="1"></rect><rect x="14" y="14" width="6" height="6" rx="1"></rect></svg><span>Browse</span></a>
        <a href="#marketplace-search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg><span>Search</span></a>
        <a href="#cart-section" class="storefront-mobile-cart-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8Z"></path><path d="M9 10V7a3 3 0 0 1 6 0v3"></path></svg><b data-cart-count @if(($cart['count'] ?? 0) < 1) hidden @endif>{{ $cart['count'] ?? 0 }}</b><span>Cart</span></a>
        <a href="{{ route('customer.orders.login') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m4 7 8-4 8 4-8 4-8-4Z"></path><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"></path><path d="M12 11v10"></path></svg><span>Track</span></a>
    </nav>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.storefront-theme-perfume a[href="#storefront-search"], .storefront-theme-fashion a[href="#storefront-search"]').forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                var search = trigger.closest('.storefront').querySelector('#storefront-search');
                if (!search) return;
                event.preventDefault();
                search.classList.add('is-open');
                search.scrollIntoView({ behavior: 'smooth', block: 'center' });
                window.setTimeout(function () { search.querySelector('input')?.focus(); }, 350);
            });
        });
        document.querySelectorAll('[data-storefront-search]').forEach(function (input) {
            if (input.dataset.searchBound === '1') return;
            input.dataset.searchBound = '1';
            input.addEventListener('input', function () {
                var storefront = input.closest('.storefront');
                if (input.dataset.marketSearch === '1' || storefront.classList.contains('storefront-theme-market')) return;
                var query = input.value.trim().toLowerCase();
                var visible = 0;
                storefront.querySelectorAll('[data-product-search]').forEach(function (card) {
                    var matches = !query || card.dataset.productSearch.includes(query);
                    card.hidden = !matches;
                    if (matches) visible += 1;
                });
                var empty = storefront.querySelector('[data-storefront-search-empty]');
                if (empty) empty.hidden = visible !== 0;
            });
        });
    });
</script>
</div>
