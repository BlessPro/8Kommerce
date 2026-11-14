<?php

namespace Tests\Feature;

use App\Jobs\SendAdminOtpEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminOtpAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_allowed_admin_email_queues_an_encrypted_otp_email(): void
    {
        Queue::fake();
        config(['admin.allowed_emails' => ['bless53doe3@gmail.com']]);

        $this->post(route('admin.login.send'), [
            'email' => 'Bless53Doe3@Gmail.com',
        ])->assertRedirect()
            ->assertSessionHas('status', 'otp-sent')
            ->assertSessionHas('admin_otp_email', 'bless53doe3@gmail.com');

        Queue::assertPushedOn('otp', SendAdminOtpEmail::class, function (SendAdminOtpEmail $job): bool {
            return $job->email === 'bless53doe3@gmail.com'
                && preg_match('/^\d{6}$/', $job->code) === 1;
        });
    }

    public function test_unknown_email_receives_generic_response_without_queueing_an_otp(): void
    {
        Queue::fake();
        config(['admin.allowed_emails' => ['bless53doe3@gmail.com']]);

        $this->post(route('admin.login.send'), [
            'email' => 'unknown@example.com',
        ])->assertRedirect()->assertSessionHas('status', 'otp-sent');

        Queue::assertNothingPushed();
    }

    public function test_production_log_mailer_does_not_claim_that_email_was_sent(): void
    {
        Queue::fake();
        config([
            'app.env' => 'production',
            'mail.default' => 'log',
            'admin.allowed_emails' => ['bless53doe3@gmail.com'],
        ]);

        $this->post(route('admin.login.send'), [
            'email' => 'bless53doe3@gmail.com',
        ])->assertRedirect()
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('status');

        Queue::assertNothingPushed();
    }

    public function test_incomplete_email_address_is_rejected_before_delivery(): void
    {
        Queue::fake();
        config(['admin.allowed_emails' => ['acesquare52@gmail.com']]);

        $this->post(route('admin.login.send'), [
            'email' => 'acesquare52@gmail',
        ])->assertSessionHasErrors('email');

        Queue::assertNothingPushed();
    }
}
