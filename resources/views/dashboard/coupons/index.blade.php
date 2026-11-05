@php
    $title = 'Coupons';
    $latestCoupon = $coupons->first();
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header title="Coupons" description="Create clear, controlled discounts and monitor how often each code is used.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="shopping-bag" :href="route('products.orders')">Orders</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if(session('status') === 'coupon-created')
        <x-ui.alert tone="success" title="Coupon created" class="mt-5">The new discount is available for eligible checkouts.</x-ui.alert>
    @endif
    @if(session('status') === 'coupon-updated')
        <x-ui.alert tone="info" title="Coupon updated" class="mt-5">The availability of this code has changed.</x-ui.alert>
    @endif

    @if($latestCoupon)
        <section class="lp-card mt-6 border-indigo-200 bg-indigo-50/50 p-5">
            <p class="lp-eyebrow text-indigo-700">Newest coupon</p>
            <div class="mt-2 flex flex-wrap items-center justify-between gap-4"><div><p class="font-mono text-xl font-bold tracking-wide text-slate-900">{{ $latestCoupon->code }}</p><p class="mt-1 text-sm text-slate-600">{{ $latestCoupon->discount_type === \App\Models\Coupon::TYPE_PERCENT ? rtrim(rtrim(number_format((float) $latestCoupon->discount_value, 2), '0'), '.').'%' : \App\Support\Money::format((string) $latestCoupon->discount_value, config('services.paystack.currency', 'GHS')) }} off · {{ $latestCoupon->is_active ? 'Active' : 'Inactive' }}</p></div><span class="rounded-lg bg-white px-3 py-2 text-xs font-semibold text-indigo-700">Used {{ $latestCoupon->used_count }}{{ $latestCoupon->usage_limit ? '/'.$latestCoupon->usage_limit : '' }}</span></div>
        </section>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-[0.95fr_1.05fr] lg:items-start">
        <section class="lp-card p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-slate-900">Create coupon</h2>
            <p class="mt-1 text-sm text-slate-500">Use codes to drive conversions on cart checkout.</p>
            <form method="POST" action="{{ route('coupons.store') }}" class="mt-5 grid gap-3 sm:grid-cols-2" data-form-draft="coupon-create-{{ auth()->id() }}" data-form-clear-on-load="{{ session('status') === 'coupon-created' ? '1' : '0' }}">
                @csrf
                <div>
                    <label class="text-sm font-medium text-slate-700">Code</label>
                    <input name="code" value="{{ old('code') }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="SAVE10" />
                    @error('code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Type</label>
                    <select name="discount_type" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="PERCENT" @selected(old('discount_type', 'PERCENT') === 'PERCENT')>Percent</option>
                        <option value="FIXED" @selected(old('discount_type') === 'FIXED')>Fixed amount</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Discount value</label>
                    <div class="mt-1 flex overflow-hidden rounded-lg border border-slate-200 bg-white"><span data-discount-unit class="flex items-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-600">%</span><input name="discount_value" type="number" step="0.01" min="0.01" value="{{ old('discount_value') }}" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" placeholder="10" /></div>
                    @error('discount_value') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Max discount (optional)</label>
                    <div class="mt-1 flex overflow-hidden rounded-lg border border-slate-200 bg-white"><span class="flex items-center border-r border-slate-200 bg-slate-50 px-2 text-xs font-semibold text-slate-600">GH₵</span><input name="max_discount" type="number" step="0.01" min="0" value="{{ old('max_discount') }}" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" /></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Min order amount (optional)</label>
                    <div class="mt-1 flex overflow-hidden rounded-lg border border-slate-200 bg-white"><span class="flex items-center border-r border-slate-200 bg-slate-50 px-2 text-xs font-semibold text-slate-600">GH₵</span><input name="min_order_amount" type="number" step="0.01" min="0" value="{{ old('min_order_amount') }}" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" /></div>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Usage limit (optional)</label>
                    <input name="usage_limit" type="number" min="1" value="{{ old('usage_limit') }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Starts at (optional)</label>
                    <input name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Ends at (optional)</label>
                    <input name="ends_at" type="datetime-local" value="{{ old('ends_at') }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 sm:col-span-2">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" checked>
                    Active immediately
                </label>
                <x-ui.button type="submit" icon="plus" class="sm:col-span-2">Create coupon</x-ui.button>
            </form>
        </section>

        <section class="lp-card overflow-hidden">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><p class="lp-eyebrow">Discount library</p><h2 class="mt-1 text-lg font-semibold text-slate-900">Existing coupons</h2></div><x-ui.badge>{{ $coupons->total() }} total</x-ui.badge></header>
            <div class="divide-y divide-slate-100">
                @forelse($coupons as $coupon)
                    <article class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $coupon->code }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $coupon->discount_type === \App\Models\Coupon::TYPE_PERCENT ? rtrim(rtrim(number_format((float) $coupon->discount_value, 2), '0'), '.') . '%' : \App\Support\Money::format((string) $coupon->discount_value, config('services.paystack.currency', 'GHS')) }}
                                    · Used {{ $coupon->used_count }}{{ $coupon->usage_limit ? '/'.$coupon->usage_limit : '' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('coupons.update', $coupon) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $coupon->is_active ? 0 : 1 }}">
                                <button type="submit" class="rounded-md border px-3 py-1.5 text-xs font-semibold {{ $coupon->is_active ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                                    {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">
                            Min: {{ $coupon->min_order_amount ? \App\Support\Money::format((string) $coupon->min_order_amount, config('services.paystack.currency', 'GHS')) : 'None' }}
                            · Max discount: {{ $coupon->max_discount ? \App\Support\Money::format((string) $coupon->max_discount, config('services.paystack.currency', 'GHS')) : 'None' }}
                        </p>
                    </article>
                @empty
                    <x-ui.empty-state title="No coupons yet" description="Create a controlled discount for your next campaign." icon="ticket" class="border-0" />
                @endforelse
            </div>
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $coupons->links() }}
            </div>
        </section>
    </div>
@endsection
@push('scripts')
<script>(()=>{const type=document.querySelector('select[name="discount_type"]'),unit=document.querySelector('[data-discount-unit]');const sync=()=>{if(unit)unit.textContent=type?.value==='FIXED'?'GH₵':'%';};type?.addEventListener('change',sync);sync();})();</script>
@endpush
