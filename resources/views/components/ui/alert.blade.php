@props(['tone' => 'info', 'title' => null])

@php
    $tones = [
        'info' => ['lp-alert-info', 'info-circle'],
        'success' => ['lp-alert-success', 'circle-check'],
        'warning' => ['lp-alert-warning', 'warning'],
        'danger' => ['lp-alert-danger', 'alert-circle'],
    ];
    [$toneClass, $icon] = $tones[$tone] ?? $tones['info'];
@endphp

<div {{ $attributes->class(['lp-alert', $toneClass]) }} role="{{ $tone === 'danger' ? 'alert' : 'status' }}">
    <x-ui.icon :name="$icon" class="mt-0.5" />
    <div class="min-w-0">
        @if($title)<p class="font-semibold">{{ $title }}</p>@endif
        <div @class(['mt-0.5' => $title])>{{ $slot }}</div>
    </div>
</div>
