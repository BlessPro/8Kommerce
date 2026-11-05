@props(['tone' => 'neutral', 'icon' => null])

@php
    $tones = [
        'neutral' => 'lp-badge-neutral',
        'brand' => 'lp-badge-brand',
        'info' => 'lp-badge-info',
        'success' => 'lp-badge-success',
        'warning' => 'lp-badge-warning',
        'danger' => 'lp-badge-danger',
    ];
@endphp

<span {{ $attributes->class(['lp-badge', $tones[$tone] ?? $tones['neutral']]) }}>
    @if($icon)<x-ui.icon :name="$icon" size="xs" />@endif
    {{ $slot }}
</span>
