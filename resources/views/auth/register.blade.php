<x-guest-layout>
    @php($phoneAuthEnabled = (bool) config('auth_phone.enabled', true))

    <div class="auth-login-content">
        <header class="flex items-center justify-between">
            <a href="/" class="inline-flex items-center gap-2.5 text-slate-950" aria-label="8Kommerce home">
                <x-brand-logo class="h-8 text-[15px]" />
            </a>
            <a href="{{ route('login') }}" class="text-sm font-semibold text-[#073B4C] hover:text-[#032B3A] lg:hidden">Sign in</a>
        </header>

        <div class="auth-login-main">
            <div class="mx-auto w-full max-w-[410px]">
                <div class="text-center">
                    <p class="lp-eyebrow text-[#7A1F3D]">Start selling</p>
                    <h1 class="mt-3 text-[28px] font-semibold tracking-[-0.025em] text-slate-950 sm:text-[32px]">Create your account</h1>
                    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">Verify your business phone number to set up your seller workspace.</p>
                </div>

                <nav class="auth-login-switcher mt-7" aria-label="Authentication options">
                    <a href="{{ route('login') }}">Sign in</a>
                    <span aria-current="page">Create account</span>
                </nav>

                @if(!$phoneAuthEnabled)
                    <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">Phone signup is currently unavailable. Please contact support.</div>
                @else
                    @if (session('register_otp_status') === 'sent')
                        <div class="mt-5 rounded-md border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">OTP sent to {{ session('register_otp_phone_masked', 'your phone number') }}.</div>
                    @endif

                    <form class="mt-6 space-y-4" method="POST" action="{{ route('register.phone.send') }}">
                        @csrf
                        <div>
                            <div class="flex items-center justify-between">
                                <label for="register-phone" class="auth-field-label">Business phone number <span>*</span></label>
                                <span class="text-xs text-slate-400">Step 1 of 2</span>
                            </div>
                            <div class="auth-phone-control mt-2">
                                <span class="auth-field-icon" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="none"><path d="M6.5 3.5h2l1 3-1.4 1.4a11 11 0 0 0 4 4l1.4-1.4 3 1v2a2 2 0 0 1-2 2C8.4 15.5 4.5 11.6 4.5 5.5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <select name="phone_country" aria-label="Country code">
                                    <option value="+233" @selected(old('phone_country', session('register_phone_pending_country', '+233')) === '+233')>GH +233</option>
                                </select>
                                <span class="auth-phone-divider" aria-hidden="true"></span>
                                <input id="register-phone" name="phone_number" value="{{ old('phone_number', session('register_phone_pending_phone')) }}" placeholder="54 190 0229" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" data-strip-leading-zero="true" autofocus required />
                            </div>
                            <p class="mt-2 text-xs text-slate-500">We’ll send a one-time verification code to this number.</p>
                            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                        </div>

                        <button type="submit" class="lp-button-secondary min-h-[44px] w-full">Send verification code</button>
                    </form>

                    <div class="auth-signup-divider"><span>Then verify your number</span></div>

                    <form class="space-y-4" method="POST" action="{{ route('register.phone.complete') }}">
                        @csrf
                        <input type="hidden" name="phone_country" value="{{ old('phone_country', session('register_phone_pending_country', '+233')) }}" />
                        <input type="hidden" name="phone_number" value="{{ old('phone_number', session('register_phone_pending_phone')) }}" />

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="otp" class="auth-field-label">Verification code <span>*</span></label>
                                <span class="text-xs text-slate-400">Step 2 of 2</span>
                            </div>
                            <div class="auth-pin-control mt-2">
                                <span class="auth-field-icon" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="none"><path d="M5 3.5h10a1.5 1.5 0 0 1 1.5 1.5v10a1.5 1.5 0 0 1-1.5 1.5H5A1.5 1.5 0 0 1 3.5 15V5A1.5 1.5 0 0 1 5 3.5Z" stroke="currentColor" stroke-width="1.4"/><path d="M7 8h6M7 12h3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                                </span>
                                <input id="otp" name="otp" value="{{ old('otp') }}" placeholder="Enter the code" inputmode="numeric" pattern="[0-9]*" minlength="4" maxlength="8" autocomplete="one-time-code" required />
                            </div>
                            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
                        </div>

                        <button type="submit" class="lp-button-brand min-h-[46px] w-full">Verify and create account</button>
                    </form>

                    <p class="mt-4 text-center text-xs leading-5 text-slate-400">By creating an account, you agree to our <a href="{{ route('legal.terms') }}" class="font-semibold text-slate-600 hover:text-[#073B4C]">Terms</a> and <a href="{{ route('legal.privacy') }}" class="font-semibold text-slate-600 hover:text-[#073B4C]">Privacy Policy</a>.</p>
                @endif
            </div>
        </div>

        <footer class="mt-8 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-[11px] text-slate-400 lg:justify-start">
            <span>© {{ date('Y') }} 8Kommerce</span>
            <a href="{{ route('legal.terms') }}" class="hover:text-slate-700">Terms</a>
            <a href="{{ route('legal.privacy') }}" class="hover:text-slate-700">Privacy</a>
        </footer>
    </div>
</x-guest-layout>
