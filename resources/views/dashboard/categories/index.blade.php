@php($title = 'Product categories')
@extends('layouts.dashboard')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="lp-eyebrow">Catalogue</p>
                <h1 class="lp-page-heading">Product categories</h1>
                <p class="lp-page-description">Organise storefront filters without editing products one by one.</p>
            </div>
            <a href="{{ route('products.create') }}" class="lp-button-secondary">Back to products</a>
        </header>

        @if(session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
        @endif

        <section class="lp-card p-5 sm:p-6">
            <h2 class="text-base font-semibold text-slate-900">Add categories</h2>
            <p class="mt-1 text-sm text-slate-500">Separate names with commas to add several at once.</p>
            <form method="POST" action="{{ route('product-categories.store') }}" class="mt-4 flex flex-col gap-3 sm:flex-row" data-form-draft="categories-{{ auth()->id() }}" data-form-clear-on-load="{{ session('status') ? '1' : '0' }}">
                @csrf
                <div class="flex-1">
                    <input name="categories" value="{{ old('categories') }}" placeholder="Fashion, Beauty, Home & living" class="w-full rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('categories')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button class="lp-button-brand" type="submit">Add categories</button>
            </form>
        </section>

        <section class="lp-card overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-semibold text-slate-900">All categories</h2></div>
            @forelse($categories as $category)
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 last:border-0 sm:flex-row sm:items-center">
                    <form method="POST" action="{{ route('product-categories.update', $category) }}" class="flex flex-1 gap-2">
                        @csrf @method('PATCH')
                        <input name="name" value="{{ $category->name }}" class="min-w-0 flex-1 rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <button type="submit" class="lp-button-secondary">Update</button>
                    </form>
                    <span class="text-xs text-slate-500">{{ $category->product_count }} {{ str('product')->plural($category->product_count) }}</span>
                    <form method="POST" action="{{ route('product-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Its products will become uncategorised.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-4 text-sm font-semibold text-rose-700 hover:bg-rose-100">Delete</button>
                    </form>
                </div>
            @empty
                <div class="px-5 py-12 text-center"><p class="font-medium text-slate-700">No saved categories yet</p><p class="mt-1 text-sm text-slate-500">They will also appear here automatically when you save a product.</p></div>
            @endforelse
        </section>
    </div>
@endsection
