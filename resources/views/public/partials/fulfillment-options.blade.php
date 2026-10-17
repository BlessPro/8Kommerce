@php
    $fulfillmentOptions = collect($profile->resolvedFulfillmentOptions());
    $selectedFulfillment = old('fulfillment_method', data_get($fulfillmentOptions->first(), 'key'));
@endphp

@if($fulfillmentOptions->isNotEmpty())
    <fieldset class="storefront-checkout-fulfillment sm:col-span-full" data-fulfillment-options data-base-total="{{ $baseTotal ?? '0.00' }}" data-currency="{{ $currency }}">
        <legend>How would you like to receive your order?</legend>
        <div class="storefront-checkout-fulfillment-grid">
            @foreach($fulfillmentOptions as $option)
                <label>
                    <input type="radio" name="fulfillment_method" value="{{ $option['key'] }}" data-fulfillment-fee="{{ $option['fee'] }}" data-requires-address="{{ $option['type'] === 'delivery' ? '1' : '0' }}" @checked($selectedFulfillment === $option['key']) required>
                    <span class="storefront-checkout-fulfillment-copy"><strong>{{ $option['type'] === 'pickup' ? 'Pickup station' : 'Delivery area' }}</strong><small>{{ $option['label'] }}</small>@if($option['details'])<em>{{ $option['details'] }}</em>@endif</span>
                    <b>{{ (float) $option['fee'] > 0 ? \App\Support\Money::format($option['fee'], $currency) : 'Free' }}</b>
                </label>
            @endforeach
        </div>
        @error('fulfillment_method') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </fieldset>
@endif

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-fulfillment-options]').forEach(function (group) {
                const form = group.closest('form');
                const address = form?.querySelector('[data-fulfillment-address]');
                const button = form?.querySelector('[data-cart-checkout-button], [data-fulfillment-pay-button]');

                function formatAmount(amount) {
                    return (group.dataset.currency || 'GHS') + ' ' + Number(amount || 0).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                function syncFulfillment() {
                    const selected = group.querySelector('input[name="fulfillment_method"]:checked');
                    const requiresAddress = selected?.dataset.requiresAddress === '1';
                    if (address) {
                        address.required = requiresAddress;
                        address.placeholder = requiresAddress ? 'Delivery address or nearest landmark' : 'Additional location details (optional)';
                        const wrapper = address.closest('div');
                        if (wrapper) wrapper.hidden = !requiresAddress;
                        else address.hidden = !requiresAddress;
                    }
                    if (button && selected) {
                        const total = Number(group.dataset.baseTotal || 0) + Number(selected.dataset.fulfillmentFee || 0);
                        const prefix = button.dataset.cartCheckoutPrefix || button.dataset.payLabel || 'Pay now';
                        button.textContent = prefix.replace(/\s*[·•]\s*$/, '') + ' · ' + formatAmount(total);
                    }
                }

                group.syncFulfillment = syncFulfillment;
                group.querySelectorAll('input[name="fulfillment_method"]').forEach(function (control) {
                    control.addEventListener('change', syncFulfillment);
                });
                syncFulfillment();
            });
        });
    </script>
@endonce

