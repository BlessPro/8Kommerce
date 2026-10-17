@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border-slate-300 text-sm shadow-none focus:border-emerald-700 focus:ring-emerald-700']) }}>
