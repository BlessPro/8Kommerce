<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Product;
use App\Models\PromoterProfile;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\PromoterAttributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoterProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_join_and_receive_a_promoter_link(): void
    {
        $this->get(route('promoters.index'))
            ->assertOk()
            ->assertSee('Share products worth talking about')
            ->assertSee('Rewards will be introduced later.');

        $this->post(route('promoters.store'), [
            'name' => 'Ama Promoter',
            'phone_number' => '0541900229',
            'city' => 'Accra',
            'channels' => ['whatsapp', 'in_person'],
            'terms' => '1',
        ])->assertRedirect(route('promoters.index'));

        $profile = PromoterProfile::query()->firstOrFail();
        $this->assertSame('+233541900229', $profile->phone);
        $this->assertSame(['whatsapp', 'in_person'], $profile->channels);

        $this->get(route('promoters.index'))
            ->assertOk()
            ->assertSee('Your promoter workspace')
            ->assertSee($profile->public_code);
    }

    public function test_promoter_visit_is_counted_once_per_session_and_product_link_redirects(): void
    {
        [$product, $profile] = $this->productAndPromoter();

        $this->get(route('promoters.visit', ['promoter' => $profile->public_code]))
            ->assertRedirectContains('/sellers');
        $this->get(route('promoters.visit', ['promoter' => $profile->public_code]));
        $this->assertSame(1, $profile->fresh()->visits_count);

        $this->get(route('promoters.product', [
            'promoter' => $profile->public_code,
            'product' => $product->slug,
        ]))->assertRedirectContains('/p/'.$product->slug);

        $this->assertSame($profile->id, session(PromoterAttributionService::SESSION_PROMOTER_ID));
        $this->assertSame(1, $profile->fresh()->visits_count);
    }

    public function test_completed_sale_is_attributed_only_once(): void
    {
        [, $profile, $seller] = $this->productAndPromoter();
        $payment = Payment::create([
            'user_id' => $seller->id,
            'reference' => 'PROMOTER-SALE-001',
            'amount' => '125.00',
            'status' => Payment::STATUS_SUCCESS,
            'paid_at' => now(),
            'raw_payload' => ['promoter_id' => $profile->id],
        ]);

        $service = app(PromoterAttributionService::class);
        $service->recordConversion($payment);
        $service->recordConversion($payment);

        $this->assertDatabaseCount('promoter_conversions', 1);
        $this->assertSame(1, $profile->fresh()->conversions_count);
        $this->assertSame('125.00', $profile->fresh()->conversion_amount);
    }

    private function productAndPromoter(): array
    {
        $seller = User::factory()->create();
        SellerProfile::create([
            'user_id' => $seller->id,
            'business_name' => 'Good Things Store',
            'public_slug' => 'good-things-store',
        ]);
        $product = Product::create([
            'user_id' => $seller->id,
            'name' => 'Everyday Bag',
            'slug' => 'everyday-bag',
            'price' => '125.00',
            'stock_quantity' => 5,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);
        $profile = PromoterProfile::create([
            'public_code' => 'AMASHARES1',
            'name' => 'Ama Promoter',
            'phone' => '+233541900229',
            'channels' => ['whatsapp'],
        ]);

        return [$product, $profile, $seller];
    }
}
