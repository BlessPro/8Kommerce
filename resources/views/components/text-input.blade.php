@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'lp-form-control']) }}>
