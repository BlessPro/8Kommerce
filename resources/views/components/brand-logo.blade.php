@props(['reversed' => false, 'symbolOnly' => false])
<span {{ $attributes->class(['inline-flex items-center', 'gap-2.5' => ! $symbolOnly]) }}>
    <x-brand-mark :reversed="$reversed" class="h-full w-auto shrink-0" />
    @unless($symbolOnly)
        <span class="font-display font-semibold tracking-[-0.035em] {{ $reversed ? 'text-[#FFF8F2]' : 'text-[#073B4C]' }}">8Kommerce</span>
    @endunless
</span>
