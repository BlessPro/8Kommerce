<?php

namespace Tests\Feature;

use App\Services\HubtelSmsService;
use App\Services\SmsDeliveryService;
use App\Services\TwilioMessagingService;
use Mockery;
use Tests\TestCase;

class SmsDeliveryServiceTest extends TestCase
{
    public function test_auto_uses_twilio_when_hubtel_is_not_configured(): void
    {
        config()->set('services.sms.provider', 'auto');
        config()->set('services.hubtel', [
            'client_id' => null,
            'client_secret' => null,
            'sender_id' => null,
        ]);
        config()->set('services.twilio.account_sid', 'AC123');
        config()->set('services.twilio.auth_token', 'secret');
        config()->set('services.twilio.messaging_service_sid', 'MG123');
        config()->set('services.twilio.sms_from', null);

        $hubtel = Mockery::mock(HubtelSmsService::class);
        $twilio = Mockery::mock(TwilioMessagingService::class);
        $twilio->shouldReceive('sendSms')
            ->once()
            ->with('+233541900229', 'Test message', ['context_type' => 'test']);

        $service = new SmsDeliveryService($hubtel, $twilio);
        $service->send('+233541900229', 'Test message', ['context_type' => 'test']);

        $this->assertSame('twilio', $service->provider());
    }

    public function test_auto_prefers_hubtel_when_both_are_configured(): void
    {
        config()->set('services.sms.provider', 'auto');
        config()->set('services.hubtel.client_id', 'client');
        config()->set('services.hubtel.client_secret', 'secret');
        config()->set('services.hubtel.sender_id', '8KOMMERCE');
        config()->set('services.twilio.account_sid', 'AC123');
        config()->set('services.twilio.auth_token', 'secret');
        config()->set('services.twilio.messaging_service_sid', 'MG123');

        $hubtel = Mockery::mock(HubtelSmsService::class);
        $twilio = Mockery::mock(TwilioMessagingService::class);
        $hubtel->shouldReceive('send')->once();
        $twilio->shouldNotReceive('sendSms');

        $service = new SmsDeliveryService($hubtel, $twilio);
        $service->send('+233541900229', 'Test message');

        $this->assertSame('hubtel', $service->provider());
    }

    public function test_explicit_incomplete_provider_is_reported_as_unavailable(): void
    {
        config()->set('services.sms.provider', 'twilio');
        config()->set('services.twilio.account_sid', 'AC123');
        config()->set('services.twilio.auth_token', 'secret');
        config()->set('services.twilio.messaging_service_sid', null);
        config()->set('services.twilio.sms_from', null);

        $service = new SmsDeliveryService(
            Mockery::mock(HubtelSmsService::class),
            Mockery::mock(TwilioMessagingService::class),
        );

        $this->assertNull($service->provider());
        $this->expectException(\RuntimeException::class);
        $service->send('+233541900229', 'Test message');
    }
}
