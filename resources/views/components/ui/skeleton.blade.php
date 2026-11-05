@props(['width' => 'w-full', 'height' => 'h-4'])

<span {{ $attributes->class(['lp-skeleton block', $width, $height]) }} aria-hidden="true"></span>
