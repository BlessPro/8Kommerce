<button {{ $attributes->merge(['type' => 'submit', 'class' => 'lp-button-brand disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
