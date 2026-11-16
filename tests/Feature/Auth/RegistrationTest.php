<?php

namespace Tests\Feature\Auth;

use App\Models\WaitlistEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_route_shows_the_early_access_waitlist(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200)->assertSee('Does this look like you?');
    }

    public function test_phone_can_join_the_waitlist_once(): void
    {
        $response = $this->post('/register', [
            'phone_number' => '0541900229',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('waitlist.show'));
        $this->assertGuest();
        $this->assertDatabaseHas('waitlist_entries', [
            'phone' => '+233541900229',
            'status' => WaitlistEntry::STATUS_WAITING,
        ]);

        $this->post('/register', ['phone_number' => '+233541900229', 'consent' => '1']);
        $this->assertDatabaseCount('waitlist_entries', 1);
    }

    public function test_admin_can_review_waitlist_entries(): void
    {
        config()->set('admin.allowed_emails', ['admin@example.com']);
        WaitlistEntry::create([
            'phone' => '+233541900229',
            'status' => WaitlistEntry::STATUS_WAITING,
            'source' => 'public',
            'joined_at' => now(),
        ]);

        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@example.com']);

        $this->actingAs($admin)
            ->get(route('admin.audience.index'))
            ->assertOk()
            ->assertSee('Seller waitlist')
            ->assertSee('+233541900229');
    }
}
