<?php

namespace App\Services;

use App\Models\CustomerTrackingSession;
use App\Models\Order;
use Illuminate\Support\Str;

class CustomerTrackingService
{
    public const COOKIE = 'lp_tracking_access';

    public const SESSION_PHONE = 'customer_tracking.phone';

    public const SESSION_VERIFIED_AT = 'customer_tracking.verified_at';

    public const TRUST_DAYS = 30;

    public function hasOrders(string $phone): bool
    {
        return Order::query()->where('customer_phone', $phone)->where('status', '!=', Order::STATUS_TRASHED)->exists();
    }

    public function maskedPhone(string $phone): string
    {
        return strlen($phone) > 4 ? '**** '.substr($phone, -4) : $phone;
    }

    public function issueTrustedToken(string $phone): string
    {
        $token = Str::random(64);
        CustomerTrackingSession::query()->create([
            'phone' => $phone,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(self::TRUST_DAYS),
            'last_used_at' => now(),
        ]);

        return $token;
    }

    public function resolveTrustedToken(?string $token): ?string
    {
        if (! $token) {
            return null;
        }

        $session = CustomerTrackingSession::query()
            ->where('token_hash', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        if (! $session) {
            return null;
        }

        if (! $session->last_used_at || $session->last_used_at->lt(now()->subDay())) {
            $session->update(['last_used_at' => now()]);
        }

        return $session->phone;
    }

    public function revokeTrustedToken(?string $token): void
    {
        if ($token) {
            CustomerTrackingSession::query()->where('token_hash', hash('sha256', $token))->delete();
        }
    }
}
