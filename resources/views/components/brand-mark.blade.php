@props(['reversed' => false])
<img
    src="{{ asset('brand/8kommerce-monogram-transparent-teal.png') }}?v=3"
    sizes="64px"
    alt="8Kommerce"
    decoding="async"
    {{ $attributes->class([
        'aspect-square shrink-0 object-contain',
        'brightness-0 invert' => $reversed,
    ]) }}
>
