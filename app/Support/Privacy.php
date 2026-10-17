<?php

namespace App\Support;

class Privacy
{
    public static function contactHash(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return hash_hmac('sha256', (string) $value, (string) config('app.key'));
    }
}
