@php
    $title = 'Promote';
    $remaining = max(0, $monthlyLimit - $monthlyUsed);
    $statusStyles = [
        'submitted' => 'bg-sky-50 text-sky-700', 'under_review' => 'bg-amber-50 text-amber-800',
        'selected' => 'bg-emerald-50 text-emerald-700', 'preparing' => 'bg-violet-50 text-violet-700',
        'scheduled' => 'bg-indigo-50 text-indigo-700', 'published' => 'bg-emerald-50 text-emerald-700',
        'not_selected' => 'bg-slate-100 text-slate-600', 'completed' => 'bg-slate-900 text-white',
    ];
@endphp
@extends('layouts.dashboard')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <x-ui.page-header title="Promote your products" description="Nominate an existing product for editorial features across 8Kommerce social and discovery channels.">
        <x-slot:actions><x-ui.button variant="secondary" icon="target" :href="route('goals.index')">Goals</x-ui.button></x-slot:actions>
    </x-ui.page-header>

    <section class="grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card label="Nominations remaining" :value="$remaining" meta="Available this month" icon="speakerphone" />
        <x-ui.stat-card label="Used this month" :value="$monthlyUsed" :meta="$monthlyLimit.' monthly allowance'" icon="activity" />
        <x-ui.stat-card label="Verified rating" :value="$ratingSummary['public'] ? number_format($ratingSummary['average'], 1).'/5' : 'Building'" :meta="$ratingSummary['count'].' verified '.Str::plural('review', $ratingSummary['count'])" icon="circle-check" />
    </section>

    @if(session('status') === 'promotion-submitted')
        <x-ui.alert tone="success" title="Nomination submitted">Your product is now waiting for editorial review.</x-ui.alert>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(300px,.8fr)]">
        <section class="lp-card overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4">
                <p class="lp-eyebrow">Put your best product forward</p>
                <h2 class="mt-1 font-semibold text-slate-950">Nominate a product</h2>
                <p class="mt-1 text-sm text-slate-500">Selection is editorial and does not guarantee publication.</p>
            </div>
            <form method="POST" action="{{ route('promotions.store') }}" class="space-y-5 p-5">
                @csrf
                <div>
                    <label for="product_id" class="block text-sm font-semibold text-slate-800">Product</label>
                    <select id="product_id" name="product_id" required class="lp-form-control mt-2 w-full">
                        <option value="">Select an active product</option>
                        @foreach($products as $product)<option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }} · GHS {{ number_format((float) $product->price, 2) }}</option>@endforeach
                    </select>
                    @error('product_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="purpose" class="block text-sm font-semibold text-slate-800">What should we highlight?</label>
                    <select id="purpose" name="purpose" required class="lp-form-control mt-2 w-full">
                        @foreach(\App\Models\PromotionSubmission::PURPOSES as $value => $label)<option value="{{ $value }}" @selected(old('purpose') === $value)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <div class="flex items-center justify-between"><label for="seller_note" class="text-sm font-semibold text-slate-800">Brief for our editorial team</label><span class="text-xs text-slate-400">Optional</span></div>
                    <textarea id="seller_note" name="seller_note" rows="4" maxlength="600" placeholder="What makes this product useful, timely or different? Mention any launch date or stock deadline." class="lp-form-control mt-2 w-full">{{ old('seller_note') }}</textarea>
                    @error('seller_note')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <x-ui.button type="submit" icon="speakerphone" :disabled="$remaining < 1 || $products->isEmpty()">Submit for consideration</x-ui.button>
                @if($products->isEmpty())<p class="text-xs text-amber-700">Add an active product before submitting a nomination.</p>@endif
            </form>
        </section>

        <aside class="space-y-4">
            <section class="rounded-lg border border-slate-200 bg-[#073B4C] p-5 text-white">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#A9D4DE]">Verified customer rating</p>
                @if($ratingSummary['public'])
                    <p class="mt-4 text-3xl font-semibold">{{ number_format($ratingSummary['average'], 1) }} <span class="text-[#F4C95D]">★</span></p>
                    <p class="mt-1 text-sm text-slate-300">{{ $ratingSummary['count'] }} verified {{ Str::plural('review', $ratingSummary['count']) }}</p>
                @else
                    <p class="mt-4 text-xl font-semibold">Building your reputation</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">Your public score appears after three verified customer ratings. You currently have {{ $ratingSummary['count'] }}.</p>
                @endif
            </section>
            <section class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-5">
                <span class="lp-icon-box"><x-ui.icon name="chart-bar" size="sm" /></span>
                <h2 class="mt-4 font-semibold text-slate-950">Performance insights are coming soon</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Tracking begins when a post is published. Future reports will include product visits, carts, checkouts and paid orders.</p>
            </section>
        </aside>
    </div>

    <section class="lp-card overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-semibold text-slate-950">Promotion history</h2></div>
        <div class="divide-y divide-slate-100">
            @forelse($submissions as $submission)
                <article class="grid gap-4 p-5 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-md bg-slate-100">@if($submission->product?->image_path)<img src="{{ asset('storage/'.$submission->product->image_path) }}" alt="" class="h-full w-full object-cover">@endif</div>
                        <div class="min-w-0"><h3 class="truncate font-semibold text-slate-950">{{ $submission->product?->name ?? 'Product removed' }}</h3><p class="mt-1 text-xs text-slate-500">{{ \App\Models\PromotionSubmission::PURPOSES[$submission->purpose] ?? $submission->purpose }} · Submitted {{ $submission->created_at->diffForHumans() }}</p>@if($submission->channel)<p class="mt-1 text-xs font-medium text-slate-600">{{ \App\Models\PromotionSubmission::CHANNELS[$submission->channel] ?? $submission->channel }}</p>@endif</div>
                    </div>
                    <div class="flex items-center gap-3 md:justify-end"><span class="rounded px-2.5 py-1 text-xs font-semibold {{ $statusStyles[$submission->status] ?? 'bg-slate-100 text-slate-600' }}">{{ \App\Models\PromotionSubmission::STATUSES[$submission->status] ?? $submission->status }}</span>@if($submission->published_url)<a href="{{ $submission->published_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-sm font-semibold text-emerald-700">View post <x-ui.icon name="external-link" size="xs" /></a>@endif</div>
                </article>
            @empty
                <x-ui.empty-state title="No nominations yet" description="Submitted products and their publication status will appear here." icon="speakerphone" class="border-0" />
            @endforelse
        </div>
    </section>
</div>
@endsection
