<button {{ $attributes->merge(['type' => 'button', 'class' => 'lp-button-secondary disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
