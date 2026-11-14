@php($title = 'Become an 8Kommerce promoter')
@extends('layouts.public')

@section('og_title', 'Help good products get discovered — 8Kommerce')
@section('og_description', 'Join the 8Kommerce promoter network, share tracked product links and follow the sales your recommendations create.')

@section('content')
    <div class="space-y-8 pb-8">
        <section class="overflow-hidden rounded-xl bg-[#073B4C] text-white">
            <div class="grid gap-8 px-6 py-10 sm:px-10 lg:grid-cols-[1fr_.8fr] lg:items-end lg:px-14 lg:py-14">
                <div><p class="text-xs font-bold uppercase tracking-[.16em] text-[#A9D4DE]">8Kommerce promoter network</p><h1 class="mt-4 max-w-2xl font-display text-4xl font-semibold tracking-[-.04em] sm:text-5xl">Share products worth talking about.</h1><p class="mt-5 max-w-xl text-base leading-7 text-white/70">Anyone can join—even if you already sell on 8Kommerce. Get tracked links for marketplace products and help independent businesses reach more customers.</p></div>
                <div class="grid grid-cols-3 gap-px overflow-hidden rounded-lg bg-white/15 text-center"><div class="bg-[#0A4657] p-4"><strong class="block text-xl">Free</strong><span class="mt-1 block text-[11px] text-white/60">to join</span></div><div class="bg-[#0A4657] p-4"><strong class="block text-xl">Tracked</strong><span class="mt-1 block text-[11px] text-white/60">visits & sales</span></div><div class="bg-[#0A4657] p-4"><strong class="block text-xl">Soon</strong><span class="mt-1 block text-[11px] text-white/60">rewards</span></div></div>
            </div>
        </section>

        @if(session('status') === 'promoter-existing')<x-ui.alert tone="info" title="You may already be registered">For privacy, an existing promoter link is not revealed through a phone-number lookup. Open the link you saved previously or contact support.</x-ui.alert>@endif

        @if($profile)
            <section class="grid gap-6 lg:grid-cols-[.72fr_1.28fr]">
                <article class="lp-card p-6">
                    <p class="lp-eyebrow text-emerald-700">Your promoter workspace</p><h2 class="mt-2 text-2xl font-semibold text-slate-950">Welcome, {{ str($profile->name)->before(' ') }}</h2><p class="mt-2 text-sm leading-6 text-slate-600">Tracking is active. Rewards and payout rules are coming soon; visits and completed purchases are already being recorded.</p>
                    <div class="mt-5 grid grid-cols-3 gap-2"><div class="rounded-md bg-slate-50 p-3"><span class="text-xs text-slate-500">Link visits</span><strong class="mt-1 block text-xl text-slate-950">{{ number_format($profile->visits_count) }}</strong></div><div class="rounded-md bg-slate-50 p-3"><span class="text-xs text-slate-500">Sales</span><strong class="mt-1 block text-xl text-slate-950">{{ number_format($profile->conversions_count) }}</strong></div><div class="rounded-md bg-slate-50 p-3"><span class="text-xs text-slate-500">Value</span><strong class="mt-1 block text-sm text-slate-950">{{ \App\Support\Money::format((string) $profile->conversion_amount, config('services.paystack.currency', 'GHS')) }}</strong></div></div>
                    <label class="lp-form-label mt-6" for="promoter-main-link">Your marketplace link</label><div class="mt-2 flex gap-2"><input id="promoter-main-link" readonly value="{{ route('promoters.visit', ['promoter' => $profile->public_code]) }}" class="lp-form-control min-w-0 flex-1 bg-slate-50 text-xs"><x-ui.button type="button" variant="secondary" icon="copy" data-copy-promoter="#promoter-main-link">Copy</x-ui.button></div>
                </article>
                <article class="lp-card p-6">
                    <p class="lp-eyebrow">Product links</p><h2 class="mt-1 text-xl font-semibold text-slate-950">Choose something to share</h2><p class="mt-1 text-sm text-slate-500">Each link remembers that the customer came through you.</p>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">@forelse($products as $product)<div class="flex items-center gap-3 rounded-md border border-slate-200 p-3"><div class="h-14 w-14 shrink-0 overflow-hidden rounded bg-slate-100">@if($product->image_path)<img src="{{ asset('storage/'.$product->image_path) }}" alt="" class="h-full w-full object-cover">@endif</div><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-900">{{ $product->name }}</p><p class="truncate text-xs text-slate-500">{{ $product->user?->sellerProfile?->business_name }}</p></div><button type="button" data-copy-value="{{ route('promoters.product', ['promoter' => $profile->public_code, 'product' => $product->slug]) }}" class="grid h-9 w-9 shrink-0 place-items-center rounded-md border border-slate-200 text-[#073B4C]" aria-label="Copy promoter link for {{ $product->name }}"><x-ui.icon name="copy" size="sm" /></button></div>@empty<p class="text-sm text-slate-500">Products will appear here as sellers publish them.</p>@endforelse</div>
                    <p data-promoter-copy-status class="mt-3 min-h-5 text-xs font-semibold text-emerald-700" aria-live="polite"></p>
                </article>
            </section>
        @else
            <section class="grid gap-7 lg:grid-cols-[.82fr_1.18fr]">
                <div class="space-y-4">@foreach([['01','Join once','Tell us how you normally reach people—WhatsApp, social media or in person.'],['02','Choose products','Pick real products from active sellers and copy your tracked link.'],['03','Share naturally','Recommend products to people who would genuinely find them useful.'],['04','See your impact','Visits and completed sales are counted now. Rewards will be introduced later.']] as $step)<div class="lp-card flex gap-4 p-5"><span class="text-xs font-bold text-[#7A1F3D]">{{ $step[0] }}</span><div><h2 class="font-semibold text-slate-950">{{ $step[1] }}</h2><p class="mt-1 text-sm leading-6 text-slate-600">{{ $step[2] }}</p></div></div>@endforeach</div>
                <form method="POST" action="{{ route('promoters.store') }}" class="lp-card p-6 sm:p-8">@csrf<p class="lp-eyebrow text-emerald-700">Join the network</p><h2 class="mt-2 text-2xl font-semibold text-slate-950">Create your promoter link</h2><div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Your name" name="name" required class="sm:col-span-2"><input name="name" value="{{ old('name', auth()->user()?->name) }}" class="lp-form-control" required maxlength="120"></x-ui.field>
                    <x-ui.field label="Phone number" name="phone_number" required><input name="phone_number" value="{{ old('phone_number', auth()->user()?->phone) }}" class="lp-form-control" inputmode="tel" required></x-ui.field>
                    <x-ui.field label="City or area" name="city"><input name="city" value="{{ old('city') }}" class="lp-form-control" maxlength="120" placeholder="e.g. Accra"></x-ui.field>
                    <fieldset class="sm:col-span-2"><legend class="lp-form-label">Where do you share?</legend><div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">@foreach(['whatsapp'=>'WhatsApp','instagram'=>'Instagram','facebook'=>'Facebook','tiktok'=>'TikTok','in_person'=>'In person'] as $value=>$label)<label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2.5 text-sm"><input type="checkbox" name="channels[]" value="{{ $value }}" class="rounded border-slate-300 text-emerald-700" @checked(in_array($value, old('channels', [])))>{{ $label }}</label>@endforeach</div>@error('channels')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</fieldset>
                    <x-ui.field label="Anything we should know?" name="note" class="sm:col-span-2"><textarea name="note" rows="3" class="lp-form-control" maxlength="500" placeholder="Optional: your audience, interests or how you plan to share">{{ old('note') }}</textarea></x-ui.field>
                    <label class="sm:col-span-2 flex items-start gap-3 text-sm leading-6 text-slate-600"><input type="checkbox" name="terms" value="1" class="mt-1 rounded border-slate-300 text-emerald-700" required><span>I will share products honestly, avoid spam, and understand that rewards are not active yet.</span></label>
                </div><x-ui.button type="submit" variant="brand" class="mt-6 w-full justify-center">Create my promoter link</x-ui.button></form>
            </section>
        @endif
    </div>
    <script>(()=>{const status=document.querySelector('[data-promoter-copy-status]');const copy=async(value)=>{await navigator.clipboard.writeText(value);if(status)status.textContent='Promoter link copied.';window.setTimeout(()=>{if(status)status.textContent='';},1800);};document.querySelectorAll('[data-copy-value]').forEach(button=>button.addEventListener('click',()=>copy(button.dataset.copyValue)));document.querySelectorAll('[data-copy-promoter]').forEach(button=>button.addEventListener('click',()=>copy(document.querySelector(button.dataset.copyPromoter)?.value||'')));})();</script>
@endsection

