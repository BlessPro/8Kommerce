@extends('layouts.admin')

@php($title = 'Audience')

@section('content')
<div class="space-y-7">
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Waitlist', $waitlistCount, 'People waiting for seller access', 'text-[#C2185B]'],
            ['Promoters', $promoterCount, 'Active promoter profiles', 'text-[#073B4C]'],
            ['Tracked opens', $visitCount, 'Recorded promoter link visits', 'text-indigo-700'],
            ['Verified sales', $conversionCount, 'Purchases attributed to promoters', 'text-emerald-700'],
        ] as $metric)
            <article class="border border-slate-200 bg-white p-5"><p class="text-xs font-bold uppercase tracking-[.14em] text-slate-400">{{ $metric[0] }}</p><strong class="mt-3 block text-3xl font-semibold {{ $metric[3] }}">{{ number_format($metric[1]) }}</strong><p class="mt-1 text-xs text-slate-500">{{ $metric[2] }}</p></article>
        @endforeach
    </section>

    <section class="border border-slate-200 bg-white">
        <div class="flex flex-col gap-2 border-b border-slate-200 p-5 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-bold uppercase tracking-[.14em] text-[#C2185B]">Pre-launch demand</p><h2 class="mt-1 text-xl font-semibold text-slate-950">Seller waitlist</h2></div><p class="text-xs text-slate-500">Newest numbers appear first.</p></div>
        <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3 font-semibold">Phone</th><th class="px-5 py-3 font-semibold">Source</th><th class="px-5 py-3 font-semibold">Status</th><th class="px-5 py-3 font-semibold">Joined</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($waitlist as $entry)<tr><td class="px-5 py-3 font-semibold text-slate-900">{{ $entry->phone }}</td><td class="px-5 py-3 text-slate-600">{{ $entry->source ?: 'Public' }}</td><td class="px-5 py-3"><span class="bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">{{ ucfirst($entry->status) }}</span></td><td class="px-5 py-3 text-slate-500">{{ $entry->joined_at?->format('d M Y, H:i') }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">No one has joined the waitlist yet.</td></tr>@endforelse</tbody></table></div>
        @if($waitlist->hasPages())<div class="border-t border-slate-200 p-4">{{ $waitlist->links() }}</div>@endif
    </section>

    <section class="border border-slate-200 bg-white">
        <div class="border-b border-slate-200 p-5"><p class="text-xs font-bold uppercase tracking-[.14em] text-emerald-700">Distribution network</p><h2 class="mt-1 text-xl font-semibold text-slate-950">Promoters</h2></div>
        <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3 font-semibold">Promoter</th><th class="px-5 py-3 font-semibold">Channels</th><th class="px-5 py-3 text-right font-semibold">Opens</th><th class="px-5 py-3 text-right font-semibold">Sales</th><th class="px-5 py-3 text-right font-semibold">Value</th><th class="px-5 py-3 font-semibold">Last login</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($promoters as $promoter)<tr><td class="px-5 py-3"><strong class="block text-slate-900">{{ $promoter->name }}</strong><span class="text-xs text-slate-500">{{ $promoter->phone }} · {{ $promoter->city ?: 'No area' }}</span></td><td class="px-5 py-3 text-xs text-slate-600">{{ collect($promoter->channels)->map(fn($channel) => str($channel)->replace('_', ' ')->title())->join(', ') ?: '—' }}</td><td class="px-5 py-3 text-right font-semibold">{{ number_format($promoter->visits_count) }}</td><td class="px-5 py-3 text-right font-semibold">{{ number_format($promoter->conversions_count) }}</td><td class="px-5 py-3 text-right font-semibold">{{ \App\Support\Money::format((string) $promoter->conversion_amount, config('services.paystack.currency', 'GHS')) }}</td><td class="px-5 py-3 text-slate-500">{{ $promoter->last_login_at?->diffForHumans() ?? 'Never' }}</td></tr>@empty<tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No promoter profiles yet.</td></tr>@endforelse</tbody></table></div>
        @if($promoters->hasPages())<div class="border-t border-slate-200 p-4">{{ $promoters->links() }}</div>@endif
    </section>
</div>
@endsection
