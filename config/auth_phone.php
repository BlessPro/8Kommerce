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
        'length' => (int) env('PIN_LENGTH', 6),
        'max_login_attempts' => (int) env('PIN_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_seconds' => (int) env('PIN_LOCKOUT_SECONDS', 900),
        'enforce_weak_denylist' => filter_var(env('PIN_ENFORCE_WEAK_DENYLIST', true), FILTER_VALIDATE_BOOLEAN),
        'weak_values' => array_values(array_filter(array_map(
            static fn (string $value): string => trim($value),
            explode(',', (string) env('PIN_WEAK_VALUES', '000000,111111,123456,222222,333333,444444,555555,654321,666666,777777,888888,999999'))
        ))),
    ],
];
