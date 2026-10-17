<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#073B4C">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        @php
            $defaultOgImage = url('/images/og-default.jpg');
            $ogTitle = trim($__env->yieldContent('og_title')) ?: $title ?? config('app.name', '8Kommerce');
            $ogDescription = trim($__env->yieldContent('og_description')) ?: 'Pay by WhatsApp with 8Kommerce';
            $ogImage = trim($__env->yieldContent('og_image')) ?: $defaultOgImage;
            $ogUrl = trim($__env->yieldContent('og_url')) ?: url()->current();
            $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
            $ogWidth = trim($__env->yieldContent('og_image_width')) ?: '1200';
            $ogHeight = trim($__env->yieldContent('og_image_height')) ?: '630';
        @endphp

        <title>{{ $ogTitle }}</title>
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="{{ $ogWidth }}">
        <meta property="og:image:height" content="{{ $ogHeight }}">
        <meta property="og:url" content="{{ $ogUrl }}">
        <meta property="og:type" content="{{ $ogType }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogTitle }}">
        <meta name="twitter:description" content="{{ $ogDescription }}">
        <meta name="twitter:image" content="{{ $ogImage }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:600,700,800&family=public-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="lp-application min-h-screen bg-[#f4f7f7] font-sans text-slate-900 antialiased {{ in_array(($storefrontTheme ?? null), ['perfume', 'market', 'fashion'], true) ? 'public-layout-storefront-immersive' : '' }}">
        @if(! in_array(($storefrontTheme ?? null), ['perfume', 'market', 'fashion'], true))
        <header class="sticky top-0 z-40 border-b border-[#D5E1E4] bg-white/95">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-3 sm:px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <x-brand-mark class="h-9 w-auto" />
                <div>
                    <p class="text-sm font-semibold leading-tight text-slate-950">8Kommerce</p>
                    <p class="text-xs text-slate-500">Secure social commerce</p>
                </div>
            </a>
            <div class="flex items-center gap-1 text-sm font-medium text-slate-600 sm:gap-2">
                <a href="{{ route('customer.orders.login') }}" class="min-h-10 rounded-xl px-3 py-2.5 hover:bg-white hover:text-slate-950">Track orders</a>
                <a href="{{ route('discover.index') }}" class="hidden min-h-10 rounded-xl px-3 py-2.5 hover:bg-white hover:text-slate-950 sm:inline-flex">Discover</a>
                <a href="{{ route('pricing') }}" class="hidden min-h-10 rounded-xl px-3 py-2.5 hover:bg-white hover:text-slate-950 sm:inline-flex">Pricing</a>
                <a href="{{ route('legal.privacy') }}" class="hidden min-h-10 rounded-xl px-3 py-2.5 hover:bg-white hover:text-slate-950 md:inline-flex">Privacy</a>
                <a href="{{ route('login') }}" class="inline-flex min-h-10 items-center rounded-xl border border-[#d8ddd6] bg-white px-3 py-2 text-sm font-semibold text-slate-800 hover:border-slate-400">Seller login</a>
            </div>
            </div>
        </header>
        @endif

        <main class="{{ in_array(($storefrontTheme ?? null), ['perfume', 'market', 'fashion'], true) ? 'storefront-immersive-main' : 'mx-auto w-full max-w-7xl px-4 pb-20 pt-5 sm:px-6 sm:pt-8' }}">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        @if(! in_array(($storefrontTheme ?? null), ['perfume', 'market', 'fashion'], true))
        <footer class="mx-auto mb-8 flex w-full max-w-7xl items-center justify-between border-t border-[#e2e6df] px-4 pt-6 text-xs text-slate-500 sm:px-6">
            <p>© {{ date('Y') }} 8Kommerce</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('legal.privacy') }}" class="hover:text-emerald-700">Privacy</a>
                <a href="{{ route('legal.terms') }}" class="hover:text-emerald-700">Terms</a>
            </div>
        </footer>
        @endif
        @include('components.page-loader')
    </body>
    
</html>
