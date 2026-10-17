<x-guest-layout>
    <div class="flex items-center justify-between gap-4">
        <a href="/" class="inline-flex items-center gap-2" aria-label="8Kommerce home">
            <x-brand-logo class="h-9 text-sm" />
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-xs font-semibold text-[#073B4C] hover:text-[#032B3A]">Sign out</button>
        </form>
    </div>

    <div class="mt-8">
        <div class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-[#EAF1F3] text-[#073B4C]" aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v2" stroke-linecap="round"/></svg>
        </div>
        <p class="lp-eyebrow mt-5 text-[#7A1F3D]">Secure your account</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.03em] text-slate-950">Create your 4-digit PIN</h1>
        <p class="mt-3 max-w-lg text-sm leading-6 text-slate-500">You’ll use this PIN with your phone number whenever you sign in or approve sensitive account changes.</p>
    </div>

    <x-auth-session-status class="mt-6 rounded-md border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <form class="mt-7 space-y-5" method="POST" action="{{ route('pin.setup.store') }}" data-pin-setup-form>
        @csrf

        <div>
            <div class="flex items-center justify-between gap-4">
                <label for="pin" class="auth-field-label">New PIN <span>*</span></label>
                <span class="text-xs text-slate-400">Exactly 4 numbers</span>
            </div>
            <div class="relative mt-2">
                <input id="pin" name="pin" class="w-full rounded-md border-slate-300 bg-white px-4 py-3.5 pr-14 text-center font-mono text-2xl font-semibold tracking-[0.75em] text-slate-900 focus:border-[#073B4C] focus:ring-[#073B4C]" type="password" placeholder="••••" inputmode="numeric" pattern="[0-9]{4}" minlength="4" maxlength="4" autocomplete="new-password" aria-describedby="pin-guidance" required />
                <button type="button" class="absolute inset-y-0 right-0 inline-flex w-12 items-center justify-center text-slate-500 hover:text-[#073B4C]" data-toggle-pin="pin" aria-label="Show PIN" aria-pressed="false">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M2.5 10s2.5-4 7.5-4 7.5 4 7.5 4-2.5 4-7.5 4-7.5-4-7.5-4Z" stroke="currentColor" stroke-width="1.4"/><circle cx="10" cy="10" r="2" stroke="currentColor" stroke-width="1.4"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('pin')" class="mt-2" />
        </div>

        <div>
            <label for="pin_confirmation" class="auth-field-label">Confirm PIN <span>*</span></label>
            <div class="relative mt-2">
                <input id="pin_confirmation" name="pin_confirmation" class="w-full rounded-md border-slate-300 bg-white px-4 py-3.5 pr-14 text-center font-mono text-2xl font-semibold tracking-[0.75em] text-slate-900 focus:border-[#073B4C] focus:ring-[#073B4C]" type="password" placeholder="••••" inputmode="numeric" pattern="[0-9]{4}" minlength="4" maxlength="4" autocomplete="new-password" required />
                <button type="button" class="absolute inset-y-0 right-0 inline-flex w-12 items-center justify-center text-slate-500 hover:text-[#073B4C]" data-toggle-pin="pin_confirmation" aria-label="Show confirmation PIN" aria-pressed="false">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M2.5 10s2.5-4 7.5-4 7.5 4 7.5 4-2.5 4-7.5 4-7.5-4-7.5-4Z" stroke="currentColor" stroke-width="1.4"/><circle cx="10" cy="10" r="2" stroke="currentColor" stroke-width="1.4"/></svg>
                </button>
            </div>
        </div>

        <div id="pin-guidance" class="rounded-md border border-[#D5E1E4] bg-[#F9FBFB] px-4 py-3">
            <p class="text-xs font-semibold text-slate-700">Choose something only you will know.</p>
            <p class="mt-1 text-xs leading-5 text-slate-500">Avoid repeated digits and obvious sequences such as 0000 or 1234. We never send your PIN by SMS.</p>
        </div>

        <button type="submit" class="lp-button-brand min-h-[46px] w-full">Save PIN and continue</button>
    </form>

    <script>
        document.querySelectorAll('[data-pin-setup-form] input[inputmode="numeric"]').forEach((input) => {
            input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '').slice(0, 4);
            });
        });

        document.querySelectorAll('[data-toggle-pin]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.togglePin);
                const reveal = input.type === 'password';
                input.type = reveal ? 'text' : 'password';
                button.setAttribute('aria-pressed', String(reveal));
                button.setAttribute('aria-label', reveal ? 'Hide PIN' : 'Show PIN');
            });
        });
    </script>
</x-guest-layout>
