<?php

return [
    'enabled' => filter_var(env('AUTH_PHONE_PIN_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'otp' => [
        'ttl_seconds' => (int) env('OTP_TTL_SECONDS', 600),
        'resend_cooldown_seconds' => (int) env('OTP_RESEND_COOLDOWN_SECONDS', 30),
        'max_verify_attempts' => (int) env('OTP_MAX_VERIFY_ATTEMPTS', 5),
        'lockout_seconds' => (int) env('OTP_LOCKOUT_SECONDS', 900),
        'length' => (int) env('OTP_LENGTH', 6),
        'queue_connection' => env('OTP_QUEUE_CONNECTION')
            ?: (
                env('APP_ENV', 'production') === 'local'
                && env('QUEUE_CONNECTION', 'sync') === 'database'
                    ? 'sync'
                    : null
            ),
    ],

    'pin' => [
        // Keep the PIN contract consistent across setup, sign-in, reset,
        // and sensitive account changes.
        'length' => 4,
        'max_login_attempts' => (int) env('PIN_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_seconds' => (int) env('PIN_LOCKOUT_SECONDS', 900),
        'enforce_weak_denylist' => filter_var(env('PIN_ENFORCE_WEAK_DENYLIST', true), FILTER_VALIDATE_BOOLEAN),
        'weak_values' => array_values(array_unique(array_merge(
            ['0000', '1111', '1234', '2222', '3333', '4444', '5555', '6789', '7777', '8888', '9876', '9999'],
            array_filter(array_map(
                static fn (string $value): string => trim($value),
                explode(',', (string) env('PIN_WEAK_VALUES', ''))
            ))
        ))),
    ],
];
