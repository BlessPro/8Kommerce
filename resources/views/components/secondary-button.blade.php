<button {{ $attributes->merge(['type' => 'button', 'class' => 'lp-button lp-button-secondary']) }}>
    {{ $slot }}
</button>
