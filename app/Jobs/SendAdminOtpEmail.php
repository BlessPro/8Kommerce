<?php

namespace App\Jobs;

use App\Mail\AdminOtpCode;
use App\Support\Privacy;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendAdminOtpEmail implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public int $maxExceptions = 3;

    public int $timeout = 25;

    public int $expiresAt;

    public function __construct(public string $email, public string $code, int $ttlMinutes = 10)
    {
        $this->expiresAt = now()->addMinutes($ttlMinutes)->timestamp;
        $this->onQueue('otp');
    }

    public function handle(): void
    {
        if (now()->timestamp >= $this->expiresAt) {
            return;
        }

        Mail::to($this->email)->send(new AdminOtpCode($this->code));
    }

    public function backoff(): array
    {
        return [5, 15, 45];
    }

    public function retryUntil(): DateTimeInterface
    {
        return (new DateTimeImmutable)->setTimestamp($this->expiresAt);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Admin OTP email exhausted retries', [
            'email_hash' => Privacy::contactHash($this->email),
            'message' => $exception?->getMessage(),
        ]);
    }
}
