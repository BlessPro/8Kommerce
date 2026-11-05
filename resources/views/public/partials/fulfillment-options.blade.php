@php
    $fulfillmentOptions = collect($profile->resolvedFulfillmentOptions());
    $selectedFulfillment = old('fulfillment_method', data_get($fulfillmentOptions->first(), 'key'));
    $selectedOption = $fulfillmentOptions->firstWhere('key', $selectedFulfillment) ?? $fulfillmentOptions->first();
@endphp

@if($fulfillmentOptions->isNotEmpty())
    <fieldset class="storefront-checkout-fulfillment sm:col-span-full" data-fulfillment-options data-base-total="{{ $baseTotal ?? '0.00' }}" data-currency="{{ $currency }}">
        <div class="storefront-checkout-fulfillment-head">
            <div><span>Delivery or pickup</span><legend>How would you like to receive your order?</legend></div>
            <small>Required before payment</small>
        </div>
        <div class="storefront-checkout-fulfillment-grid">
            @foreach($fulfillmentOptions as $option)
                <label>
                    <input type="radio" name="fulfillment_method" value="{{ $option['key'] }}" data-fulfillment-fee="{{ $option['fee'] }}" data-fulfillment-type="{{ $option['type'] }}" data-fulfillment-label="{{ $option['label'] }}" data-requires-address="{{ $option['type'] === 'delivery' ? '1' : '0' }}" @checked($selectedFulfillment === $option['key']) required>
                    <span class="storefront-checkout-fulfillment-icon" aria-hidden="true">
                        @if($option['type'] === 'delivery')
                            <svg viewBox="0 0 24 24"><path d="M3 6h11v10H3zM14 10h4l3 3v3h-7zM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM18 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg>
                        @else
                            <svg viewBox="0 0 24 24"><path d="M4 9h16v11H4zM2 5h20v4H2zM9 13h6M12 9v11"/></svg>
                        @endif
                    </span>
                    <span class="storefront-checkout-fulfillment-copy"><strong>{{ $option['type'] === 'pickup' ? 'Pickup station' : 'Delivery area' }}</strong><small>{{ $option['label'] }}</small>@if($option['details'])<em>{{ $option['details'] }}</em>@endif</span>
                    <b>{{ (float) $option['fee'] > 0 ? \App\Support\Money::format($option['fee'], $currency) : 'Free' }}</b>
                </label>
            @endforeach
        </div>
        <div class="storefront-checkout-total" aria-live="polite">
            <div><span>Items subtotal</span><strong data-fulfillment-subtotal>{{ \App\Support\Money::format($baseTotal ?? '0.00', $currency) }}</strong></div>
            <div><span data-fulfillment-fee-label>{{ ($selectedOption['type'] ?? null) === 'pickup' ? 'Pickup fee' : 'Delivery fee' }}</span><strong data-fulfillment-fee>{{ (float) ($selectedOption['fee'] ?? 0) > 0 ? \App\Support\Money::format($selectedOption['fee'], $currency) : 'Free' }}</strong></div>
            <div class="storefront-checkout-total-due"><span>Total due now</span><strong data-fulfillment-total>{{ \App\Support\Money::format(\App\Support\Money::add((string) ($baseTotal ?? '0.00'), (string) ($selectedOption['fee'] ?? '0.00')), $currency) }}</strong></div>
        </div>
        <p class="storefront-checkout-selection" data-fulfillment-selection>{{ ($selectedOption['type'] ?? null) === 'pickup' ? 'Pickup from' : 'Delivering within' }} <strong>{{ $selectedOption['label'] ?? '' }}</strong></p>
        @error('fulfillment_method') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </fieldset>
@else
    <div class="storefront-checkout-fulfillment-empty sm:col-span-full" role="note">
        <span class="storefront-checkout-fulfillment-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 6h11v10H3zM14 10h4l3 3v3h-7zM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM18 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg></span>
        <div><strong>Delivery will be confirmed by the seller</strong><p>Add your location below. No delivery charge is included in this payment because the seller has not published a fixed delivery fee.</p></div>
    </div>
@endif

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-fulfillment-options]').forEach(function (group) {
                const form = group.closest('form');
                const address = form?.querySelector('[data-fulfillment-address]');
                const addressWrapper = form?.querySelector('[data-fulfillment-address-wrapper]');
                const button = form?.querySelector('[data-cart-checkout-button], [data-fulfillment-pay-button]');
                const subtotalOutput = group.querySelector('[data-fulfillment-subtotal]');
                const feeLabel = group.querySelector('[data-fulfillment-fee-label]');
                const feeOutput = group.querySelector('[data-fulfillment-fee]');
                const totalOutput = group.querySelector('[data-fulfillment-total]');
                const selectionOutput = group.querySelector('[data-fulfillment-selection]');

                function formatAmount(amount) {
                    return (group.dataset.currency || 'GHS') + ' ' + Number(amount || 0).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                function syncFulfillment() {
                    const selected = group.querySelector('input[name="fulfillment_method"]:checked');
                    const requiresAddress = selected?.dataset.requiresAddress === '1';
                    if (address) {
                        address.required = requiresAddress;
                        address.placeholder = requiresAddress ? 'House number, street and nearest landmark' : 'Additional location details (optional)';
                        const wrapper = addressWrapper || address.closest('div');
                        if (wrapper) wrapper.hidden = !requiresAddress;
                        else address.hidden = !requiresAddress;
                    }
                    if (selected) {
                        const fee = Number(selected.dataset.fulfillmentFee || 0);
                        const subtotal = Number(group.dataset.baseTotal || 0);
                        const total = subtotal + fee;
                        const type = selected.dataset.fulfillmentType || 'delivery';
                        if (subtotalOutput) subtotalOutput.textContent = formatAmount(subtotal);
                        if (feeLabel) feeLabel.textContent = type === 'pickup' ? 'Pickup fee' : 'Delivery fee';
                        if (feeOutput) feeOutput.textContent = fee > 0 ? formatAmount(fee) : 'Free';
                        if (totalOutput) totalOutput.textContent = formatAmount(total);
                        if (selectionOutput) {
                            selectionOutput.textContent = type === 'pickup' ? 'Pickup from ' : 'Delivering within ';
                            const strong = document.createElement('strong');
                            strong.textContent = selected.dataset.fulfillmentLabel || '';
                            selectionOutput.appendChild(strong);
                        }
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
