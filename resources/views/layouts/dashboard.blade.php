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

        <title>{{ config('app.name', '8Kommerce') }}</title>
        <script>
            (function () {
                try {
                    var saved = localStorage.getItem('lp_theme');
                    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    var dark = saved ? saved === 'dark' : prefersDark;
                    document.documentElement.classList.toggle('theme-dark', dark);
                } catch (_) {}
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:600,700,800&family=public-sans:400,500,600,700&display=swap" rel="stylesheet" />
        <style>
            body, aside, header, main, nav, section, div, a, button, input, textarea, select {
                transition: background-color .2s ease, color .2s ease, border-color .2s ease;
            }
            html.theme-dark body {
                background: #031f2a;
                color: #e2e8f0;
            }
            html.theme-dark aside,
            html.theme-dark header,
            html.theme-dark .js-mobile-menu-panel {
                background-color: #0b3543 !important;
            }
            html.theme-dark .bg-white,
            html.theme-dark .bg-white\/70,
            html.theme-dark .bg-white\/90,
            html.theme-dark .bg-slate-50,
            html.theme-dark .bg-slate-50\/60,
            html.theme-dark .bg-slate-50\/70 {
                background-color: #0b3543 !important;
            }
            html.theme-dark .bg-emerald-50,
            html.theme-dark .bg-emerald-50\/60,
            html.theme-dark .bg-emerald-50\/70 {
                background-color: rgba(169, 212, 222, 0.16) !important;
            }
            html.theme-dark .bg-blue-50,
            html.theme-dark .bg-blue-50\/60,
            html.theme-dark .bg-blue-50\/70 {
                background-color: rgba(59, 130, 246, 0.16) !important;
            }
            html.theme-dark .bg-indigo-50,
            html.theme-dark .bg-indigo-50\/60,
            html.theme-dark .bg-indigo-50\/70 {
                background-color: rgba(99, 102, 241, 0.18) !important;
            }
            html.theme-dark .bg-amber-50,
            html.theme-dark .bg-amber-50\/60,
            html.theme-dark .bg-amber-50\/70 {
                background-color: rgba(245, 158, 11, 0.18) !important;
            }
            html.theme-dark .bg-fuchsia-50,
            html.theme-dark .bg-fuchsia-50\/60,
            html.theme-dark .bg-fuchsia-50\/70 {
                background-color: rgba(217, 70, 239, 0.16) !important;
            }
            html.theme-dark .bg-rose-50,
            html.theme-dark .bg-rose-50\/70 { background-color: rgba(244, 63, 94, 0.16) !important; }
            html.theme-dark .bg-cyan-50,
            html.theme-dark .bg-cyan-50\/70 { background-color: rgba(6, 182, 212, 0.15) !important; }
            html.theme-dark .border-slate-200,
            html.theme-dark .border-slate-100,
            html.theme-dark .border-slate-300\/70,
            html.theme-dark .border-slate-700\/70 {
                border-color: #334155 !important;
            }
            html.theme-dark .border-emerald-200,
            html.theme-dark .border-emerald-100 { border-color: rgba(169, 212, 222, 0.45) !important; }
            html.theme-dark .border-blue-200,
            html.theme-dark .border-blue-100 { border-color: rgba(59, 130, 246, 0.45) !important; }
            html.theme-dark .border-indigo-200,
            html.theme-dark .border-indigo-100 { border-color: rgba(99, 102, 241, 0.45) !important; }
            html.theme-dark .border-amber-200,
            html.theme-dark .border-amber-100 { border-color: rgba(245, 158, 11, 0.45) !important; }
            html.theme-dark .border-fuchsia-200,
            html.theme-dark .border-fuchsia-100 { border-color: rgba(217, 70, 239, 0.45) !important; }
            html.theme-dark .text-slate-900 { color: #f8fafc !important; }
            html.theme-dark .text-slate-800 { color: #e2e8f0 !important; }
            html.theme-dark .text-slate-700 { color: #cbd5e1 !important; }
            html.theme-dark .text-slate-600 { color: #94a3b8 !important; }
            html.theme-dark .text-slate-500,
            html.theme-dark .text-slate-400 { color: #94a3b8 !important; }
            html.theme-dark .text-emerald-500,
            html.theme-dark .text-emerald-600,
            html.theme-dark .text-emerald-700,
            html.theme-dark .text-emerald-800 { color: #a9d4de !important; }
            html.theme-dark .text-blue-500,
            html.theme-dark .text-blue-600,
            html.theme-dark .text-blue-700,
            html.theme-dark .text-blue-800 { color: #93c5fd !important; }
            html.theme-dark .text-indigo-500,
            html.theme-dark .text-indigo-600,
            html.theme-dark .text-indigo-700,
            html.theme-dark .text-indigo-800 { color: #a5b4fc !important; }
            html.theme-dark .text-amber-500,
            html.theme-dark .text-amber-600,
            html.theme-dark .text-amber-700,
            html.theme-dark .text-amber-800,
            html.theme-dark .text-amber-900 { color: #fcd34d !important; }
            html.theme-dark .text-fuchsia-500,
            html.theme-dark .text-fuchsia-600,
            html.theme-dark .text-fuchsia-700,
            html.theme-dark .text-fuchsia-800 { color: #f0abfc !important; }
            html.theme-dark .js-mobile-menu-panel a,
            html.theme-dark aside a,
            html.theme-dark aside button {
                color: #cbd5e1 !important;
            }
            html.theme-dark aside .bg-emerald-50,
            html.theme-dark .js-mobile-menu-panel .bg-emerald-50 {
                background-color: rgba(169, 212, 222, 0.18) !important;
            }
            html.theme-dark .hover\:bg-emerald-50:hover { background-color: rgba(169, 212, 222, 0.12) !important; }
            html.theme-dark .shadow-sm,
            html.theme-dark .shadow-2xl,
            html.theme-dark .shadow-lg {
                box-shadow: 0 12px 34px rgba(2, 6, 23, 0.45) !important;
            }
            .desktop-tour-focus {
                position: relative;
                z-index: 73;
                outline: 3px solid #a84c6a;
                outline-offset: 3px;
                border-radius: 10px;
            }

            /* Forms retain the same hierarchy in both themes. */
            input,
            textarea,
            select {
                background-color: #ffffff !important;
                color: #111827 !important;
            }
            input::placeholder,
            textarea::placeholder {
                color: #334155 !important;
                opacity: 1;
            }
            html.theme-dark input,
            html.theme-dark textarea,
            html.theme-dark select {
                background-color: #082d39 !important;
                color: #f8fafc !important;
                border-color: #47616b !important;
            }
            html.theme-dark input::placeholder,
            html.theme-dark textarea::placeholder { color: #94a3b8 !important; }
            @media (min-width: 1024px) {
                html.lp-sidebar-collapsed .js-desktop-sidebar { width: 4.5rem !important; }
                html.lp-sidebar-collapsed .js-dashboard-shell { padding-left: 4.5rem !important; }
                html.lp-sidebar-collapsed .js-dashboard-header { left: 4.5rem !important; }
                html.lp-sidebar-collapsed .js-sidebar-copy,
                html.lp-sidebar-collapsed .js-sidebar-label,
                html.lp-sidebar-collapsed .js-sidebar-group-label,
                html.lp-sidebar-collapsed .js-sidebar-status { display: none !important; }
                html.lp-sidebar-collapsed .js-desktop-sidebar .lp-nav-item { justify-content: center; padding-left: .75rem; padding-right: .75rem; }
                html.lp-sidebar-collapsed .js-desktop-sidebar .lp-nav-item-active { box-shadow: inset 0 -3px 0 var(--lp-brand); }
                html.lp-sidebar-collapsed .js-sidebar-brand { justify-content: center; padding-left: .75rem; padding-right: .75rem; }
                html.lp-sidebar-collapsed .js-sidebar-quick { width: 2.75rem; padding-left: 0; padding-right: 0; }
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="lp-application min-h-screen bg-[#f4f7f7] font-sans text-slate-900 antialiased">
        @php
            $user = auth()->user();
            $profile = $user->sellerProfile;
            $canPromotion = $user->canUsePromotionFeatures();
            $canUsePayments = $user->canUsePaymentsFeature();
            $publicUrl = $profile?->public_slug ? route('public.listing', $profile->public_slug) : null;
            $onboarding = app(\App\Services\OnboardingService::class)->forUser($user);
            $accountLabel = $profile?->business_name ?: $user->name ?: 'Seller';
            $accountPhone = $user->phone ?: 'Phone not set';
            $accountInitials = collect(preg_split('/\s+/', trim($accountLabel)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
            $navClass = function (string|array $routes) {
                return request()->routeIs(...(array) $routes)
                    ? 'lp-nav-item lp-nav-item-active'
                    : 'lp-nav-item';
            };
        @endphp

        <div class="min-h-screen lg:flex">
            <aside class="js-desktop-sidebar hidden transition-[width] lg:fixed lg:left-0 lg:top-0 lg:z-30 lg:flex lg:h-screen lg:w-[232px] lg:flex-col lg:overflow-x-hidden lg:overflow-y-auto lg:border-r lg:border-[#D5E1E4] lg:bg-[#F9FBFB]">
                <div class="js-sidebar-brand flex items-center gap-3 px-5 pb-4 pt-5">
                    <x-brand-mark class="h-10 w-auto" />
                    <div class="js-sidebar-copy min-w-0">
                        <p class="text-sm font-semibold tracking-tight text-slate-950">8Kommerce</p>
                        <p class="truncate text-xs text-slate-500">{{ $profile?->business_name ?? 'Seller workspace' }}</p>
                    </div>
                </div>

                <div class="flex justify-center px-3 pb-4">
                    <button type="button" class="lp-button lp-button-brand js-sidebar-quick js-quick-actions-open js-tour-quick-action w-full" aria-label="Open quick menu" title="Quick menu">
                        <x-ui.icon name="circle-plus" />
                        <span class="js-sidebar-label">Quick menu</span>
                    </button>
                </div>

                <nav class="space-y-4 px-3 pb-5">
                    <div class="space-y-1">
                        <p class="lp-eyebrow js-sidebar-group-label px-3 pb-1">Overview</p>
                        <a href="{{ route('dashboard') }}" class="{{ $navClass('dashboard') }} js-tour-dashboard-nav" title="Home"><x-ui.icon name="dashboard" /><span class="js-sidebar-label">Home</span></a>
                        <a href="{{ route('notifications.index') }}" class="{{ $navClass('notifications.*') }}" title="Activity"><x-ui.icon name="activity" /><span class="js-sidebar-label">Activity</span></a>
                    </div>

                    <div class="space-y-1">
                        <p class="lp-eyebrow js-sidebar-group-label px-3 pb-1">Sell</p>
                        <a href="{{ route('products.index') }}" class="{{ $navClass(['products.index', 'products.create', 'products.edit']) }} js-tour-products-nav" title="Products"><x-ui.icon name="package" /><span class="js-sidebar-label">Products</span></a>
                        <a href="{{ route('products.orders') }}" class="{{ $navClass('products.orders') }}" title="Orders"><x-ui.icon name="shopping-bag" /><span class="js-sidebar-label">Orders</span></a>
                        <a href="{{ route('customers.index') }}" class="{{ $navClass('customers.*') }}" title="Customers"><x-ui.icon name="users" /><span class="js-sidebar-label">Customers</span></a>
                        <a href="{{ route('coupons.index') }}" class="{{ $navClass('coupons.*') }}" title="Coupons"><x-ui.icon name="ticket" /><span class="js-sidebar-label">Coupons</span></a>
                    </div>

                    <div class="space-y-1">
                        <p class="lp-eyebrow js-sidebar-group-label px-3 pb-1">Money</p>
                        @if($canUsePayments)
                            <a href="{{ route('payments.index') }}" class="{{ $navClass('payments.*') }} js-tour-payments-nav" title="Payments"><x-ui.icon name="credit-card" /><span class="js-sidebar-label">Payments</span></a>
                            <a href="{{ route('invoices.index') }}" class="{{ $navClass('invoices.*') }}" title="Invoices"><x-ui.icon name="file-invoice" /><span class="js-sidebar-label">Invoices</span></a>
                        @else
                            <a href="{{ route('billing.upgrade') }}" class="lp-nav-item" title="Payments"><x-ui.icon name="credit-card" /><span class="js-sidebar-label">Payments</span><span class="js-sidebar-label ml-auto text-[10px] font-semibold text-amber-700">Upgrade</span></a>
                            <a href="{{ route('billing.upgrade') }}" class="lp-nav-item" title="Invoices"><x-ui.icon name="file-invoice" /><span class="js-sidebar-label">Invoices</span><span class="js-sidebar-label ml-auto text-[10px] font-semibold text-amber-700">Upgrade</span></a>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <p class="lp-eyebrow js-sidebar-group-label px-3 pb-1">Grow</p>
                        <a href="{{ route('goals.index') }}" class="{{ $navClass('goals.*') }}" title="Goals"><x-ui.icon name="target" /><span class="js-sidebar-label">Goals</span></a>
                        <a href="{{ route('promotions.index') }}" class="{{ $navClass('promotions.*') }}" title="Promote"><x-ui.icon name="speakerphone" /><span class="js-sidebar-label">Promote</span></a>
                    </div>

                    <div class="space-y-1">
                        <p class="lp-eyebrow js-sidebar-group-label px-3 pb-1">Store & account</p>
                        @if($profile)
                            <a href="{{ route('public.preview') }}" class="{{ $navClass('public.preview') }}" title="Storefront"><x-ui.icon name="building-store" /><span class="js-sidebar-label">Storefront</span></a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="{{ $navClass('profile.*') }}" title="Store settings"><x-ui.icon name="settings" /><span class="js-sidebar-label">Store settings</span></a>
                        <a href="{{ route('billing.show') }}" class="{{ $navClass('billing.*') }}" title="Plan and billing"><x-ui.icon name="receipt" /><span class="js-sidebar-label">Plan & billing</span></a>
                        @if(! $onboarding['is_complete'])
                            <a href="{{ route('onboarding.index') }}" class="{{ $navClass('onboarding.*') }}" title="Setup guide"><x-ui.icon name="circle-check" /><span class="js-sidebar-label">Setup guide</span></a>
                        @endif
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="{{ $navClass('admin.*') }}" title="Admin"><x-ui.icon name="shield-lock" /><span class="js-sidebar-label">Admin</span></a>
                        @endif
                    </div>
                </nav>

                <div class="js-sidebar-status mt-auto border-t border-[#e2e6df] px-5 py-4">
                    <p class="lp-eyebrow">Payout status</p>
                    @if($profile?->paystack_subaccount_code)
                        <p class="mt-2 flex items-center gap-2 text-sm font-medium text-emerald-700"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Connected</p>
                    @else
                        <p class="mt-2 flex items-center gap-2 text-sm font-medium text-amber-700"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Needs attention</p>
                    @endif
                </div>
            </aside>

            <div class="js-dashboard-shell flex-1 overflow-x-hidden transition-[padding] lg:pl-[232px]">
                <header class="js-dashboard-header fixed left-0 right-0 top-0 z-40 border-b border-[#D5E1E4] bg-white/95 transition-[left] lg:left-[232px]">
                    <div class="flex items-center justify-between px-4 py-3 sm:px-6 lg:px-7">
                        <div class="flex items-center gap-3">
                            <button type="button" class="js-desktop-sidebar-toggle hidden h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 lg:inline-flex" aria-label="Minimise sidebar" title="Minimise sidebar">
                                <x-ui.icon name="menu" />
                            </button>
                            <div>
                            <p class="lp-eyebrow sm:hidden">8Kommerce</p>
                            <p class="hidden text-xs font-medium text-slate-500 sm:block">Seller workspace</p>
                            <h1 class="font-display text-xl font-semibold tracking-tight text-slate-950">{{ $title ?? 'Overview' }}</h1>
                            </div>
                        </div>
                        <div class="hidden items-center gap-3 sm:flex">
                            <button type="button" class="lp-button lp-button-brand js-quick-actions-open">
                                <x-ui.icon name="circle-plus" size="sm" />
                                Quick menu
                            </button>
                            <a href="{{ route('notifications.index') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50" aria-label="Open activity" title="Activity"><x-ui.icon name="bell" /></a>
                            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 js-theme-toggle" aria-label="Toggle dark mode" title="Toggle theme">
                                <x-ui.icon name="moon" class="js-theme-icon-moon" size="sm" />
                                <x-ui.icon name="sun" class="js-theme-icon-sun hidden" size="sm" />
                            </button>
                            <details class="relative">
                                <summary class="flex min-h-10 cursor-pointer list-none items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-left hover:bg-slate-50">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-md bg-[#EAF1F3] text-xs font-bold text-[#073B4C]">{{ $accountInitials ?: '8K' }}</span>
                                    <span class="hidden max-w-40 lg:block"><span class="block truncate text-xs font-semibold text-slate-800">{{ $accountLabel }}</span><span class="block truncate text-[10px] text-slate-500">{{ $accountPhone }} · {{ $user->planDisplayName() }}</span></span>
                                    <x-ui.icon name="chevron-down" size="xs" />
                                </summary>
                                <div class="absolute right-0 mt-2 w-64 rounded-lg border border-slate-200 bg-white p-2 shadow-xl">
                                    <div class="border-b border-slate-100 px-3 py-2"><p class="truncate text-sm font-semibold text-slate-900">{{ $accountLabel }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $accountPhone }}</p></div>
                                    <a href="{{ route('profile.edit') }}" class="lp-nav-item mt-1"><x-ui.icon name="settings" size="sm" />Store settings</a>
                                    <a href="{{ route('billing.show') }}" class="lp-nav-item"><x-ui.icon name="receipt" size="sm" />Plan & billing</a>
                                    <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-slate-100 pt-1">@csrf<button type="submit" class="lp-nav-item w-full text-red-700 hover:bg-red-50"><x-ui.icon name="logout" size="sm" />Logout</button></form>
                                </div>
                            </details>
                        </div>
                        <div class="flex items-center gap-2 sm:hidden">
                            <button
                                type="button"
                                aria-label="Toggle dark mode"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 js-theme-toggle"
                            >
                                <x-ui.icon name="moon" class="js-theme-icon-moon" />
                                <x-ui.icon name="sun" class="js-theme-icon-sun hidden" />
                            </button>
                            <a
                                href="{{ route('notifications.index') }}"
                                aria-label="Notifications"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700"
                            >
                                <x-ui.icon name="bell" />
                            </a>
                            <button
                                type="button"
                                aria-label="Open menu"
                                class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 js-mobile-menu-open"
                            >
                                <x-ui.icon name="menu" size="sm" /><span class="ml-1">Menu</span>
                            </button>
                        </div>
                    </div>
                </header>

                <main class="mx-auto max-w-[1360px] px-4 py-5 pb-24 pt-20 sm:px-6 sm:pb-8 lg:px-7 lg:pt-24">
                    @php
                        $trialActive = $user->isOnTrial() && $user->trial_ends_at;
                        $daysLeft = $trialActive ? max(0, now()->diffInDays($user->trial_ends_at, false)) : null;
                    @endphp
                    @if(($publicUrl || $trialActive) && !request()->routeIs('public.preview'))
                        <div class="lp-card mb-6 px-5 py-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    @if($trialActive)
                                        <p class="font-semibold text-emerald-800">
                                            Trial active - {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left (ends {{ $user->trial_ends_at->toFormattedDateString() }})
                                        </p>
                                        <a href="{{ route('billing.show') }}" class="mt-2 inline-flex text-sm font-semibold text-emerald-700 hover:text-emerald-600">
                                            Manage plan
                                        </a>
                                    @endif
                                </div>
                                @if($publicUrl)
                                    <div class="ml-auto flex flex-wrap items-center gap-3 sm:justify-end">
                                        <p class="lp-eyebrow">Store live</p>
                                        <a href="{{ $publicUrl }}" target="_blank" rel="noreferrer noopener" class="max-w-64 truncate text-sm font-semibold text-emerald-700 hover:text-emerald-600">View storefront</a>
                                        <button
                                            type="button"
                                            class="lp-button-secondary min-h-9 px-3 py-1.5 text-xs js-copy-public-link js-tour-copy-public-link"
                                            data-copy-value="{{ $publicUrl }}"
                                        >
                                            Copy link
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    {{ $slot ?? '' }}
                    @yield('content')
                </main>
            </div>
        </div>
        <nav class="js-mobile-bottom-nav fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white lg:hidden">
            <div class="grid grid-cols-5 items-center px-1 pb-[max(.35rem,env(safe-area-inset-bottom))] pt-1">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 rounded-md px-1 py-1.5 text-[11px] {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-500' }}">
                    <x-ui.icon name="home" />
                    <span class="font-medium">Home</span>
                </a>
                <a href="{{ $canPromotion ? route('products.orders') : route('billing.upgrade') }}" class="flex flex-col items-center gap-0.5 rounded-md px-1 py-1.5 text-[11px] {{ request()->routeIs('products.orders') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-500' }}">
                    <x-ui.icon name="shopping-bag" />
                    <span class="font-medium">Orders</span>
                </a>
                <button type="button" class="group flex flex-col items-center gap-0.5 rounded-md px-1 py-1.5 text-[11px] text-emerald-700 js-quick-actions-open">
                    <span class="flex h-5 w-5 items-center justify-center rounded bg-emerald-600 text-white transition group-active:scale-95">
                        <x-ui.icon name="plus" size="sm" />
                    </span>
                    <span class="font-semibold">Create</span>
                </button>
                <a href="{{ $canPromotion ? route('products.index') : route('billing.upgrade') }}" class="flex flex-col items-center gap-0.5 rounded-md px-1 py-1.5 text-[11px] {{ request()->routeIs('products.index', 'products.create', 'products.edit') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-500' }}">
                    <x-ui.icon name="package" />
                    <span class="font-medium">Products</span>
                </a>
                <button type="button" class="js-mobile-menu-open flex flex-col items-center gap-0.5 rounded-md px-1 py-1.5 text-[11px] text-slate-500" aria-label="Open more navigation">
                    <x-ui.icon name="menu" />
                    <span class="font-medium">More</span>
                </button>
            </div>
        </nav>
        <div class="pointer-events-none fixed inset-0 z-40 bg-slate-900/40 opacity-0 transition-opacity duration-300 js-mobile-menu-backdrop"></div>
        <aside class="fixed right-0 top-0 z-50 h-full w-[88%] max-w-sm translate-x-full overflow-y-auto border-l border-[#e2e6df] bg-[#fbfcf9] p-5 shadow-2xl transition-transform duration-300 ease-out js-mobile-menu-panel lg:hidden">
            <div class="mb-3 flex items-center justify-between">
                <div><p class="text-sm font-semibold text-slate-900">{{ $accountLabel }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $accountPhone }} · {{ $user->planDisplayName() }}</p></div>
                <div class="flex items-center gap-2">
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 text-slate-600 js-theme-toggle" aria-label="Toggle dark mode">
                        <x-ui.icon name="moon" class="js-theme-icon-moon" size="sm" />
                        <x-ui.icon name="sun" class="js-theme-icon-sun hidden" size="sm" />
                    </button>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 text-slate-600 js-mobile-menu-close" aria-label="Close menu"><x-ui.icon name="x" size="sm" /></button>
                </div>
            </div>
            <button type="button" class="lp-button lp-button-brand js-quick-actions-open mb-5 w-full"><x-ui.icon name="circle-plus" size="sm" />Quick menu</button>
            <nav class="space-y-5 text-sm">
                <div class="space-y-1">
                    <p class="lp-eyebrow px-3">Overview</p>
                    <a href="{{ route('dashboard') }}" class="{{ $navClass('dashboard') }}"><x-ui.icon name="dashboard" />Home</a>
                    <a href="{{ route('notifications.index') }}" class="{{ $navClass('notifications.*') }}"><x-ui.icon name="activity" />Activity</a>
                </div>
                <div class="space-y-1">
                    <p class="lp-eyebrow px-3">Sell</p>
                    <a href="{{ route('products.index') }}" class="{{ $navClass(['products.index', 'products.create', 'products.edit']) }}"><x-ui.icon name="package" />Products</a>
                    <a href="{{ route('products.orders') }}" class="{{ $navClass('products.orders') }}"><x-ui.icon name="shopping-bag" />Orders</a>
                    <a href="{{ route('customers.index') }}" class="{{ $navClass('customers.*') }}"><x-ui.icon name="users" />Customers</a>
                    <a href="{{ route('coupons.index') }}" class="{{ $navClass('coupons.*') }}"><x-ui.icon name="ticket" />Coupons</a>
                </div>
                <div class="space-y-1">
                    <p class="lp-eyebrow px-3">Money</p>
                    @if($canUsePayments)
                        <a href="{{ route('payments.index') }}" class="{{ $navClass('payments.*') }}"><x-ui.icon name="credit-card" />Payments</a>
                        <a href="{{ route('invoices.index') }}" class="{{ $navClass('invoices.*') }}"><x-ui.icon name="file-invoice" />Invoices</a>
                    @else
                        <a href="{{ route('billing.upgrade') }}" class="lp-nav-item">Payments & invoices <span class="ml-auto text-xs text-amber-700">Upgrade</span></a>
                    @endif
                </div>
                <div class="space-y-1">
                    <p class="lp-eyebrow px-3">Grow</p>
                    <a href="{{ route('goals.index') }}" class="{{ $navClass('goals.*') }}"><x-ui.icon name="target" />Goals</a>
                    <a href="{{ route('promotions.index') }}" class="{{ $navClass('promotions.*') }}"><x-ui.icon name="speakerphone" />Promote</a>
                </div>
                <div class="space-y-1">
                    <p class="lp-eyebrow px-3">Store & account</p>
                    @if($profile)<a href="{{ route('public.preview') }}" class="{{ $navClass('public.preview') }}"><x-ui.icon name="building-store" />Storefront</a>@endif
                    <a href="{{ route('profile.edit') }}" class="{{ $navClass('profile.*') }}"><x-ui.icon name="settings" />Store settings</a>
                    <a href="{{ route('billing.show') }}" class="{{ $navClass('billing.*') }}"><x-ui.icon name="receipt" />Plan & billing</a>
                    @if(! $onboarding['is_complete'])<a href="{{ route('onboarding.index') }}" class="{{ $navClass('onboarding.*') }}"><x-ui.icon name="circle-check" />Setup guide</a>@endif
                    @if(auth()->user()->is_admin)<a href="{{ route('admin.dashboard') }}" class="{{ $navClass('admin.*') }}"><x-ui.icon name="shield-lock" />Admin</a>@endif
                </div>
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="mt-3 border-t border-slate-100 pt-3">
                @csrf
                <button type="submit" class="lp-button lp-button-danger w-full"><x-ui.icon name="logout" size="sm" />Logout</button>
            </form>
        </aside>
        <div class="pointer-events-none fixed inset-0 z-50 bg-slate-900/40 opacity-0 transition-opacity duration-300 js-quick-actions-backdrop"></div>
        <section class="fixed inset-x-0 bottom-0 z-50 translate-y-full rounded-t-lg border-t border-slate-200 bg-white p-5 shadow-2xl transition-transform duration-300 ease-out js-quick-actions-panel" aria-label="Quick menu">
            <div class="mx-auto mb-4 h-1.5 w-14 rounded-full bg-slate-200"></div>
            <div class="mb-4 flex items-center justify-between">
                <div><p class="lp-eyebrow">Create and manage</p><h2 class="mt-1 text-lg font-semibold text-slate-900">Quick menu</h2></div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 text-slate-600 js-quick-actions-close" aria-label="Close quick menu"><x-ui.icon name="x" size="sm" /></button>
            </div>
            <div class="mx-auto grid max-w-5xl gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ $canPromotion ? route('products.create') : route('billing.upgrade') }}" class="group flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:border-[#B7CBD0] hover:bg-[#F9FBFB]"><span class="lp-icon-box"><x-ui.icon name="package" /></span><span><strong class="block text-sm text-slate-900">Add product</strong><small class="mt-1 block text-xs text-slate-500">Publish something new</small></span></a>
                <a href="{{ $canPromotion ? route('products.orders') : route('billing.upgrade') }}" class="group flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:border-[#B7CBD0] hover:bg-[#F9FBFB]"><span class="lp-icon-box"><x-ui.icon name="shopping-bag" /></span><span><strong class="block text-sm text-slate-900">Review orders</strong><small class="mt-1 block text-xs text-slate-500">Process customer orders</small></span></a>
                <a href="{{ $canUsePayments ? route('invoices.create') : route('billing.upgrade') }}" class="group flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:border-[#B7CBD0] hover:bg-[#F9FBFB]"><span class="lp-icon-box"><x-ui.icon name="file-invoice" /></span><span><strong class="block text-sm text-slate-900">Create invoice</strong><small class="mt-1 block text-xs text-slate-500">Send a payment link</small></span></a>
                <a href="{{ $canPromotion ? route('coupons.index') : route('billing.upgrade') }}" class="group flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:border-[#B7CBD0] hover:bg-[#F9FBFB]"><span class="lp-icon-box"><x-ui.icon name="ticket" /></span><span><strong class="block text-sm text-slate-900">Create coupon</strong><small class="mt-1 block text-xs text-slate-500">Offer a promotion</small></span></a>
                <a href="{{ $canUsePayments ? route('payments.index') : route('billing.upgrade') }}" class="group flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:border-[#B7CBD0] hover:bg-[#F9FBFB]"><span class="lp-icon-box"><x-ui.icon name="credit-card" /></span><span><strong class="block text-sm text-slate-900">Check payments</strong><small class="mt-1 block text-xs text-slate-500">Review transactions</small></span></a>
                <a href="{{ $canPromotion ? route('products.index', ['stock' => \App\Models\Product::STATUS_LOW_STOCK]) : route('billing.upgrade') }}" class="group flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50/60 p-4 hover:bg-amber-50"><span class="lp-icon-box bg-amber-100 text-amber-800"><x-ui.icon name="warning" /></span><span><strong class="block text-sm text-slate-900">Low stock</strong><small class="mt-1 block text-xs text-slate-500">Restock products</small></span></a>
                <a href="{{ route('notifications.index') }}" class="group flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:border-[#B7CBD0] hover:bg-[#F9FBFB]"><span class="lp-icon-box"><x-ui.icon name="bell" /></span><span><strong class="block text-sm text-slate-900">Activity</strong><small class="mt-1 block text-xs text-slate-500">Orders and updates</small></span></a>
                <button type="button" class="hidden items-start gap-3 rounded-lg border border-slate-200 p-4 text-left hover:border-[#B7CBD0] hover:bg-[#F9FBFB] lg:flex js-start-desktop-tour"><span class="lp-icon-box"><x-ui.icon name="help" /></span><span><strong class="block text-sm text-slate-900">Start tutorial</strong><small class="mt-1 block text-xs text-slate-500">Tour the workspace</small></span></button>
            </div>
        </section>
        @if(! $onboarding['is_complete'] && !request()->routeIs('onboarding.*'))
            <aside class="js-onboarding-popup lp-card fixed bottom-5 right-5 z-40 hidden w-[360px] p-4 shadow-2xl lg:block" data-minimized="{{ data_get($onboarding, 'state.desktop_popup_minimized', data_get($onboarding, 'state.desktop_popup_dismissed')) ? '1' : '0' }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Onboarding</p>
                        <h3 class="mt-1 text-sm font-semibold text-slate-900">{{ $onboarding['completed_count'] }}/{{ $onboarding['total_count'] }} complete</h3>
                    </div>
                    <button type="button" class="js-onboarding-close lp-button lp-button-ghost lp-button-sm">Minimise</button>
                </div>
                <div class="mt-3 h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $onboarding['percent'] }}%"></div>
                </div>
                <div class="mt-3 space-y-2">
                    @foreach($onboarding['steps'] as $step)
                        @php($isDone = (bool) ($step['effective_done'] ?? $step['completed']))
                        @if($isDone)
                            <div class="flex items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50/60 px-3 py-2">
                                <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-600 text-[10px] text-white">
                                    &check;
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-slate-900">{{ $step['title'] }}</p>
                                    <p class="text-[11px] text-slate-500">
                                        {{ $step['description'] }}
                                        @if(!empty($step['skipped']))
                                            <span class="ml-1 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">Skipped</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @else
                            <a href="{{ $step['action_url'] }}" class="group flex items-start gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 hover:border-emerald-200 hover:bg-emerald-50/50">
                                <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-slate-200 text-[10px] text-slate-600 group-hover:bg-emerald-200 group-hover:text-emerald-800">
                                    &bull;
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-slate-900">{{ $step['title'] }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $step['description'] }}</p>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
                @if($onboarding['next_step'])
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ $onboarding['next_step']['action_url'] }}" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-500">
                            {{ $onboarding['next_step']['action_label'] }}
                        </a>
                        @if(($onboarding['next_step']['id'] ?? null) === 'share_store' && !empty($onboarding['next_step']['public_url']))
                            <button type="button" class="js-onboarding-copy rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 hover:border-emerald-200 hover:text-emerald-700" data-copy-value="{{ $onboarding['next_step']['public_url'] }}">
                                Copy link
                            </button>
                        @endif
                        <button type="button" class="js-start-desktop-tour inline-flex items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 hover:border-emerald-200 hover:text-emerald-700">
                            Start tutorial
                        </button>
                    </div>
                @endif
            </aside>
            <button type="button" class="js-onboarding-restore fixed bottom-5 right-5 z-40 hidden items-center gap-2 rounded-lg border border-emerald-200 bg-white px-4 py-3 text-xs font-semibold text-emerald-700 shadow-lg lg:inline-flex">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>Resume setup
            </button>
            <a href="{{ route('onboarding.index') }}" class="fixed bottom-24 right-4 z-30 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700 shadow-lg lg:hidden">
                Resume onboarding
            </a>
        @endif
        <div class="js-desktop-tour hidden">
            <div class="pointer-events-none fixed inset-0 z-[70] bg-slate-900/45 js-desktop-tour-overlay"></div>
            <div class="pointer-events-none fixed z-[71] rounded-xl border-2 border-emerald-300 shadow-[0_0_0_9999px_rgba(2,6,23,0.48)] transition-all duration-200 js-desktop-tour-highlight"></div>
            <section class="fixed z-[72] w-[340px] max-w-[calc(100vw-2rem)] rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl pointer-events-auto js-desktop-tour-card">
                <p class="text-[11px] uppercase tracking-[0.25em] text-slate-400 js-desktop-tour-progress">Tutorial</p>
                <h3 class="mt-2 text-sm font-semibold text-slate-900 js-desktop-tour-title">Welcome</h3>
                <p class="mt-2 text-xs leading-5 text-slate-600 js-desktop-tour-description">Quick walkthrough to learn key actions.</p>
                <div class="mt-4 flex items-center justify-between gap-2">
                    <button type="button" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 js-desktop-tour-prev">Previous</button>
                    <div class="flex items-center gap-2">
                        <button type="button" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 js-desktop-tour-skip">Skip</button>
                        <button type="button" class="rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white js-desktop-tour-next">Next</button>
                    </div>
                </div>
            </section>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const copyButton = document.querySelector('.js-copy-public-link');
                if (copyButton) {
                    const copyValue = copyButton.dataset.copyValue || '';
                    const setLabel = (text) => {
                        copyButton.textContent = text;
                        setTimeout(() => {
                            copyButton.textContent = 'Copy link';
                        }, 1200);
                    };

                    copyButton.addEventListener('click', async () => {
                        if (!copyValue) {
                            return;
                        }

                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            try {
                                await navigator.clipboard.writeText(copyValue);
                                setLabel('Copied');
                                return;
                            } catch (_) {
                                // Fallback below.
                            }
                        }

                        const helper = document.createElement('textarea');
                        helper.value = copyValue;
                        helper.setAttribute('readonly', '');
                        helper.style.position = 'absolute';
                        helper.style.left = '-9999px';
                        document.body.appendChild(helper);
                        helper.select();
                        const ok = document.execCommand('copy');
                        document.body.removeChild(helper);
                        setLabel(ok ? 'Copied' : 'Copy failed');
                    });
                }
                const onboardingPopup = document.querySelector('.js-onboarding-popup');
                const onboardingClose = document.querySelector('.js-onboarding-close');
                const onboardingRestore = document.querySelector('.js-onboarding-restore');
                const onboardingCopy = document.querySelector('.js-onboarding-copy');
                const onboardingState = @json($onboarding['state'] ?? []);
                const startDesktopTourButtons = document.querySelectorAll('.js-start-desktop-tour');

                const postOnboardingState = (payload) => {
                    return fetch('{{ route('onboarding.state') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    }).catch(() => {});
                };

                if (onboardingPopup) {
                    const minimized = onboardingPopup.dataset.minimized === '1';
                    if (!minimized) {
                        onboardingPopup.classList.remove('hidden');
                        onboardingRestore?.classList.add('hidden');
                    } else {
                        onboardingRestore?.classList.remove('hidden');
                    }

                    if (onboardingClose) {
                        onboardingClose.addEventListener('click', () => {
                            onboardingPopup.classList.add('hidden');
                            onboardingRestore?.classList.remove('hidden');
                            postOnboardingState({ desktop_popup_minimized: true });
                        });
                    }

                    onboardingRestore?.addEventListener('click', () => {
                        onboardingPopup.classList.remove('hidden');
                        onboardingRestore.classList.add('hidden');
                        postOnboardingState({ desktop_popup_minimized: false });
                    });

                    if (onboardingCopy) {
                        onboardingCopy.addEventListener('click', async () => {
                            const value = onboardingCopy.dataset.copyValue || '';
                            if (!value) {
                                return;
                            }
                            try {
                                await navigator.clipboard.writeText(value);
                                onboardingCopy.textContent = 'Copied';
                            } catch (_) {
                                onboardingCopy.textContent = 'Copy failed';
                            }
                            setTimeout(() => {
                                onboardingCopy.textContent = 'Copy link';
                            }, 1200);
                        });
                    }
                }

                const desktopTourRoot = document.querySelector('.js-desktop-tour');
                const desktopTourHighlight = document.querySelector('.js-desktop-tour-highlight');
                const desktopTourCard = document.querySelector('.js-desktop-tour-card');
                const desktopTourProgress = document.querySelector('.js-desktop-tour-progress');
                const desktopTourTitle = document.querySelector('.js-desktop-tour-title');
                const desktopTourDescription = document.querySelector('.js-desktop-tour-description');
                const desktopTourPrev = document.querySelector('.js-desktop-tour-prev');
                const desktopTourNext = document.querySelector('.js-desktop-tour-next');
                const desktopTourSkip = document.querySelector('.js-desktop-tour-skip');
                const desktopTourSteps = [
                    {
                        selector: '.js-tour-dashboard-nav',
                        title: 'Dashboard',
                        description: 'Use this to return to your main overview at any time.',
                    },
                    {
                        selector: '.js-tour-products-nav',
                        title: 'Products',
                        description: 'Manage stock, pricing, and sharing actions for your products.',
                    },
                    {
                        selector: '.js-tour-quick-action',
                        title: 'Quick Action',
                        description: 'Open fast actions for orders, invoices, coupons, and stock.',
                    },
                    {
                        selector: '.js-tour-payments-nav',
                        title: 'Payments',
                        description: 'Track payouts and transactions from your payment dashboard.',
                    },
                    {
                        selector: '.js-tour-copy-public-link',
                        title: 'Share Public Link',
                        description: 'Copy your store link and send it to customers instantly.',
                    },
                ].filter((step) => document.querySelector(step.selector));

                let desktopTourIndex = Math.min(
                    Math.max(0, Number(onboardingState.desktop_tour_step || 0)),
                    Math.max(0, desktopTourSteps.length - 1)
                );
                let desktopTourOpen = false;
                let desktopTourFocusedEl = null;

                const setDesktopTourOpen = (open) => {
                    if (!desktopTourRoot) {
                        return;
                    }
                    desktopTourOpen = Boolean(open);
                    desktopTourRoot.classList.toggle('hidden', !desktopTourOpen);
                };

                const positionDesktopTour = () => {
                    if (!desktopTourOpen || !desktopTourSteps[desktopTourIndex] || !desktopTourHighlight || !desktopTourCard) {
                        return;
                    }

                    const target = document.querySelector(desktopTourSteps[desktopTourIndex].selector);
                    if (!target) {
                        return;
                    }
                    if (desktopTourFocusedEl && desktopTourFocusedEl !== target) {
                        desktopTourFocusedEl.classList.remove('desktop-tour-focus');
                    }
                    desktopTourFocusedEl = target;
                    desktopTourFocusedEl.classList.add('desktop-tour-focus');
                    target.scrollIntoView({ behavior: 'auto', block: 'center', inline: 'nearest' });
                    const rect = target.getBoundingClientRect();
                    const pad = 8;
                    const highlightTop = Math.max(8, rect.top - pad);
                    const highlightLeft = Math.max(8, rect.left - pad);
                    const highlightWidth = Math.min(window.innerWidth - 16 - highlightLeft, rect.width + (pad * 2));
                    const highlightHeight = Math.min(window.innerHeight - 16 - highlightTop, rect.height + (pad * 2));

                    desktopTourHighlight.style.top = `${highlightTop}px`;
                    desktopTourHighlight.style.left = `${highlightLeft}px`;
                    desktopTourHighlight.style.width = `${highlightWidth}px`;
                    desktopTourHighlight.style.height = `${highlightHeight}px`;

                    const cardWidth = Math.min(340, window.innerWidth - 24);
                    desktopTourCard.style.width = `${cardWidth}px`;
                    const cardHeight = desktopTourCard.offsetHeight || 180;
                    const preferBelow = highlightTop + highlightHeight + cardHeight + 16 < window.innerHeight;
                    const top = preferBelow
                        ? highlightTop + highlightHeight + 12
                        : Math.max(12, highlightTop - cardHeight - 12);
                    const left = Math.min(
                        Math.max(12, highlightLeft),
                        Math.max(12, window.innerWidth - cardWidth - 12)
                    );
                    desktopTourCard.style.top = `${top}px`;
                    desktopTourCard.style.left = `${left}px`;
                };

                const renderDesktopTour = () => {
                    if (!desktopTourSteps.length || !desktopTourTitle || !desktopTourDescription || !desktopTourProgress) {
                        return;
                    }
                    const step = desktopTourSteps[desktopTourIndex];
                    desktopTourProgress.textContent = `Tutorial ${desktopTourIndex + 1}/${desktopTourSteps.length}`;
                    desktopTourTitle.textContent = step.title;
                    desktopTourDescription.textContent = step.description;
                    if (desktopTourPrev) {
                        desktopTourPrev.disabled = desktopTourIndex === 0;
                        desktopTourPrev.classList.toggle('opacity-40', desktopTourIndex === 0);
                    }
                    if (desktopTourNext) {
                        desktopTourNext.textContent = desktopTourIndex === desktopTourSteps.length - 1 ? 'Finish' : 'Next';
                    }
                    window.requestAnimationFrame(() => {
                        window.requestAnimationFrame(positionDesktopTour);
                    });
                };

                const openDesktopTour = (reset = false) => {
                    if (!desktopTourSteps.length || !desktopTourRoot) {
                        return;
                    }
                    if (reset) {
                        desktopTourIndex = 0;
                    }
                    setDesktopTourOpen(true);
                    postOnboardingState({
                        desktop_tour_dismissed: false,
                        desktop_tour_completed: false,
                        desktop_tour_step: desktopTourIndex,
                    });
                    renderDesktopTour();
                };

                const closeDesktopTour = (dismissed = true) => {
                    setDesktopTourOpen(false);
                    if (desktopTourFocusedEl) {
                        desktopTourFocusedEl.classList.remove('desktop-tour-focus');
                        desktopTourFocusedEl = null;
                    }
                    if (dismissed) {
                        postOnboardingState({ desktop_tour_dismissed: true });
                    }
                };

                const completeDesktopTour = () => {
                    setDesktopTourOpen(false);
                    if (desktopTourFocusedEl) {
                        desktopTourFocusedEl.classList.remove('desktop-tour-focus');
                        desktopTourFocusedEl = null;
                    }
                    postOnboardingState({
                        desktop_tour_completed: true,
                        desktop_tour_dismissed: true,
                        desktop_tour_step: 0,
                    });
                };

                startDesktopTourButtons.forEach((button) => {
                    button.addEventListener('click', () => openDesktopTour(true));
                });

                if (desktopTourPrev) {
                    desktopTourPrev.addEventListener('click', () => {
                        if (desktopTourIndex <= 0) {
                            return;
                        }
                        desktopTourIndex -= 1;
                        postOnboardingState({ desktop_tour_step: desktopTourIndex });
                        renderDesktopTour();
                    });
                }

                if (desktopTourNext) {
                    desktopTourNext.addEventListener('click', () => {
                        if (desktopTourIndex >= desktopTourSteps.length - 1) {
                            completeDesktopTour();
                            return;
                        }
                        desktopTourIndex += 1;
                        postOnboardingState({ desktop_tour_step: desktopTourIndex });
                        renderDesktopTour();
                    });
                }

                if (desktopTourSkip) {
                    desktopTourSkip.addEventListener('click', () => closeDesktopTour(true));
                }

                window.addEventListener('resize', () => {
                    if (desktopTourOpen) {
                        positionDesktopTour();
                    }
                });

                // Tutorials are deliberately opt-in. The setup card may be
                // minimized, but it must never take over the seller workspace.

                const panel = document.querySelector('.js-quick-actions-panel');
                const backdrop = document.querySelector('.js-quick-actions-backdrop');
                const openButtons = document.querySelectorAll('.js-quick-actions-open');
                const closeButtons = document.querySelectorAll('.js-quick-actions-close');
                const themeToggles = document.querySelectorAll('.js-theme-toggle');
                const themeMoonIcons = document.querySelectorAll('.js-theme-icon-moon');
                const themeSunIcons = document.querySelectorAll('.js-theme-icon-sun');
                const mobileMenuPanel = document.querySelector('.js-mobile-menu-panel');
                const mobileMenuBackdrop = document.querySelector('.js-mobile-menu-backdrop');
                const mobileMenuOpenButtons = document.querySelectorAll('.js-mobile-menu-open');
                const mobileMenuCloseButtons = document.querySelectorAll('.js-mobile-menu-close');
                const desktopSidebarToggle = document.querySelector('.js-desktop-sidebar-toggle');
                const root = document.documentElement;
                let quickActionsOpen = false;
                let quickDragStartY = 0;
                let quickDragCurrentY = 0;
                let quickDragging = false;
                try {
                    const initiallyCollapsed = localStorage.getItem('lp_sidebar_collapsed') === '1';
                    root.classList.toggle('lp-sidebar-collapsed', initiallyCollapsed);
                    desktopSidebarToggle?.setAttribute('aria-label', initiallyCollapsed ? 'Expand sidebar' : 'Minimise sidebar');
                    desktopSidebarToggle?.setAttribute('title', initiallyCollapsed ? 'Expand sidebar' : 'Minimise sidebar');
                } catch (_) {}
                desktopSidebarToggle?.addEventListener('click', () => {
                    const collapsed = root.classList.toggle('lp-sidebar-collapsed');
                    desktopSidebarToggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Minimise sidebar');
                    desktopSidebarToggle.setAttribute('title', collapsed ? 'Expand sidebar' : 'Minimise sidebar');
                    try { localStorage.setItem('lp_sidebar_collapsed', collapsed ? '1' : '0'); } catch (_) {}
                });
                const applyTheme = (mode) => {
                    const dark = mode === 'dark';
                    root.classList.toggle('theme-dark', dark);
                    themeMoonIcons.forEach((icon) => icon.classList.toggle('hidden', dark));
                    themeSunIcons.forEach((icon) => icon.classList.toggle('hidden', !dark));
                };
                const detectTheme = () => {
                    try {
                        const saved = localStorage.getItem('lp_theme');
                        if (saved === 'dark' || saved === 'light') {
                            return saved;
                        }
                    } catch (_) {}
                    return root.classList.contains('theme-dark') ? 'dark' : 'light';
                };
                let currentTheme = detectTheme();
                applyTheme(currentTheme);
                themeToggles.forEach((button) => {
                    button.addEventListener('click', () => {
                        currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
                        applyTheme(currentTheme);
                        try {
                            localStorage.setItem('lp_theme', currentTheme);
                        } catch (_) {}
                    });
                });
                const syncBodyLock = () => {
                    const quickOpen = panel && !panel.classList.contains('translate-y-full');
                    const menuOpen = mobileMenuPanel && !mobileMenuPanel.classList.contains('translate-x-full');
                    document.body.classList.toggle('overflow-hidden', Boolean(quickOpen || menuOpen));
                };

                const setQuickActions = (open) => {
                    if (!panel || !backdrop) {
                        return;
                    }

                    quickActionsOpen = open;
                    panel.classList.toggle('translate-y-full', !open);
                    if (!open) {
                        panel.style.transform = '';
                        panel.style.transition = '';
                    }
                    backdrop.classList.toggle('opacity-0', !open);
                    backdrop.classList.toggle('pointer-events-none', !open);
                    syncBodyLock();
                };
                const setMobileMenu = (open) => {
                    if (!mobileMenuPanel || !mobileMenuBackdrop) {
                        return;
                    }

                    mobileMenuPanel.classList.toggle('translate-x-full', !open);
                    mobileMenuBackdrop.classList.toggle('opacity-0', !open);
                    mobileMenuBackdrop.classList.toggle('pointer-events-none', !open);
                    syncBodyLock();
                };

                openButtons.forEach((button) => {
                    button.addEventListener('click', () => setQuickActions(true));
                });
                mobileMenuOpenButtons.forEach((button) => {
                    button.addEventListener('click', () => setMobileMenu(true));
                });

                closeButtons.forEach((button) => {
                    button.addEventListener('click', () => setQuickActions(false));
                });
                mobileMenuCloseButtons.forEach((button) => {
                    button.addEventListener('click', () => setMobileMenu(false));
                });

                if (backdrop) {
                    backdrop.addEventListener('click', () => setQuickActions(false));
                }
                if (mobileMenuBackdrop) {
                    mobileMenuBackdrop.addEventListener('click', () => setMobileMenu(false));
                }

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        setQuickActions(false);
                        setMobileMenu(false);
                        if (desktopTourOpen) {
                            closeDesktopTour(true);
                        }
                    }
                });

                // Mobile touch drag to dismiss quick actions panel.
                if (panel) {
                    panel.addEventListener('touchstart', (event) => {
                        if (!quickActionsOpen || event.touches.length !== 1) {
                            return;
                        }
                        quickDragging = true;
                        quickDragStartY = event.touches[0].clientY;
                        quickDragCurrentY = quickDragStartY;
                        panel.style.transition = 'none';
                    }, { passive: true });

                    panel.addEventListener('touchmove', (event) => {
                        if (!quickDragging || event.touches.length !== 1) {
                            return;
                        }
                        quickDragCurrentY = event.touches[0].clientY;
                        const delta = Math.max(0, quickDragCurrentY - quickDragStartY);
                        panel.style.transform = `translateY(${delta}px)`;
                        if (delta > 0) {
                            event.preventDefault();
                        }
                    }, { passive: false });

                    panel.addEventListener('touchend', () => {
                        if (!quickDragging) {
                            return;
                        }
                        quickDragging = false;
                        const delta = Math.max(0, quickDragCurrentY - quickDragStartY);
                        panel.style.transition = 'transform 220ms ease';
                        if (delta > 90) {
                            setQuickActions(false);
                            return;
                        }
                        panel.style.transform = 'translateY(0)';
                        window.setTimeout(() => {
                            if (quickActionsOpen) {
                                panel.style.transform = '';
                                panel.style.transition = '';
                            }
                        }, 230);
                    });

                    panel.addEventListener('touchcancel', () => {
                        quickDragging = false;
                        if (!quickActionsOpen) {
                            panel.style.transform = '';
                            panel.style.transition = '';
                            return;
                        }
                        panel.style.transition = 'transform 220ms ease';
                        panel.style.transform = 'translateY(0)';
                        window.setTimeout(() => {
                            if (quickActionsOpen) {
                                panel.style.transform = '';
                                panel.style.transition = '';
                            }
                        }, 230);
                    });
                }
            });
        </script>
        @stack('scripts')
        @include('components.page-loader')
    </body>
</html>
