@php
    $title = 'Create Invoice';
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-ui.page-header title="Create invoice" description="Create a secure payment link for a customer, custom order or service.">
        <x-slot:actions><x-ui.button variant="ghost" icon="arrow-left" :href="route('invoices.index')">Invoices</x-ui.button></x-slot:actions>
    </x-ui.page-header>
    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px] lg:items-start">
    <section class="lp-card p-5 sm:p-6">
        <div class="flex items-start gap-3"><span class="lp-icon-box"><x-ui.icon name="file-invoice" /></span><div><h2 class="text-base font-semibold text-slate-900">Invoice details</h2><p class="mt-1 text-sm text-slate-500">The customer will see this information before paying.</p></div></div>
        <form class="mt-6 space-y-5" method="POST" action="{{ route('invoices.store') }}" enctype="multipart/form-data" data-form-draft="invoice-create-{{ auth()->id() }}">
            @csrf
            <div>
                <label class="text-sm font-medium text-slate-700">Title</label>
                <input name="title" value="{{ old('title') }}" required class="mt-2 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500" />
                @error('title') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Description</label>
                <textarea name="description" rows="4" class="mt-2 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
                @error('description') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">Total amount</label>
                    <div class="mt-2 flex overflow-hidden rounded-lg border border-slate-200 bg-white"><span class="flex items-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-600">GH₵</span><input name="total_amount" value="{{ old('total_amount') }}" required type="number" min="0.01" step="0.01" class="min-w-0 flex-1 border-0 focus:ring-0" /></div>
                    @error('total_amount') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">Payment mode</label>
                    <select name="payment_mode" class="mt-2 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="FULL" {{ old('payment_mode') === 'FULL' ? 'selected' : '' }}>Full payment</option>
                        <option value="PARTIAL" {{ old('payment_mode') === 'PARTIAL' ? 'selected' : '' }}>Partial (deposit)</option>
                    </select>
                    @error('payment_mode') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Deposit amount (for partial)</label>
                <div class="mt-2 flex overflow-hidden rounded-lg border border-slate-200 bg-white"><span class="flex items-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-600">GH₵</span><input name="deposit_amount" value="{{ old('deposit_amount') }}" type="number" min="0" step="0.01" class="min-w-0 flex-1 border-0 focus:ring-0" /></div>
                @error('deposit_amount') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Customer name (optional)</label>
                <input name="customer_name" value="{{ old('customer_name') }}" class="mt-2 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500" />
                @error('customer_name') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700">Image (optional)</label>
                <input name="image" type="file" accept="image/*" data-image-input class="mt-2 w-full rounded-xl border-slate-200 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-emerald-700" />
                <img data-image-preview class="mt-3 hidden h-32 w-32 rounded-lg border border-slate-200 object-cover" alt="Selected invoice image preview">
                @error('image') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-4">
                <x-ui.button type="submit" icon="plus">Create invoice</x-ui.button>
                <x-ui.button variant="ghost" :href="route('invoices.index')">Cancel</x-ui.button>
            </div>
        </form>
    </section>
    <aside class="lp-card p-5 lg:sticky lg:top-24"><p class="lp-eyebrow">How it works</p><h2 class="mt-1 text-base font-semibold text-slate-900">Share and get paid</h2><ol class="mt-4 space-y-4 text-sm text-slate-600"><li class="flex gap-3"><span class="font-semibold text-emerald-700">1.</span><span>Create the invoice with a clear title and amount.</span></li><li class="flex gap-3"><span class="font-semibold text-emerald-700">2.</span><span>Copy its secure link and send it to your customer.</span></li><li class="flex gap-3"><span class="font-semibold text-emerald-700">3.</span><span>Payments and outstanding balances update automatically.</span></li></ol><div class="mt-5 border-t border-slate-200 pt-4"><p class="text-xs font-semibold text-slate-700">Draft protection is on</p><p class="mt-1 text-xs leading-5 text-slate-500">Your entries are restored after an accidental refresh on this device.</p></div></aside>
    </div>
@endsection
@push('scripts')
<script>document.querySelector('[data-image-input]')?.addEventListener('change',function(){const file=this.files?.[0],preview=document.querySelector('[data-image-preview]');if(!file||!preview)return;preview.src=URL.createObjectURL(file);preview.classList.remove('hidden');});</script>
@endpush
