<?php

namespace Tests\Feature;

use App\Models\EditorialArticle;
use App\Models\FeaturedPlacement;
use App\Models\Order;
use App\Models\OrderFeedback;
use App\Models\OrderFeedbackToken;
use App\Models\Product;
use App\Models\PromotionSubmission;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\SellerRatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EditorialPromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_nominate_an_owned_product_and_see_incoming_insights(): void
    {
        [$seller] = $this->seller();
        $product = $this->product($seller, 'Launch bag');

        $this->actingAs($seller)->get(route('promotions.index'))
            ->assertOk()
            ->assertSee('Put your best product forward')
            ->assertSee('Performance insights are coming soon');

        $this->actingAs($seller)->post(route('promotions.store'), [
            'product_id' => $product->id,
            'purpose' => 'new_product',
            'seller_note' => 'A locally made launch piece.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('promotion_submissions', [
            'user_id' => $seller->id,
            'product_id' => $product->id,
            'status' => 'submitted',
        ]);
    }

    public function test_seller_cannot_nominate_another_sellers_product(): void
    {
        [$seller] = $this->seller();
        [$other] = $this->seller('Other Store', 'other-store');
        $product = $this->product($other, 'Other product');

        $this->actingAs($seller)->post(route('promotions.store'), [
            'product_id' => $product->id,
            'purpose' => 'bestseller',
        ])->assertSessionHasErrors('product_id');
    }

    public function test_admin_can_publish_an_article_feature_a_product_and_recognise_a_seller(): void
    {
        [$seller] = $this->seller();
        $product = $this->product($seller, 'Featured basket');
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@8kommerce.test']);
        config(['admin.allowed_emails' => ['admin@8kommerce.test']]);

        $this->actingAs($admin)->post(route('admin.promotions.articles.store'), [
            'seller_user_id' => $seller->id,
            'type' => 'seller_spotlight',
            'title' => 'Meet the maker behind the basket',
            'excerpt' => 'A practical Ghanaian business story.',
            'body' => 'This is a carefully prepared editorial story about the seller.',
            'product_ids' => [$product->id],
            'status' => 'published',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $article = EditorialArticle::firstOrFail();
        $this->get(route('discover.show', $article->slug))
            ->assertOk()
            ->assertSee($article->title)
            ->assertSee($product->name);

        $this->actingAs($admin)->post(route('admin.promotions.placements.store'), [
            'product_id' => $product->id,
            'placement' => 'marketplace_home',
            'source' => 'editorial',
            'label' => 'Featured by 8Kommerce',
            'starts_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addWeek()->format('Y-m-d H:i:s'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertTrue(FeaturedPlacement::query()->active()->exists());
        $this->get(route('marketplace.sellers'))->assertOk()->assertSee('Featured basket');

        $this->actingAs($admin)->post(route('admin.promotions.recognitions.store'), [
            'user_id' => $seller->id,
            'article_id' => $article->id,
            'period_start' => now()->format('Y-m'),
            'summary' => 'Consistent service and thoughtful products.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->get(route('discover.index'))->assertOk()->assertSee('Seller of the Month');
    }

    public function test_only_verified_completed_order_ratings_build_the_public_score(): void
    {
        [$seller] = $this->seller();

        foreach ([5, 4, 5] as $index => $rating) {
            $order = Order::create([
                'user_id' => $seller->id,
                'reference' => 'ORDER-RATING-'.$index,
                'status' => Order::STATUS_COMPLETED,
                'payment_status' => 'SUCCESS',
                'customer_phone' => '+23350000000'.$index,
                'subtotal' => 20,
                'total' => 20,
                'currency' => 'GHS',
            ]);
            $token = OrderFeedbackToken::create([
                'order_id' => $order->id,
                'token' => 'rating-token-'.$index,
                'used_at' => now(),
            ]);
            OrderFeedback::create([
                'order_id' => $order->id,
                'order_feedback_token_id' => $token->id,
                'type' => OrderFeedback::TYPE_RECEIVED,
                'rating' => $rating,
                'admin_status' => OrderFeedback::ADMIN_IGNORED,
            ]);
        }

        $summary = app(SellerRatingService::class)->summary($seller->id);
        $this->assertTrue($summary['public']);
        $this->assertSame(3, $summary['count']);
        $this->assertSame(4.7, $summary['average']);
        $this->get(route('marketplace.sellers'))->assertOk()->assertSee('4.7 ★');
    }

    private function seller(string $name = 'North Star', string $slug = 'north-star'): array
    {
        $user = User::factory()->create();
        $user->forceFill([
            'pin_hash' => Hash::make('482951'),
            'plan_type' => User::PLAN_GROWTH,
            'plan_started_at' => now(),
            'plan_ends_at' => now()->addMonth(),
        ])->save();
        $profile = SellerProfile::create([
            'user_id' => $user->id,
            'business_name' => $name,
            'phone' => '+233541900229',
            'public_slug' => $slug,
        ]);
        return [$user, $profile];
    }

    private function product(User $seller, string $name): Product
    {
        return Product::create([
            'user_id' => $seller->id,
            'name' => $name,
            'slug' => (string) str($name)->slug(),
            'price' => '60.00',
            'stock_quantity' => 5,
            'low_stock_threshold' => 1,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);
    }
}
