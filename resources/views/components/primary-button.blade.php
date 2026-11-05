<button {{ $attributes->merge(['type' => 'submit', 'class' => 'lp-button lp-button-brand']) }}>
    {{ $slot }}
</button>
