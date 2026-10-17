<?php

namespace App\Jobs;

use App\Services\SmsDeliveryService;
use App\Services\TwilioMessagingService;
use App\Support\Privacy;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOutboundMessage implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public int $maxExceptions = 3;

    public int $timeout = 25;

    public function __construct(
        public string $phone,
        public string $message,
        public array $context = [],
        public bool $fallbackToWhatsApp = true,
        public ?int $expiresAt = null,
        string $queue = 'notifications',
    ) {
        $this->onQueue($queue);
    }

    public function handle(SmsDeliveryService $sms, TwilioMessagingService $messaging): void
    {
        if ($this->expiresAt !== null && now()->timestamp >= $this->expiresAt) {
            Log::notice('Expired outbound message discarded', $this->logContext());

            return;
        }

        try {
            $sms->send($this->phone, $this->message, $this->context);

            return;
        } catch (Throwable $smsException) {
            Log::warning('Queued SMS delivery failed', $this->logContext([
                'message' => $smsException->getMessage(),
                'attempt' => $this->attempts(),
            ]));

            if (! $this->fallbackToWhatsApp) {
                throw $smsException;
            }
        }

        try {
            $messaging->sendWhatsApp($this->phone, $this->message, $this->context);
        } catch (Throwable $whatsAppException) {
            Log::warning('Queued WhatsApp fallback failed', $this->logContext([
                'message' => $whatsAppException->getMessage(),
                'attempt' => $this->attempts(),
            ]));

            throw $whatsAppException;
        }
    }

    public function backoff(): array
    {
        return [5, 15, 45];
    }

    public function retryUntil(): DateTimeInterface
    {
        return (new DateTimeImmutable)->setTimestamp($this->expiresAt ?? now()->addMinutes(15)->timestamp);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Outbound message exhausted retries', $this->logContext([
            'message' => $exception?->getMessage(),
        ]));
    }

    private function logContext(array $extra = []): array
    {
        return array_merge([
            'phone_hash' => Privacy::contactHash($this->phone),
            'context_type' => $this->context['context_type'] ?? null,
            'context_id' => $this->context['context_id'] ?? null,
        ], $extra);
    }
}
