@props(['title', 'description' => null, 'icon' => 'package'])

<div {{ $attributes->class('lp-empty-state') }}>
    <span class="lp-icon-box mx-auto"><x-ui.icon :name="$icon" /></span>
    <p class="mt-4 font-semibold text-slate-900">{{ $title }}</p>
    @if($description)<p class="mx-auto mt-1 max-w-md leading-6">{{ $description }}</p>@endif
    @isset($action)<div class="mt-5 flex justify-center">{{ $action }}</div>@endisset
</div>
