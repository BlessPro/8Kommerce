@props(['value'])

<label {{ $attributes->merge(['class' => 'lp-form-label']) }}>
    {{ $value ?? $slot }}
</label>
