@props(['label', 'value', 'meta' => null, 'icon' => null])

<article {{ $attributes->class('lp-stat-card') }}>
    <div class="flex items-start justify-between gap-3">
        <p class="lp-stat-card__label">{{ $label }}</p>
        @if($icon)<span class="lp-icon-box h-8 w-8"><x-ui.icon :name="$icon" size="sm" /></span>@endif
    </div>
    <p class="lp-stat-card__value">{{ $value }}</p>
    @if($meta)<p class="lp-stat-card__meta">{{ $meta }}</p>@endif
</article>
