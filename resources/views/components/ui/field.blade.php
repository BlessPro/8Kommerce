@props(['label', 'name', 'help' => null, 'required' => false, 'error' => null])

<div {{ $attributes }}>
    <label for="{{ $name }}" class="lp-form-label">
        {{ $label }}
        @if($required)<span class="text-red-700" aria-hidden="true">*</span><span class="sr-only"> required</span>@endif
    </label>
    {{ $slot }}
    @if($help && !$error)<p id="{{ $name }}-help" class="lp-form-help">{{ $help }}</p>@endif
    @if($error)<p id="{{ $name }}-error" class="lp-form-error flex items-start gap-1.5" role="alert"><x-ui.icon name="alert-circle" size="xs" />{{ $error }}</p>@endif
</div>
