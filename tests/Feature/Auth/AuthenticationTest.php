<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_login_route_shows_the_waitlist_and_private_seller_access_still_works(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)->assertSee('Be first when the doors open');
        $this->get(route('seller.login'))->assertOk()->assertSee('Welcome back');
    }

    public function test_users_can_authenticate_with_phone_and_pin(): void
    {
        $user = User::factory()->create([
            'phone' => '+233541900229',
            'pin_hash' => Hash::make('2486'),
        ]);

        $response = $this->post(route('login.phone.pin'), [
            'phone_country' => '+233',
            'phone_number' => '0541900229',
            'pin' => '2486',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_phone_pin(): void
    {
        $user = User::factory()->create([
            'phone' => '+233541900229',
            'pin_hash' => Hash::make('2486'),
        ]);

        $this->post(route('login.phone.pin'), [
            'phone_country' => '+233',
            'phone_number' => '0541900229',
            'pin' => '9999',
        ]);

        $this->assertGuest();
    }

    public function test_email_login_endpoint_is_not_available(): void
    {
        $this->post('/login', [
            'email' => 'seller@example.com',
            'pin' => '2486',
        ])->assertStatus(405);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
