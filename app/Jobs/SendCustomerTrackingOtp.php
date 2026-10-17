<?php

namespace App\Jobs;

use App\Services\SmsDeliveryService;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendCustomerTrackingOtp implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public int $maxExceptions = 3;

    public int $timeout = 25;

    public int $expiresAt = 0;

    public function __construct(public string $phone, public string $code)
    {
        $this->expiresAt = now()->addMinutes(5)->timestamp;
        $this->onQueue('otp');
    }

    public function handle(SmsDeliveryService $sms): void
    {
        if ($this->expiresAt <= 0 || now()->timestamp >= $this->expiresAt) {
            return;
        }

        $sms->send(
            $this->phone,
            "Your LinkPay tracking code is {$this->code}. It expires in 5 minutes. Do not share it.",
            ['context_type' => 'customer_tracking_otp']
        );
    }

    public function backoff(): array
    {
        return [5, 15, 45];
    }

    public function retryUntil(): DateTimeInterface
    {
        return (new DateTimeImmutable)->setTimestamp($this->expiresAt > 0 ? $this->expiresAt : now()->timestamp);
    }
}
