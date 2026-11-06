<?php

namespace Tests\Feature;

use App\Models\ReferralLink;
use App\Models\SellerProfile;
use App\Models\SellerReferral;
use App\Models\User;
use App\Services\SellerReferralService;
use App\Services\SmsOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class SellerReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_open_referral_workspace_and_get_a_stable_invite_link(): void
    {
        $seller = $this->seller();

        $this->actingAs($seller)
            ->get(route('referrals.index'))
            ->assertOk()
            ->assertSee('Seller referrals')
            ->assertSee('Referral rewards')
            ->assertSee('Coming soon');

        $link = ReferralLink::query()->where('user_id', $seller->id)->firstOrFail();

        $this->actingAs($seller)
            ->get(route('referrals.index'))
            ->assertOk()
            ->assertSee(route('referrals.accept', $link));

        $this->assertSame(1, ReferralLink::query()->where('user_id', $seller->id)->count());
    }

    public function test_invite_records_one_visit_per_session_and_goes_directly_to_registration(): void
    {
        $seller = $this->seller('Grace Store');
        $link = app(SellerReferralService::class)->linkFor($seller);

        $this->get(route('referrals.accept', $link))
            ->assertRedirect(route('register'));

        $this->get(route('referrals.accept', $link))
            ->assertRedirect(route('register'));

        $this->assertSame(1, $link->fresh()->visits_count);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Seller invitation')
            ->assertSee('Grace Store invited you');
    }

    public function test_verified_phone_signup_is_attributed_to_the_inviting_seller(): void
    {
        config()->set('auth_phone.enabled', true);

        $seller = $this->seller('Grace Store');
        $link = app(SellerReferralService::class)->linkFor($seller);

        $sms = Mockery::mock(SmsOtpService::class);
        $sms->shouldReceive('sendOtp')->once()->andReturnTrue();
        $sms->shouldReceive('verifyOtp')->once()->andReturnTrue();
        $this->app->instance(SmsOtpService::class, $sms);

        $this->get(route('referrals.accept', $link));

        $this->post(route('register.phone.send'), [
            'phone_country' => '+233',
            'phone_number' => '0541900229',
        ])->assertSessionHas('register_otp_status', 'sent');

        $this->post(route('register.phone.complete'), [
            'phone_country' => '+233',
            'phone_number' => '+233541900229',
            'otp' => '123456',
        ])->assertRedirect(route('pin.setup.show'));

        $referred = User::query()->where('phone', '+233541900229')->firstOrFail();

        $this->assertDatabaseHas('seller_referrals', [
            'referral_link_id' => $link->id,
            'referrer_id' => $seller->id,
            'referred_user_id' => $referred->id,
            'status' => SellerReferral::STATUS_JOINED,
        ]);

        $this->assertNull(session(SellerReferralService::SESSION_LINK_ID));
        $this->assertNull(session(SellerReferralService::SESSION_INVITER_NAME));
    }

    public function test_existing_accounts_cannot_be_reassigned_to_an_inviter(): void
    {
        $inviter = $this->seller('Inviter');
        $existing = $this->seller('Existing Seller');
        $link = app(SellerReferralService::class)->linkFor($inviter);

        $this->actingAs($existing)
            ->get(route('referrals.accept', $link))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('seller_referrals', 0);
        $this->assertSame(0, $link->fresh()->visits_count);
    }

    private function seller(string $businessName = 'Seller Store'): User
    {
        $user = User::factory()->create([
            'phone' => '+233'.fake()->unique()->numerify('#########'),
            'phone_verified_at' => now(),
            'pin_hash' => Hash::make('2486'),
            'plan_type' => User::PLAN_FREE_TRIAL,
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays(9),
        ]);

        SellerProfile::create([
            'user_id' => $user->id,
            'business_name' => $businessName,
            'phone' => $user->phone,
            'public_slug' => 'seller-'.$user->id,
        ]);

        return $user;
    }
}
