@props(['label', 'value', 'meta' => null, 'icon' => null, 'liveKey' => null, 'liveMetaKey' => null])

<article {{ $attributes->class('lp-stat-card') }}>
    <div class="flex items-start justify-between gap-3">
        <p class="lp-stat-card__label">{{ $label }}</p>
        @if($icon)<span class="lp-icon-box h-8 w-8"><x-ui.icon :name="$icon" size="sm" /></span>@endif
    </div>
    <p class="lp-stat-card__value" @if($liveKey) data-live-key="{{ $liveKey }}" @endif>{{ $value }}</p>
    @if($meta)<p class="lp-stat-card__meta" @if($liveMetaKey) data-live-key="{{ $liveMetaKey }}" @endif>{{ $meta }}</p>@endif
</article>
