<?php

namespace App\Services;

use RuntimeException;

class SmsDeliveryService
{
    public function __construct(
        private readonly HubtelSmsService $hubtel,
        private readonly TwilioMessagingService $twilio,
    ) {}

    public function send(string $to, string $message, array $context = []): void
    {
        match ($this->provider()) {
            'hubtel' => $this->hubtel->send($to, $message, $context),
            'twilio' => $this->twilio->sendSms($to, $message, $context),
            default => throw new RuntimeException(
                'SMS is not configured. Configure Hubtel credentials or Twilio credentials with a Messaging Service SID/SMS sender.'
            ),
        };
    }

    public function provider(): ?string
    {
        $configured = strtolower(trim((string) config('services.sms.provider', 'auto')));

        if ($configured === 'hubtel') {
            return $this->hubtelIsConfigured() ? 'hubtel' : null;
        }

        if ($configured === 'twilio') {
            return $this->twilioIsConfigured() ? 'twilio' : null;
        }

        if ($configured !== 'auto') {
            throw new RuntimeException('Unsupported SMS_PROVIDER. Use auto, hubtel, or twilio.');
        }

        if ($this->hubtelIsConfigured()) {
            return 'hubtel';
        }

        return $this->twilioIsConfigured() ? 'twilio' : null;
    }

    public function diagnostics(): array
    {
        return [
            'requested_provider' => strtolower(trim((string) config('services.sms.provider', 'auto'))),
            'selected_provider' => $this->provider(),
            'hubtel_configured' => $this->hubtelIsConfigured(),
            'twilio_configured' => $this->twilioIsConfigured(),
        ];
    }

    private function hubtelIsConfigured(): bool
    {
        return filled(config('services.hubtel.client_id'))
            && filled(config('services.hubtel.client_secret'))
            && filled(config('services.hubtel.sender_id'));
    }

    private function twilioIsConfigured(): bool
    {
        $messagingServiceSid = (string) config('services.twilio.messaging_service_sid');

        return filled(config('services.twilio.account_sid'))
            && filled(config('services.twilio.auth_token'))
            && (str_starts_with($messagingServiceSid, 'MG') || filled(config('services.twilio.sms_from')));
    }
}
