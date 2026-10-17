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
        <link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:600,700,800&family=public-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="lp-application min-h-screen bg-[#f4f7f7] font-sans text-slate-900 antialiased">
        @if(request()->routeIs('login', 'register'))
            <main class="auth-login-shell">
                <section class="auth-login-form-panel">{{ $slot }}</section>

                <aside class="auth-login-showcase" aria-label="8Kommerce business workspace preview">
                    <div class="auth-showcase-label"><span class="auth-showcase-dot"></span>One workspace for everyday commerce</div>

                    <div class="auth-showcase-stage" aria-hidden="true">
                        <div class="auth-preview-card auth-preview-orders">
                            <div class="flex items-start justify-between gap-4">
                                <div><p class="auth-preview-kicker">Today’s orders</p><p class="mt-1 text-2xl font-semibold tracking-tight text-slate-950">18</p></div>
                                <span class="auth-preview-badge">Live</span>
                            </div>
                            <div class="mt-5 space-y-3">
                                <div class="auth-preview-row"><span>Wireless speaker</span><strong>Paid</strong></div>
                                <div class="auth-preview-row"><span>Market tote</span><strong>Ready</strong></div>
                                <div class="auth-preview-row"><span>Skin care set</span><strong>Processing</strong></div>
                            </div>
                        </div>

                        <div class="auth-preview-card auth-preview-stock">
                            <div class="flex items-center justify-between"><p class="auth-preview-kicker">Stock overview</p><span class="text-[10px] font-semibold text-slate-400">Updated now</span></div>
                            <div class="mt-4 grid grid-cols-3 gap-3">
                                <div><strong>124</strong><span>In stock</span></div>
                                <div><strong>8</strong><span>Low stock</span></div>
                                <div><strong>3</strong><span>Sold out</span></div>
                            </div>
                            <div class="auth-preview-meter mt-5"><span></span></div>
                        </div>

                        <div class="auth-preview-card auth-preview-payment">
                            <div class="flex items-center gap-3">
                                <span class="auth-preview-check"><svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 10 3 3 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                <div><p class="auth-preview-kicker">Payment received</p><p class="mt-0.5 text-base font-semibold text-slate-950">GHS 480.00</p></div>
                            </div>
                            <div class="mt-4 border-t border-slate-200 pt-3 text-xs text-slate-500">Order #8K-2481 · Mobile money</div>
                        </div>
                    </div>

                    <div class="auth-showcase-copy">
                        <x-brand-mark :reversed="true" class="auth-showcase-mark h-12 w-auto" />
                        <h2>Know what’s happening across your business.</h2>
                        <p>Products, orders, stock and payments—clear, connected and ready when you are.</p>
                    </div>

                    <div class="auth-showcase-steps" aria-hidden="true"><span class="is-active"></span><span></span><span></span><span></span></div>
                </aside>
            </main>
        @else
        <div class="min-h-screen">
            <div class="flex min-h-screen items-center justify-center px-4 py-8">
                <div class="w-full max-w-4xl overflow-hidden rounded-lg border border-slate-200 bg-white">
                    <div class="grid lg:grid-cols-5">
                        <div class="hidden flex-col justify-between bg-[#073B4C] px-9 py-10 text-white lg:col-span-2">
                            <div>
                                <a href="/" class="inline-flex items-center gap-3 text-white">
                                    <x-brand-logo :reversed="true" class="h-9 text-sm" />
                                </a>
                                <h2 class="mt-10 text-2xl font-semibold leading-tight">Run your business from one reliable workspace.</h2>
                                <p class="mt-4 text-sm text-emerald-50/90">
                                    Create mini listings, share invoices, and track payments in one clean workspace.
                                </p>
                            </div>
                            <div class="space-y-3 text-sm text-emerald-50/90">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-white"></span>
                                    <span>Instant Paystack checkout</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-white"></span>
                                    <span>Smart invoice tracking</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-white"></span>
                                    <span>Mobile-first dashboards</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-8 sm:p-10 lg:col-span-3">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @include('components.page-loader')
    </body>
</html>
