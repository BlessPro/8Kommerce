@php
    $title = 'Profile';
    $selectedCountry = old('phone_country', '+233');
    $displayPhone = old('phone_number');
    if (! $displayPhone && $profile->phone && str_starts_with($profile->phone, '+233')) {
        $displayPhone = '0'.substr($profile->phone, 4);
    }
    $payoutMethod = old('payout_method', $profile->payout_method ?? 'MOMO');
    $momoBanks = $momoBanks ?? [];
    $bankOptions = $banks ?? [];
    $momoBankOptions = $momoBanks;
    $fulfillmentOptions = old('fulfillment_options', $profile->resolvedFulfillmentOptions());
@endphp
@extends('layouts.dashboard')

@section('content')
    @if(request()->query('onboarding') === 'profile' || session('onboarding_required'))
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            <p class="font-semibold">Complete onboarding first</p>
            <p class="mt-1">Add your business name, phone, and email before continuing to products, payments, and insights.</p>
        </div>
    @endif
    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Seller profile</h2>
                <p class="mt-1 text-sm text-slate-600">Business details and payout setup.</p>
                <div class="mt-4">
                    @if($profile->paystack_subaccount_code)
                        <span class="rounded-full bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">Paystack Connected</span>
                    @else
                        <span class="rounded-full bg-amber-50 px-4 py-2 text-xs font-semibold text-amber-700">Paystack Not Connected</span>
                    @endif
                </div>

                @if($errors->has('paystack'))
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
                        {{ $errors->first('paystack') }}
                    </div>
                @endif
                @if(session('paystack_status'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('paystack_status') }}
                    </div>
                @endif

                @if(session('status') === 'profile-updated')
                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">Store settings saved.</div>
                @endif

                <form id="store-settings-form" method="POST" action="{{ route('profile.seller.update') }}" class="mt-6 space-y-5" data-resolve-account-url="{{ route('profile.seller.resolve-account') }}">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="text-sm font-medium text-slate-700">Business name</label>
                        <input name="business_name" value="{{ old('business_name', $profile->business_name) }}" required class="mt-2 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500" />
                        @error('business_name') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Phone</label>
                        <div class="mt-2 flex gap-2">
                            <select name="phone_country" class="w-28 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="+233" {{ $selectedCountry === '+233' ? 'selected' : '' }}>+233</option>
                            </select>
                            <input name="phone_number" value="{{ $displayPhone }}" placeholder="0541900229" class="flex-1 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500" data-strip-leading-zero="true" />
                        </div>
                        <p class="mt-2 text-xs text-slate-500">We remove the leading 0 and save 9 digits.</p>
                        @error('phone_number') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
                        @error('phone_country') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Paystack payout</p>
                        <p class="mt-2 text-xs text-slate-500">Fill in to connect your Paystack subaccount.</p>
                        <div class="mt-4 inline-flex overflow-hidden rounded-full border border-slate-200 bg-white text-xs font-semibold">
                            <label class="cursor-pointer px-4 py-2 {{ $payoutMethod === 'MOMO' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600' }}">
                                <input type="radio" name="payout_method" value="MOMO" class="hidden" {{ $payoutMethod === 'MOMO' ? 'checked' : '' }}>
                                Mobile Money
                            </label>
                            <label class="cursor-pointer px-4 py-2 {{ $payoutMethod === 'BANK' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600' }}">
                                <input type="radio" name="payout_method" value="BANK" class="hidden" {{ $payoutMethod === 'BANK' ? 'checked' : '' }}>
                                Bank
                            </label>
                        </div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label id="settlement-label" class="text-sm font-medium text-slate-700">MoMo provider</label>
                                    <select name="settlement_bank_code" id="settlement-bank-code" class="mt-2 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">Select provider</option>
                                        @foreach($momoBankOptions as $bank)
                                            @php
                                                $bankNameLower = strtolower($bank['name']);
                                                $network = 'UNKNOWN';
                                                if (str_contains($bankNameLower, 'mtn')) {
                                                    $network = 'MTN';
                                                } elseif (str_contains($bankNameLower, 'airtel') || str_contains($bankNameLower, 'tigo')) {
                                                    $network = 'AIRTELTIGO';
                                                } elseif (str_contains($bankNameLower, 'telecel') || str_contains($bankNameLower, 'vodafone')) {
                                                    $network = 'TELECEL';
                                                }
                                            @endphp
                                            <option
                                                value="{{ $bank['code'] }}"
                                                data-network="{{ $network }}"
                                                data-type="momo"
                                                {{ old('settlement_bank_code', $profile->settlement_bank_code) === $bank['code'] ? 'selected' : '' }}
                                            >
                                                {{ $bank['name'] }}
                                            </option>
                                        @endforeach
                                        @foreach($bankOptions as $bank)
                                            <option
                                                value="{{ $bank['code'] }}"
                                                data-network="UNKNOWN"
                                                data-type="bank"
                                                @if($payoutMethod !== 'BANK') hidden disabled @endif
                                                {{ old('settlement_bank_code', $profile->settlement_bank_code) === $bank['code'] && ! collect($momoBankOptions)->contains(fn($b) => $b['code'] === $bank['code']) ? 'selected' : '' }}
                                            >
                                                {{ $bank['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p data-provider-empty="momo" class="mt-2 text-xs text-amber-700" @if(!empty($momoBankOptions)) hidden @endif>MoMo providers are temporarily unavailable. Refresh the page and try again.</p>
                                    <p data-provider-empty="bank" class="mt-2 text-xs text-amber-700" @if(!empty($bankOptions) || $payoutMethod !== 'BANK') hidden @endif>Banks are temporarily unavailable. Refresh the page and try again.</p>
                                @error('settlement_bank_code') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label id="account-number-label" class="text-sm font-medium text-slate-700">Wallet number</label>
                                <input id="account-number" name="account_number" value="{{ old('account_number', $profile->account_number) }}" class="mt-2 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. 0541900229" />
                                <p id="momo-network-hint" class="mt-2 hidden text-xs text-slate-500"></p>
                                 <p id="account-resolution-status" class="mt-2 min-h-4 text-xs text-slate-500" aria-live="polite"></p>
                                @error('account_number') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-sm font-medium text-slate-700">Account name</label>
                                <input id="account-name" name="account_name" value="{{ old('account_name', $profile->account_name) }}" readonly placeholder="Verified name appears here" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-100 text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" />
                                @error('account_name') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-sm font-medium text-slate-700">Current login PIN</label>
                                <input name="current_pin" type="password" inputmode="numeric" autocomplete="current-password" class="mt-2 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Required when payout details change" />
                                <p class="mt-2 text-xs leading-5 text-slate-500">Your PIN confirms it is really you before we change where payouts are sent. It is checked securely by 8Kommerce and is never sent to Paystack.</p>
                                @error('current_pin') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </form>
                <form method="POST" action="{{ route('profile.seller.test') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="rounded-full border border-emerald-200 px-5 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                        Test Paystack connection
                    </button>
                </form>
                <div class="mt-6 rounded-xl border border-emerald-100 bg-emerald-50/70 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.3em] text-emerald-500">Public listing</p>
                    <a href="{{ route('public.listing', $profile->public_slug) }}" class="mt-2 block break-all text-sm font-semibold text-emerald-700 hover:text-emerald-600">
                        {{ route('public.listing', $profile->public_slug) }}
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div><p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Order fulfilment</p><h2 class="mt-1 text-lg font-semibold text-slate-900">Delivery and pickup</h2><p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600">Add the pickup stations and delivery areas customers can choose at checkout. Each option has its own fee and instructions.</p></div>
                    <button type="button" class="rounded-lg border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50" data-add-fulfillment-option>Add option</button>
                </div>

                <div class="mt-5" data-fulfillment-settings-form>
                    <div class="grid gap-3" data-fulfillment-option-list>
                        @foreach($fulfillmentOptions as $index => $option)
                            <section class="rounded-xl border border-slate-200 bg-slate-50/60 p-4" data-fulfillment-option>
                                <div class="grid gap-4 sm:grid-cols-[0.75fr_1.4fr_0.65fr_auto] sm:items-end">
                                    <div><label class="text-xs font-semibold text-slate-600">Option type</label><select form="store-settings-form" name="fulfillment_options[{{ $index }}][type]" class="mt-2 w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500"><option value="pickup" @selected(($option['type'] ?? '') === 'pickup')>Pickup station</option><option value="delivery" @selected(($option['type'] ?? '') === 'delivery')>Delivery area</option></select></div>
                                    <div><label class="text-xs font-semibold text-slate-600">Station or delivery area</label><input form="store-settings-form" name="fulfillment_options[{{ $index }}][label]" value="{{ $option['label'] ?? '' }}" maxlength="160" required placeholder="e.g. East Legon pickup or Greater Accra" class="mt-2 w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
                                    <div><label class="text-xs font-semibold text-slate-600">Fee ({{ $currency }})</label><input form="store-settings-form" name="fulfillment_options[{{ $index }}][fee]" value="{{ $option['fee'] ?? '0.00' }}" type="number" min="0" max="100000" step="0.01" inputmode="decimal" required class="mt-2 w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
                                    <button type="button" class="min-h-10 rounded-lg border border-rose-200 px-3 text-xs font-semibold text-rose-600 hover:bg-rose-50" data-remove-fulfillment-option>Remove</button>
                                </div>
                                <div class="mt-4"><label class="text-xs font-semibold text-slate-600">Details for customers <span class="font-normal text-slate-400">(optional)</span></label><textarea form="store-settings-form" name="fulfillment_options[{{ $index }}][details]" rows="2" maxlength="180" placeholder="Opening hours, landmarks, delivery time or special instructions" class="mt-2 w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ $option['details'] ?? '' }}</textarea></div>
                            </section>
                        @endforeach
                    </div>
                    <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-500" data-fulfillment-empty @if(count($fulfillmentOptions)) hidden @endif>No options yet. Add a pickup station or delivery area to show it at checkout.</div>
                    @if($errors->has('fulfillment_options') || $errors->has('fulfillment_options.*'))<p class="mt-2 text-xs text-rose-600">{{ $errors->first('fulfillment_options.*') ?: $errors->first('fulfillment_options') }}</p>@endif
                    <div class="mt-5"><p class="text-xs text-slate-500">Use a fee of 0 for a free option.</p></div>
                </div>

                <template data-fulfillment-option-template>
                    <section class="rounded-xl border border-slate-200 bg-slate-50/60 p-4" data-fulfillment-option>
                        <div class="grid gap-4 sm:grid-cols-[0.75fr_1.4fr_0.65fr_auto] sm:items-end">
                            <div><label class="text-xs font-semibold text-slate-600">Option type</label><select form="store-settings-form" name="fulfillment_options[__INDEX__][type]" class="mt-2 w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500"><option value="pickup">Pickup station</option><option value="delivery">Delivery area</option></select></div>
                            <div><label class="text-xs font-semibold text-slate-600">Station or delivery area</label><input form="store-settings-form" name="fulfillment_options[__INDEX__][label]" maxlength="160" required placeholder="e.g. East Legon pickup or Greater Accra" class="mt-2 w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
                            <div><label class="text-xs font-semibold text-slate-600">Fee ({{ $currency }})</label><input form="store-settings-form" name="fulfillment_options[__INDEX__][fee]" value="0.00" type="number" min="0" max="100000" step="0.01" inputmode="decimal" required class="mt-2 w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
                            <button type="button" class="min-h-10 rounded-lg border border-rose-200 px-3 text-xs font-semibold text-rose-600 hover:bg-rose-50" data-remove-fulfillment-option>Remove</button>
                        </div>
                        <div class="mt-4"><label class="text-xs font-semibold text-slate-600">Details for customers <span class="font-normal text-slate-400">(optional)</span></label><textarea form="store-settings-form" name="fulfillment_options[__INDEX__][details]" rows="2" maxlength="180" placeholder="Opening hours, landmarks, delivery time or special instructions" class="mt-2 w-full rounded-lg border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea></div>
                    </section>
                </template>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Account details</h2>
                <div class="mt-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Security</h2>
                <div class="mt-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
            <div class="rounded-2xl border border-rose-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-rose-600">Danger zone</h2>
                <div class="mt-4">
                    @include('profile.partials.data-deletion-request-form')
                </div>
                <div class="mt-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    <div class="fixed bottom-6 right-6 z-40 sm:bottom-8 sm:right-8">
        <button form="store-settings-form" type="submit" class="inline-flex min-h-12 items-center gap-2 rounded-lg bg-emerald-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 4h12l2 2v14H5z"/><path d="M8 4v6h8V4M8 20v-6h8v6"/></svg>
            Save store settings
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const storeSettingsForm = document.getElementById('store-settings-form');
            const accountInput = document.getElementById('account-number');
            const accountNameInput = document.getElementById('account-name');
            const resolutionStatus = document.getElementById('account-resolution-status');
            const hint = document.getElementById('momo-network-hint');
            const bankSelect = document.querySelector('select[name="settlement_bank_code"]');
            const payoutRadios = document.querySelectorAll('input[name="payout_method"]');
            const settlementLabel = document.getElementById('settlement-label');
            const accountLabel = document.getElementById('account-number-label');
            const providerEmptyMessages = document.querySelectorAll('[data-provider-empty]');

            const detectGhNetwork = (value) => {
                const digits = (value || '').replace(/\D+/g, '');
                if (!digits) return null;

                let local = digits;
                if (local.startsWith('233') && local.length >= 12) {
                    local = local.slice(3);
                }

                let prefix = null;
                if (local.length === 10 && local.startsWith('0')) {
                    prefix = local.slice(0, 3);
                } else if (local.length === 9) {
                    prefix = '0' + local.slice(0, 2);
                }
                if (!prefix) return null;

                const mtn = new Set(['024','025','053','054','055','059']);
                const telecel = new Set(['020','050']);
                const airteltigo = new Set(['026','027','056','057']);

                if (mtn.has(prefix)) return 'MTN MoMo';
                if (telecel.has(prefix)) return 'Telecel Cash';
                if (airteltigo.has(prefix)) return 'AirtelTigo Money';
                return null;
            };

            const getPayoutMethod = () => {
                const picked = Array.from(payoutRadios).find((r) => r.checked);
                return picked ? picked.value : 'MOMO';
            };

            const updateLabels = () => {
                const method = getPayoutMethod();
                if (settlementLabel) settlementLabel.textContent = method === 'BANK' ? 'Bank' : 'MoMo provider';
                if (accountLabel) accountLabel.textContent = method === 'BANK' ? 'Account number' : 'Wallet number';
                if (accountInput) accountInput.placeholder = method === 'BANK' ? 'e.g. bank account number' : 'e.g. 0541900229';
                if (bankSelect) {
                    const options = Array.from(bankSelect.options);
                    const selectedOption = bankSelect.selectedOptions[0];
                    options.forEach((option, index) => {
                        if (index === 0) {
                            option.textContent = method === 'BANK' ? 'Select bank' : 'Select provider';
                            return;
                        }
                        const type = option.dataset.type || 'bank';
                        const visible = method === 'BANK' ? type === 'bank' : type === 'momo';
                        option.hidden = !visible;
                        option.disabled = !visible;
                    });
                    if (selectedOption?.disabled) bankSelect.value = '';
                }
                providerEmptyMessages.forEach((message) => {
                    const relevant = message.dataset.providerEmpty === (method === 'BANK' ? 'bank' : 'momo');
                    const hasOptions = bankSelect && Array.from(bankSelect.options).some((option) => option.dataset.type === (method === 'BANK' ? 'bank' : 'momo'));
                    message.hidden = !relevant || hasOptions;
                });
            };

            const maybeAutoSelectBank = (networkLabel) => {
                if (!bankSelect || !networkLabel) return;

                const options = Array.from(bankSelect.options);
                const normalizedNetwork = networkLabel.toUpperCase().includes('MTN')
                    ? 'MTN'
                    : (networkLabel.toUpperCase().includes('AIRTEL') || networkLabel.toUpperCase().includes('TIGO')
                        ? 'AIRTELTIGO'
                        : (networkLabel.toUpperCase().includes('TELECEL') || networkLabel.toUpperCase().includes('VODAFONE') ? 'TELECEL' : 'UNKNOWN'));

                const best = options.find((o) => o.dataset.network === normalizedNetwork && !o.hidden && !o.disabled);
                if (best) {
                    bankSelect.value = best.value;
                }
            };

            const refresh = () => {
                if (!accountInput || !hint) return;
                const network = detectGhNetwork(accountInput.value);

                if (!network || getPayoutMethod() === 'BANK') {
                    hint.textContent = '';
                    hint.classList.add('hidden');
                    return;
                }

                hint.textContent = 'Detected MoMo network: ' + network;
                hint.classList.remove('hidden');
                maybeAutoSelectBank(network);
            };

            let resolutionTimer = null;
            let resolutionRequest = null;
            const setResolutionStatus = (message, tone = 'muted') => {
                if (!resolutionStatus) return;
                resolutionStatus.textContent = message;
                resolutionStatus.className = 'mt-2 min-h-4 text-xs ' + (tone === 'success'
                    ? 'text-emerald-700'
                    : tone === 'error' ? 'text-rose-600' : 'text-slate-500');
            };
            const scheduleAccountResolution = () => {
                window.clearTimeout(resolutionTimer);
                resolutionRequest?.abort();

                const digits = (accountInput?.value || '').replace(/\D+/g, '');
                const minimumLength = getPayoutMethod() === 'MOMO' ? 9 : 6;
                if (!bankSelect?.value || digits.length < minimumLength) {
                    setResolutionStatus(bankSelect?.value || !digits ? '' : 'Enter a complete account number.');
                    return;
                }

                setResolutionStatus('Checking account name…');
                resolutionTimer = window.setTimeout(async () => {
                    resolutionRequest = new AbortController();
                    try {
                        const response = await fetch(storeSettingsForm.dataset.resolveAccountUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({
                                payout_method: getPayoutMethod(),
                                settlement_bank_code: bankSelect.value,
                                account_number: accountInput.value,
                            }),
                            signal: resolutionRequest.signal,
                        });
                        const result = await response.json();
                        if (!response.ok || !result.account_name) throw new Error(result.message || 'Account could not be verified.');

                        accountNameInput.value = result.account_name;
                        setResolutionStatus('Verified account: ' + result.account_name, 'success');
                    } catch (error) {
                        if (error.name === 'AbortError') return;
                        accountNameInput.value = '';
                        setResolutionStatus(error.message || 'Account verification failed. Try again.', 'error');
                    }
                }, 650);
            };

            if (accountInput) {
                accountInput.addEventListener('input', () => {
                    accountNameInput.value = '';
                    refresh();
                    scheduleAccountResolution();
                });
                bankSelect?.addEventListener('change', () => {
                    accountNameInput.value = '';
                    scheduleAccountResolution();
                });
                Array.from(payoutRadios).forEach((r) => r.addEventListener('change', () => {
                    bankSelect.value = '';
                    accountNameInput.value = '';
                    setResolutionStatus('');
                    updateLabels();
                    refresh();
                    scheduleAccountResolution();
                }));
                updateLabels();
                refresh();
            }

            const fulfillmentList = document.querySelector('[data-fulfillment-option-list]');
            const fulfillmentTemplate = document.querySelector('[data-fulfillment-option-template]');
            const fulfillmentEmpty = document.querySelector('[data-fulfillment-empty]');
            let fulfillmentIndex = fulfillmentList?.querySelectorAll('[data-fulfillment-option]').length || 0;

            const refreshFulfillmentEmptyState = () => {
                if (fulfillmentEmpty && fulfillmentList) fulfillmentEmpty.hidden = fulfillmentList.children.length > 0;
            };
            document.querySelector('[data-add-fulfillment-option]')?.addEventListener('click', () => {
                if (!fulfillmentList || !fulfillmentTemplate) return;
                const markup = fulfillmentTemplate.innerHTML.replaceAll('__INDEX__', String(fulfillmentIndex++));
                fulfillmentList.insertAdjacentHTML('beforeend', markup);
                fulfillmentList.lastElementChild?.querySelector('input[name$="[label]"]')?.focus();
                refreshFulfillmentEmptyState();
            });
            fulfillmentList?.addEventListener('click', (event) => {
                const remove = event.target.closest('[data-remove-fulfillment-option]');
                if (!remove) return;
                remove.closest('[data-fulfillment-option]')?.remove();
                refreshFulfillmentEmptyState();
            });
            refreshFulfillmentEmptyState();
        });
    </script>
@endsection
