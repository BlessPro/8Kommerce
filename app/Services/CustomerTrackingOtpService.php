<?php

namespace App\Services;

use App\Jobs\SendCustomerTrackingOtp;
use App\Support\Privacy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CustomerTrackingOtpService
{
    public const TTL_SECONDS = 300;

    public const RESEND_SECONDS = 60;

    public const MAX_SENDS = 3;

    public const SEND_WINDOW_SECONDS = 900;

    public const MAX_VERIFY_ATTEMPTS = 5;

    public function request(string $phone): void
    {
        $sendKey = $this->sendKey($phone);
        if (RateLimiter::tooManyAttempts($sendKey, self::MAX_SENDS) || ! Cache::add($this->cooldownKey($phone), true, self::RESEND_SECONDS)) {
            return;
        }

        RateLimiter::hit($sendKey, self::SEND_WINDOW_SECONDS);

        $code = (string) random_int(100000, 999999);
        Cache::put($this->otpKey($phone), hash('sha256', $code), self::TTL_SECONDS);
        Cache::forget($this->attemptsKey($phone));

        try {
            SendCustomerTrackingOtp::dispatch($phone, $code)
                ->onConnection(config('auth_phone.otp.queue_connection'));
        } catch (\Throwable $exception) {
            Cache::forget($this->otpKey($phone));
            Cache::forget($this->cooldownKey($phone));
            Log::error('Customer tracking OTP delivery could not be started', [
                'phone_hash' => Privacy::contactHash($phone),
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function verify(string $phone, string $code): bool
    {
        $lock = Cache::lock('customer_tracking_otp_verify_lock:'.sha1($phone), 5);
        if (! $lock->get()) {
            return false;
        }

        try {
            $attemptsKey = $this->attemptsKey($phone);
            if ((int) Cache::get($attemptsKey, 0) >= self::MAX_VERIFY_ATTEMPTS) {
                return false;
            }

            $stored = Cache::get($this->otpKey($phone));
            if (! is_string($stored) || ! hash_equals($stored, hash('sha256', trim($code)))) {
                Cache::add($attemptsKey, 0, self::SEND_WINDOW_SECONDS);
                Cache::increment($attemptsKey);

                return false;
            }

            Cache::forget($this->otpKey($phone));
            Cache::forget($attemptsKey);

            return true;
        } finally {
            $lock->release();
        }
    }

    private function otpKey(string $phone): string
    {
        return 'customer_tracking_otp:'.sha1($phone);
    }

    private function attemptsKey(string $phone): string
    {
        return 'customer_tracking_otp_attempts:'.sha1($phone);
    }

    private function cooldownKey(string $phone): string
    {
        return 'customer_tracking_otp_cooldown:'.sha1($phone);
    }

    private function sendKey(string $phone): string
    {
        return 'customer_tracking_otp_sends:'.sha1($phone);
    }
}
