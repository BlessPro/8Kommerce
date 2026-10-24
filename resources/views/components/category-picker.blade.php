@props(['name', 'value' => '', 'categories' => collect()])
@php
    $items = collect($categories)->filter()->values();
    $current = trim((string) $value);
@endphp
<div class="mt-2 flex items-start gap-2">
    <select name="{{ $name }}" {{ $attributes->class(['min-w-0 flex-1 rounded-lg border-slate-200 bg-white text-sm focus:border-emerald-500 focus:ring-emerald-500']) }}>
        <option value="">Uncategorised</option>
        @if($current !== '' && !$items->contains(fn ($item) => strcasecmp((string) $item, $current) === 0))
            <option value="{{ $current }}" selected>{{ $current }}</option>
        @endif
        @foreach($items as $category)
            <option value="{{ $category }}" @selected(strcasecmp((string) $category, $current) === 0)>{{ $category }}</option>
        @endforeach
    </select>
    <a href="{{ route('product-categories.index') }}" class="inline-flex min-h-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700">Manage</a>
</div>
