<?php

return [
    'allowed_emails' => array_values(array_filter(array_map(
        static fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) env('ADMIN_ALLOWED_EMAILS', ''))
    ))),
    'otp_ttl_minutes' => 10,
];
