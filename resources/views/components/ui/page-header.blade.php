@props(['title', 'description' => null, 'eyebrow' => null])

<header {{ $attributes->class('lp-page-header') }}>
    <div class="min-w-0">
        @if($eyebrow)<p class="lp-eyebrow">{{ $eyebrow }}</p>@endif
        <h1 @class(['lp-page-heading', 'mt-1' => $eyebrow])>{{ $title }}</h1>
        @if($description)<p class="lp-page-description">{{ $description }}</p>@endif
    </div>
    @isset($actions)
        <div class="lp-page-actions">{{ $actions }}</div>
    @endisset
</header>
