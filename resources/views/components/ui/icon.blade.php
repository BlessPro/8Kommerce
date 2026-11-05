@props(['name', 'label' => null, 'size' => 'md'])

@php
    $icons = [
        'activity' => 'icon-[tabler--activity]',
        'alert-circle' => 'icon-[tabler--alert-circle]',
        'arrow-left' => 'icon-[tabler--arrow-left]',
        'arrow-right' => 'icon-[tabler--arrow-right]',
        'bell' => 'icon-[tabler--bell]',
        'building-store' => 'icon-[tabler--building-store]',
        'calendar' => 'icon-[tabler--calendar]',
        'chart-bar' => 'icon-[tabler--chart-bar]',
        'check' => 'icon-[tabler--check]',
        'chevron-down' => 'icon-[tabler--chevron-down]',
        'circle-check' => 'icon-[tabler--circle-check]',
        'circle-plus' => 'icon-[tabler--circle-plus]',
        'copy' => 'icon-[tabler--copy]',
        'credit-card' => 'icon-[tabler--credit-card-pay]',
        'dashboard' => 'icon-[tabler--layout-dashboard]',
        'download' => 'icon-[tabler--download]',
        'edit' => 'icon-[tabler--edit]',
        'external-link' => 'icon-[tabler--external-link]',
        'eye' => 'icon-[tabler--eye]',
        'eye-off' => 'icon-[tabler--eye-off]',
        'file-invoice' => 'icon-[tabler--file-invoice]',
        'filter' => 'icon-[tabler--filter]',
        'help' => 'icon-[tabler--help-circle]',
        'home' => 'icon-[tabler--home]',
        'info-circle' => 'icon-[tabler--info-circle]',
        'logout' => 'icon-[tabler--logout]',
        'menu' => 'icon-[tabler--menu-2]',
        'moon' => 'icon-[tabler--moon]',
        'package' => 'icon-[tabler--package]',
        'palette' => 'icon-[tabler--palette]',
        'phone' => 'icon-[tabler--phone]',
        'plus' => 'icon-[tabler--plus]',
        'receipt' => 'icon-[tabler--receipt]',
        'search' => 'icon-[tabler--search]',
        'settings' => 'icon-[tabler--settings]',
        'shield-lock' => 'icon-[tabler--shield-lock]',
        'shopping-bag' => 'icon-[tabler--shopping-bag]',
        'speakerphone' => 'icon-[tabler--speakerphone]',
        'sun' => 'icon-[tabler--sun]',
        'target' => 'icon-[tabler--target-arrow]',
        'ticket' => 'icon-[tabler--ticket]',
        'trash' => 'icon-[tabler--trash]',
        'truck' => 'icon-[tabler--truck-delivery]',
        'upload' => 'icon-[tabler--upload]',
        'user-circle' => 'icon-[tabler--user-circle]',
        'users' => 'icon-[tabler--users]',
        'warning' => 'icon-[tabler--alert-triangle]',
        'whatsapp' => 'icon-[tabler--brand-whatsapp]',
        'x' => 'icon-[tabler--x]',
    ];

    $sizes = [
        'xs' => 'h-3.5 w-3.5',
        'sm' => 'h-4 w-4',
        'md' => 'h-5 w-5',
        'lg' => 'h-6 w-6',
        'xl' => 'h-8 w-8',
    ];

    $iconClass = $icons[$name] ?? $icons['info-circle'];
@endphp

<span
    {{ $attributes->class(['inline-block shrink-0', $iconClass, $sizes[$size] ?? $sizes['md']]) }}
    @if($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif
></span>
