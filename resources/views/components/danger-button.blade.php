<button {{ $attributes->merge(['type' => 'submit', 'class' => 'lp-button lp-button-danger']) }}>
    {{ $slot }}
</button>
