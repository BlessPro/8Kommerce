@props([
    'variant' => 'brand',
    'size' => 'md',
    'href' => null,
    'icon' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'lp-button-primary',
        'brand' => 'lp-button-brand',
        'secondary' => 'lp-button-secondary',
        'danger' => 'lp-button-danger',
        'success' => 'lp-button-success',
        'warning' => 'lp-button-warning',
        'ghost' => 'lp-button-ghost',
    ];
    $sizes = ['sm' => 'lp-button-sm', 'md' => '', 'lg' => 'lp-button-lg'];
    $classes = trim('lp-button '.($variants[$variant] ?? $variants['brand']).' '.($sizes[$size] ?? ''));
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if($icon)<x-ui.icon :name="$icon" size="sm" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if($icon)<x-ui.icon :name="$icon" size="sm" />@endif
        {{ $slot }}
    </button>
@endif
