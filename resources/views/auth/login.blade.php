<x-guest-layout>
    @php($phoneAuthEnabled = (bool) config('auth_phone.enabled', true))

    <div class="auth-login-content">
        <header class="flex items-center justify-between">
            <a href="/" class="inline-flex items-center gap-2.5 text-slate-950" aria-label="8Kommerce home">
                <x-brand-logo class="h-8 text-[15px]" />
            </a>
            <a href="{{ route('register') }}" class="text-sm font-semibold text-[#073B4C] hover:text-[#032B3A] lg:hidden">Create account</a>
        </header>

        <div class="auth-login-main">
            <div class="mx-auto w-full max-w-[410px]">
                <div class="text-center">
                    <p class="lp-eyebrow text-[#7A1F3D]">Seller workspace</p>
                    <h1 class="mt-3 text-[28px] font-semibold tracking-[-0.025em] text-slate-950 sm:text-[32px]">Welcome back</h1>
                    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">Sign in with your business phone number and PIN.</p>
                </div>

                <nav class="auth-login-switcher mt-7" aria-label="Authentication options">
                    <span aria-current="page">Sign in</span>
                    <a href="{{ route('register') }}">Create account</a>
                </nav>

                <x-auth-session-status class="mt-5 rounded-md border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

                <form class="mt-6 space-y-5" method="POST" action="{{ route('login.phone.pin') }}">
                    @csrf
                    <div>
                        <label for="login-phone" class="auth-field-label">Phone number <span>*</span></label>
                        <div class="auth-phone-control mt-2">
                            <span class="auth-field-icon" aria-hidden="true">
                                <svg viewBox="0 0 20 20" fill="none"><path d="M6.5 3.5h2l1 3-1.4 1.4a11 11 0 0 0 4 4l1.4-1.4 3 1v2a2 2 0 0 1-2 2C8.4 15.5 4.5 11.6 4.5 5.5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <select name="phone_country" aria-label="Country code"><option value="+233">GH +233</option></select>
                            <span class="auth-phone-divider" aria-hidden="true"></span>
                            <input id="login-phone" name="phone_number" value="{{ old('phone_number', session('phone_login_pending_phone')) }}" placeholder="54 190 0229" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" data-strip-leading-zero="true" autofocus required />
                        </div>
                        <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="pin-phone" class="auth-field-label">PIN <span>*</span></label>
                            @if($phoneAuthEnabled)
                                <button type="button" id="toggle-pin-reset" class="text-xs font-semibold text-[#073B4C] hover:text-[#032B3A]">Forgot PIN?</button>
                            @else
                                <span class="text-xs text-slate-400">4 digits</span>
                            @endif
                        </div>
                        <div class="auth-pin-control mt-2">
                            <span class="auth-field-icon" aria-hidden="true">
                                <svg viewBox="0 0 20 20" fill="none"><rect x="4.5" y="8" width="11" height="8" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M7 8V6a3 3 0 0 1 6 0v2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                            </span>
                            <input id="pin-phone" name="pin" type="password" placeholder="••••" inputmode="numeric" pattern="[0-9]{4,6}" minlength="4" maxlength="6" autocomplete="current-password" required />
                            <button type="button" id="toggle-pin-visibility" class="auth-password-toggle" aria-label="Show PIN" aria-pressed="false">
                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M2.5 10s2.5-4 7.5-4 7.5 4 7.5 4-2.5 4-7.5 4-7.5-4-7.5-4Z" stroke="currentColor" stroke-width="1.4"/><circle cx="10" cy="10" r="2" stroke="currentColor" stroke-width="1.4"/></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('pin')" class="mt-2" />
                    </div>

                    <button type="submit" class="lp-button-brand min-h-[46px] w-full">Sign in securely</button>
                </form>

                @if($phoneAuthEnabled)
                    <div id="pin-reset-panel" class="auth-reset-panel mt-5 hidden">
                        <div class="mb-4">
                            <h2 class="text-sm font-semibold text-slate-900">Reset your PIN</h2>
                            <p class="mt-1 text-xs leading-5 text-slate-500">We’ll send a one-time code to your business phone.</p>
                        </div>

                        @if (session('pin_reset_status') === 'sent')
                            <div class="mb-4 rounded-md border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">PIN reset OTP sent to {{ session('pin_reset_phone_masked', 'your phone number') }}.</div>
                        @endif

                        <form class="space-y-3" method="POST" action="{{ route('login.phone.pin.reset.send') }}">
                            @csrf
                            <label class="auth-field-label">Business phone number</label>
                            <div class="flex gap-2">
                                <select name="phone_country" class="w-24 px-3 py-2.5 text-sm"><option value="+233">+233</option></select>
                                <input name="phone_number" value="{{ old('phone_number', session('pin_reset_pending_phone')) }}" class="w-full px-3 py-2.5" placeholder="054 190 0229" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" data-strip-leading-zero="true" />
                            </div>
                            <x-input-error :messages="$errors->get('reset_phone_number')" class="mt-1" />
                            <button type="submit" class="lp-button-secondary w-full">Send reset OTP</button>
                        </form>

                        <form class="mt-5 space-y-3 border-t border-slate-200 pt-5" method="POST" action="{{ route('login.phone.pin.reset.complete') }}">
                            @csrf
                            <input type="hidden" name="phone_country" value="{{ old('phone_country', session('pin_reset_pending_country', '+233')) }}" />
                            <div><label class="auth-field-label">Business phone number</label><input name="phone_number" value="{{ old('phone_number', session('pin_reset_pending_phone')) }}" class="mt-1.5 w-full px-3 py-2.5" placeholder="054 190 0229" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" data-strip-leading-zero="true" /><x-input-error :messages="$errors->get('reset_phone_number')" class="mt-1" /></div>
                            <div><label for="reset_otp" class="auth-field-label">OTP</label><input id="reset_otp" name="otp" value="{{ old('otp') }}" class="mt-1.5 w-full px-3 py-2.5" placeholder="123456" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" /><x-input-error :messages="$errors->get('reset_otp')" class="mt-1" /></div>
                            <div class="grid grid-cols-2 gap-3">
                                <div><label for="reset_pin" class="auth-field-label">New PIN</label><input id="reset_pin" name="reset_pin" class="mt-1.5 w-full px-3 py-2.5" type="password" placeholder="4 digits" inputmode="numeric" pattern="[0-9]{4}" minlength="4" maxlength="4" autocomplete="new-password" /><x-input-error :messages="$errors->get('reset_pin')" class="mt-1" /></div>
                                <div><label for="reset_pin_confirmation" class="auth-field-label">Confirm PIN</label><input id="reset_pin_confirmation" name="reset_pin_confirmation" class="mt-1.5 w-full px-3 py-2.5" type="password" placeholder="Repeat PIN" inputmode="numeric" pattern="[0-9]{4}" minlength="4" maxlength="4" autocomplete="new-password" /></div>
                            </div>
                            <button type="submit" class="lp-button-brand w-full">Verify and reset PIN</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <footer class="mt-10 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-[11px] text-slate-400 lg:justify-start">
            <span>© {{ date('Y') }} 8Kommerce</span>
            <a href="{{ route('legal.terms') }}" class="hover:text-slate-700">Terms</a>
            <a href="{{ route('legal.privacy') }}" class="hover:text-slate-700">Privacy</a>
        </footer>
    </div>

    <script>
        const togglePinReset = document.getElementById('toggle-pin-reset');
        const pinResetPanel = document.getElementById('pin-reset-panel');
        const pinInput = document.getElementById('pin-phone');
        const togglePinVisibility = document.getElementById('toggle-pin-visibility');
        const shouldOpenReset = @json($phoneAuthEnabled && (session('pin_reset_status') || $errors->has('reset_phone_number') || $errors->has('reset_otp') || $errors->has('reset_pin')));

        if (togglePinReset && pinResetPanel) {
            if (shouldOpenReset) pinResetPanel.classList.remove('hidden');
            togglePinReset.addEventListener('click', () => pinResetPanel.classList.toggle('hidden'));
        }

        if (togglePinVisibility && pinInput) {
            togglePinVisibility.addEventListener('click', () => {
                const showing = pinInput.type === 'text';
                pinInput.type = showing ? 'password' : 'text';
                togglePinVisibility.setAttribute('aria-pressed', String(!showing));
                togglePinVisibility.setAttribute('aria-label', showing ? 'Show PIN' : 'Hide PIN');
            });
        }
    </script>
</x-guest-layout>
