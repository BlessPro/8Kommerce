@php
    $title = 'Track your orders';
@endphp
@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-lg">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">One place for every order</span>
        <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-950">Track your orders</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Enter the phone number you paid with. We will send a one-time verification code.</p>

        @if($errors->has('credentials'))
            <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first('credentials') }}</div>
        @endif
        @if(session('tracking-otp-sent'))
            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('tracking-otp-sent') }}</div>
        @endif

        <form method="POST" action="{{ route('customer.orders.send-otp') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="phone_number" class="mb-1.5 block text-sm font-medium text-slate-700">Phone number</label>
                <input id="phone_number" name="phone_number" value="{{ old('phone_number') }}" inputmode="tel" autocomplete="tel" placeholder="054 190 0229" required class="w-full rounded-2xl border-slate-200 px-4 py-3 focus:border-emerald-500 focus:ring-emerald-500">
                @error('phone_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <button class="w-full rounded-2xl bg-slate-950 px-5 py-3.5 text-sm font-semibold text-white hover:bg-emerald-700">Send verification code</button>
        </form>

        @if(session('tracking-otp-sent') || old('phone_number'))
            <form method="POST" action="{{ route('customer.orders.verify') }}" class="mt-6 space-y-4 border-t border-slate-100 pt-5">
                @csrf
                <input type="hidden" name="phone_number" value="{{ old('phone_number') }}">
                <div>
                    <label for="otp" class="mb-1.5 block text-sm font-medium text-slate-700">Verification code</label>
                    <input id="otp" name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="6 digits" required autofocus class="w-full rounded-2xl border-slate-200 px-4 py-3 tracking-[0.35em] focus:border-emerald-500 focus:ring-emerald-500">
                    @error('otp')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600"><input type="hidden" name="remember" value="0"><input type="checkbox" name="remember" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">Trust this browser for 30 days</label>
                <button class="w-full rounded-2xl bg-emerald-600 px-5 py-3.5 text-sm font-semibold text-white hover:bg-emerald-500">Verify and view orders</button>
                <p class="text-center text-xs text-slate-500">The code expires in 5 minutes. You can request another after 60 seconds.</p>
            </form>
        @endif
    </div>
    <p class="mt-5 text-center text-sm text-slate-500">Have an order reference? <a href="{{ route('public.orders.track') }}" class="font-semibold text-emerald-700 hover:underline">Use the reference tracker</a></p>
</div>
@endsection
