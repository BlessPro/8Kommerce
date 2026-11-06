@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header
        title="Seller referrals"
        description="Invite another business owner to start selling with 8Kommerce and follow their progress."
        eyebrow="Grow together"
    />

    @if(session('status'))
        <x-ui.alert tone="info" title="Seller invitation" class="mt-6">{{ session('status') }}</x-ui.alert>
    @endif

    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Referral overview">
        <x-ui.stat-card label="Link opens" :value="number_format($stats['visits'])" meta="Unique browser sessions" icon="eye" />
        <x-ui.stat-card label="Sellers joined" :value="number_format($stats['joined'])" meta="Verified new accounts" icon="user-plus" />
        <x-ui.stat-card label="Shops set up" :value="number_format($stats['setup'])" meta="Completed seller profiles" icon="building-store" />
        <x-ui.stat-card label="Subscribed" :value="number_format($stats['subscribed'])" meta="Active paid sellers" icon="circle-check" />
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,.65fr)]">
        <section class="lp-card overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
                <p class="lp-eyebrow">Your private invite link</p>
                <h2 class="lp-section-title mt-1">Bring another seller onto 8Kommerce</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Anyone who creates a new seller account through this link will appear in your referral activity. Existing accounts cannot be reassigned.</p>
            </div>

            <div class="p-5 sm:p-6">
                <label for="seller-referral-link" class="lp-form-label">Seller invite link</label>
                <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                    <input id="seller-referral-link" value="{{ $inviteUrl }}" readonly class="lp-form-control min-w-0 flex-1 bg-slate-50 font-mono text-xs" />
                    <x-ui.button type="button" variant="secondary" icon="copy" id="seller-referral-copy">Copy link</x-ui.button>
                    <x-ui.button
                        :href="'https://api.whatsapp.com/send?text='.rawurlencode('I use 8Kommerce to manage my shop and storefront. Create your seller account here: '.$inviteUrl)"
                        icon="whatsapp"
                        target="_blank"
                        rel="noreferrer noopener"
                    >Share</x-ui.button>
                </div>
                <p id="seller-referral-copy-status" class="mt-2 min-h-5 text-xs font-medium text-emerald-700" aria-live="polite"></p>
            </div>
        </section>

        <aside class="lp-card overflow-hidden">
            <div class="bg-[#073B4C] px-5 py-5 text-white sm:px-6">
                <span class="inline-flex rounded-md bg-white/10 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[.14em] text-[#A9D4DE]">Coming soon</span>
                <h2 class="mt-4 text-xl font-semibold">Referral rewards</h2>
                <p class="mt-2 text-sm leading-6 text-white/70">We are designing a fair reward programme for sellers who help the community grow.</p>
            </div>
            <div class="space-y-3 px-5 py-5 text-sm text-slate-600 sm:px-6">
                <p class="flex gap-3"><x-ui.icon name="check" size="sm" class="mt-0.5 text-emerald-700" /><span>Your referrals are already being recorded.</span></p>
                <p class="flex gap-3"><x-ui.icon name="check" size="sm" class="mt-0.5 text-emerald-700" /><span>Joined, setup and subscription progress remains visible.</span></p>
                <p class="flex gap-3"><x-ui.icon name="info-circle" size="sm" class="mt-0.5 text-slate-500" /><span>No cash, credit or subscription reward is currently promised or issued.</span></p>
            </div>
        </aside>
    </div>

    <section class="lp-card mt-6 overflow-hidden">
        <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-end sm:justify-between sm:px-6">
            <div>
                <p class="lp-eyebrow">Referral activity</p>
                <h2 class="lp-section-title mt-1">Sellers who joined through you</h2>
            </div>
            <p class="text-xs text-slate-500">Only progress needed to understand the referral is shown.</p>
        </div>

        @if($rows->isEmpty())
            <x-ui.empty-state
                title="No sellers have joined yet"
                description="Share your invite link with a business owner. Their progress will appear here after phone verification."
                icon="user-plus"
                class="border-0 py-12"
            />
        @else
            <div class="divide-y divide-slate-200">
                @foreach($rows as $row)
                    @php
                        $stage = match($row['stage']) {
                            'subscribed' => ['Subscribed', 'success', 'Active paid plan'],
                            'setup' => ['Shop set up', 'info', 'Seller profile completed'],
                            default => ['Joined', 'neutral', 'Phone number verified'],
                        };
                    @endphp
                    <article class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center sm:px-6">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $row['label'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">Joined {{ $row['registered_at']?->diffForHumans() }}</p>
                        </div>
                        <p class="text-xs text-slate-500">{{ $stage[2] }}</p>
                        <x-ui.badge :tone="$stage[1]">{{ $stage[0] }}</x-ui.badge>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-3">
        @foreach([
            ['01', 'Share privately', 'Send your unique link to a business owner who does not already have an 8Kommerce account.'],
            ['02', 'They verify their phone', 'The referral is recorded only after a new seller completes phone verification.'],
            ['03', 'Follow their progress', 'See when they join, finish setting up their shop, and activate a paid plan.'],
        ] as $step)
            <article class="lp-card p-5">
                <span class="text-xs font-semibold tracking-[.16em] text-[#7A1F3D]">{{ $step[0] }}</span>
                <h3 class="mt-3 text-base font-semibold text-slate-900">{{ $step[1] }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $step[2] }}</p>
            </article>
        @endforeach
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var button = document.getElementById('seller-referral-copy');
            var input = document.getElementById('seller-referral-link');
            var status = document.getElementById('seller-referral-copy-status');

            if (!button || !input || !status) return;

            button.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(input.value);
                    status.textContent = 'Invite link copied.';
                } catch (_) {
                    input.select();
                    document.execCommand('copy');
                    status.textContent = 'Invite link copied.';
                }
            });
        });
    </script>
@endsection
