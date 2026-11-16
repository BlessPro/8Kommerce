@props(['reversed' => false, 'symbolOnly' => false])
<span {{ $attributes->class(['inline-flex items-center', 'gap-2.5' => $reversed && ! $symbolOnly]) }}>
    @if($symbolOnly)
        <x-brand-mark :reversed="$reversed" class="h-full w-auto" />
    @elseif($reversed)
        <x-brand-mark :reversed="true" class="h-full w-auto" />
        <span class="font-display font-semibold tracking-[-0.035em] text-[#FFF8F2]">8Kommerce</span>
    @else
        <img src="{{ asset('brand/8kommerce-logo-transparent-teal.png') }}?v=3" alt="8Kommerce" class="h-full w-auto object-contain" decoding="async">
    @endif
</span>
