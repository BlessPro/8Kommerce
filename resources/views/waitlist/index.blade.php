<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#003B49">
    <title>Join the 8Kommerce early-access list</title>
    <meta name="description" content="Join the 8Kommerce early-access list and be among the first businesses invited to sell, manage orders and grow from one practical workspace.">
    <link rel="icon" href="/favicon.svg?v=3" type="image/svg+xml">
    <link rel="icon" href="/favicon-32x32.png?v=3" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=3" sizes="180x180">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:600,700,800&family=public-sans:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#003B49] font-sans text-white antialiased">
    <main class="relative min-h-screen overflow-hidden">
        <div class="pointer-events-none absolute inset-0 opacity-70" style="background:radial-gradient(circle at 12% 18%,rgba(194,24,91,.30),transparent 25rem),radial-gradient(circle at 88% 65%,rgba(61,164,148,.22),transparent 30rem)"></div>
        <div class="pointer-events-none absolute -right-24 top-24 h-72 w-72 rotate-12 border-[42px] border-white/[.035]"></div>

        <div class="relative mx-auto flex min-h-screen max-w-[1480px] flex-col px-5 py-5 sm:px-8 lg:px-12">
            <header class="flex items-center justify-between border-b border-white/10 pb-5">
                <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="8Kommerce home">
                    <x-brand-logo :reversed="true" class="h-8 text-lg" />
                    <small class="hidden text-[10px] font-semibold uppercase tracking-[.2em] text-white/45 sm:block">Early access</small>
                </a>
                <div class="flex items-center gap-2 text-xs font-semibold">
                    <a href="{{ route('promoters.index') }}" class="rounded-md border border-white/15 px-3 py-2.5 text-white/75 transition hover:border-white/30 hover:text-white">Promoter access</a>
                    <a href="{{ route('seller.login') }}" class="hidden px-3 py-2.5 text-white/45 transition hover:text-white sm:block">Existing seller</a>
                </div>
            </header>

            <section class="grid flex-1 gap-12 py-10 lg:grid-cols-[1.08fr_.72fr] lg:items-center lg:gap-20 lg:py-16">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-[#E53572]/35 bg-[#E53572]/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[.15em] text-[#FFB9CF]"><span class="h-1.5 w-1.5 rounded-full bg-[#E53572]"></span>Built with Ghanaian businesses</div>
                    <h1 class="mt-7 max-w-4xl font-display text-[clamp(2.8rem,7vw,6.8rem)] font-semibold leading-[.9] tracking-[-.065em]">Business is already hard.<br><span class="text-[#FFDFD0]">The tools should not be.</span></h1>
                    <p class="mt-7 max-w-2xl text-base leading-7 text-white/60 sm:text-lg sm:leading-8">8Kommerce is becoming the practical command centre for the seller who runs a shop, answers WhatsApp, checks stock, confirms payments and still has customers asking where their order is.</p>

                    <div class="mt-10">
                        <p class="text-xs font-bold uppercase tracking-[.18em] text-[#95C9D1]">Does this look like you?</p>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach([
                                ['01','Orders are buried in chats','A paid order can disappear between screenshots, voice notes and unread messages.'],
                                ['02','Stock lives in your head','You only discover an item is finished after a customer has already chosen it.'],
                                ['03','Customers keep asking for updates','Every “where is my order?” takes time away from selling and fulfilment.'],
                                ['04','The numbers never meet','Products, payments, delivery and customer records live in different places.'],
                            ] as $problem)
                                <article class="group border border-white/10 bg-white/[.045] p-4 backdrop-blur-sm transition hover:border-[#E53572]/35 hover:bg-white/[.07]">
                                    <div class="flex items-start gap-4"><span class="pt-1 text-[10px] font-bold tracking-widest text-[#E53572]">{{ $problem[0] }}</span><div><h2 class="font-display text-base font-semibold text-white">{{ $problem[1] }}</h2><p class="mt-1.5 text-sm leading-6 text-white/48">{{ $problem[2] }}</p></div></div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>

                <aside class="relative">
                    <div class="absolute -inset-4 rotate-2 border border-[#E53572]/25 bg-[#E53572]/5"></div>
                    <div class="relative border border-white/15 bg-[#F8F2E8] p-6 text-[#073B4C] shadow-2xl shadow-black/25 sm:p-9">
                        <div class="flex items-start justify-between gap-5">
                            <div><p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-[#C2185B]">Private launch list</p><h2 class="mt-3 font-display text-3xl font-semibold leading-tight tracking-[-.04em]">Be first when the doors open.</h2></div>
                            <x-brand-mark class="h-12 w-12" />
                        </div>

                        @if($inviterName)
                            <div class="mt-6 border-l-2 border-[#C2185B] bg-[#F4E6E8] px-4 py-3 text-sm text-[#7A1F3D]"><strong>{{ $inviterName }}</strong> invited you to join the 8Kommerce early-access list.</div>
                        @endif

                        @if(session('status') === 'waitlist-joined')
                            <div class="mt-7 border border-emerald-200 bg-emerald-50 p-5" role="status">
                                <div class="flex gap-3"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-emerald-700 text-sm font-bold text-white">✓</span><div><strong class="block text-emerald-950">You are on the list.</strong><p class="mt-1 text-sm leading-6 text-emerald-800">We will contact you on this number when your 8Kommerce access is ready. You do not need to submit again.</p></div></div>
                            </div>
                        @else
                            <p class="mt-4 text-sm leading-6 text-slate-600">Leave the number you use for business. We will send your invitation when the next group is ready.</p>
                            <form method="POST" action="{{ route('waitlist.store') }}" class="mt-7" data-form-draft="8kommerce-waitlist">
                                @csrf
                                <input type="hidden" name="source" value="{{ old('source', $source) }}">
                                <label for="waitlist-phone" class="text-xs font-bold uppercase tracking-[.12em] text-[#073B4C]">Your WhatsApp or mobile number</label>
                                <div class="mt-2 flex border border-[#B9C9C8] bg-white focus-within:border-[#C2185B] focus-within:ring-2 focus-within:ring-[#C2185B]/15">
                                    <span class="grid min-h-14 place-items-center border-r border-[#D7E1DF] px-4 text-sm font-bold text-[#073B4C]">+233</span>
                                    <input id="waitlist-phone" name="phone_number" value="{{ old('phone_number') }}" class="min-w-0 flex-1 border-0 bg-transparent px-4 text-base text-slate-950 outline-none ring-0 placeholder:text-slate-400 focus:ring-0" placeholder="54 190 0229" inputmode="tel" autocomplete="tel" data-strip-leading-zero="true" autofocus required>
                                </div>
                                @error('phone_number')<p class="mt-2 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                                <label class="mt-4 flex items-start gap-3 text-xs leading-5 text-slate-500"><input type="checkbox" name="consent" value="1" class="mt-0.5 rounded border-slate-300 text-[#C2185B] focus:ring-[#C2185B]" required><span>8Kommerce may contact me about early access. My number will not be sold or displayed publicly.</span></label>
                                @error('consent')<p class="mt-2 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
                                <button type="submit" class="mt-6 inline-flex min-h-14 w-full items-center justify-between bg-[#C2185B] px-5 text-sm font-bold text-white transition hover:bg-[#A9134D]">Join the waitlist <span aria-hidden="true">→</span></button>
                            </form>
                        @endif

                        <div class="mt-7 grid grid-cols-3 gap-px bg-[#D7E1DF] text-center"><div class="bg-[#F8F2E8] px-2 py-3"><strong class="block text-sm">One link</strong><span class="text-[10px] text-slate-500">for your shop</span></div><div class="bg-[#F8F2E8] px-2 py-3"><strong class="block text-sm">Live stock</strong><span class="text-[10px] text-slate-500">as you sell</span></div><div class="bg-[#F8F2E8] px-2 py-3"><strong class="block text-sm">Order clarity</strong><span class="text-[10px] text-slate-500">for everyone</span></div></div>
                    </div>
                </aside>
            </section>

            <footer class="flex flex-col gap-3 border-t border-white/10 py-5 text-xs text-white/35 sm:flex-row sm:items-center sm:justify-between"><span>© {{ now()->year }} 8Kommerce. Simpler business ahead.</span><span><a href="{{ route('legal.privacy') }}" class="hover:text-white">Privacy</a><span class="mx-2">·</span><a href="{{ route('promoters.index') }}" class="hover:text-white">Earn reach as a promoter</a></span></footer>
        </div>
    </main>
    @include('components.page-loader')
</body>
</html>
