@php
    $title = 'Create Invoice';
@endphp
@extends('layouts.dashboard')

@section('content')
    <div class="max-w-3xl lp-card p-5 sm:p-6">
        <h1 class="lp-page-heading">Create invoice</h1>
        <p class="lp-page-description">Set the amount, payment terms, and customer details.</p>
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
                <button type="submit" class="lp-button-brand">
                    Create invoice
                </button>
                <a href="{{ route('invoices.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</a>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
<script>document.querySelector('[data-image-input]')?.addEventListener('change',function(){const file=this.files?.[0],preview=document.querySelector('[data-image-preview]');if(!file||!preview)return;preview.src=URL.createObjectURL(file);preview.classList.remove('hidden');});</script>
@endpush
