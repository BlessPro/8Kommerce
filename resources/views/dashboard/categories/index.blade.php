@php
    $title = 'Product categories';
@endphp
@extends('layouts.dashboard')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <x-ui.page-header title="Product categories" description="Keep storefront filters useful and make products easier for customers to browse.">
            <x-slot:actions>
                <x-ui.button variant="secondary" icon="arrow-left" :href="route('products.index')">Products</x-ui.button>
                <x-ui.button icon="plus" :href="route('products.create')">Add products</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @if(session('status'))
            <x-ui.alert tone="success" title="Categories updated">{{ session('status') }}</x-ui.alert>
        @endif

        <section class="lp-card grid gap-5 p-5 sm:p-6 lg:grid-cols-[0.7fr_1.3fr] lg:items-end">
            <div><p class="lp-eyebrow">Catalogue organisation</p><h2 class="mt-1 text-base font-semibold text-slate-900">Add categories</h2>
            <p class="mt-1 text-sm text-slate-500">Separate names with commas to add several at once.</p>
            </div>
            <form method="POST" action="{{ route('product-categories.store') }}" class="flex flex-col gap-3 sm:flex-row" data-form-draft="categories-{{ auth()->id() }}" data-form-clear-on-load="{{ session('status') ? '1' : '0' }}">
                @csrf
                <div class="flex-1">
                    <input name="categories" value="{{ old('categories') }}" placeholder="Fashion, Beauty, Home & living" class="w-full rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('categories')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <x-ui.button icon="plus" type="submit">Add categories</x-ui.button>
            </form>
        </section>

        <section class="lp-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><p class="lp-eyebrow">Saved filters</p><h2 class="mt-1 font-semibold text-slate-900">All categories</h2></div><x-ui.badge>{{ $categories->count() }} total</x-ui.badge></div>
            @forelse($categories as $category)
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 last:border-0 sm:flex-row sm:items-center">
                    <form method="POST" action="{{ route('product-categories.update', $category) }}" class="flex flex-1 gap-2">
                        @csrf @method('PATCH')
                        <input name="name" value="{{ $category->name }}" class="min-w-0 flex-1 rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <x-ui.button type="submit" variant="secondary" size="sm" icon="check">Save</x-ui.button>
                    </form>
                    <span class="text-xs text-slate-500">{{ $category->product_count }} {{ str('product')->plural($category->product_count) }}</span>
                    <form method="POST" action="{{ route('product-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Its products will become uncategorised.')">
                        @csrf @method('DELETE')
                        <x-ui.button type="submit" variant="danger" size="sm" icon="trash">Delete</x-ui.button>
                    </form>
                </div>
            @empty
                <div class="px-5 py-12 text-center"><p class="font-medium text-slate-700">No saved categories yet</p><p class="mt-1 text-sm text-slate-500">They will also appear here automatically when you save a product.</p></div>
            @endforelse
        </section>
    </div>
@endsection
