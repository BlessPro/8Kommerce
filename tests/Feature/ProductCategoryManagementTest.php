<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Http\Middleware\EnsureActiveAccess;
use App\Http\Middleware\EnsureProfileOnboardingComplete;
use App\Http\Middleware\EnsurePromotionAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function withoutSellerGates(): static
    {
        return $this->withoutMiddleware([
            EnsureActiveAccess::class,
            EnsureProfileOnboardingComplete::class,
            EnsurePromotionAccess::class,
        ]);
    }

    public function test_seller_can_create_several_categories_from_comma_separated_names(): void
    {
        $user = User::factory()->create();

        $this->withoutSellerGates()
            ->actingAs($user)
            ->post(route('product-categories.store'), ['categories' => 'Fashion, Beauty, Home & living, fashion'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('product_categories', 3);
        $this->assertDatabaseHas('product_categories', ['user_id' => $user->id, 'normalized_name' => 'fashion']);
    }

    public function test_renaming_and_deleting_a_category_keeps_products_consistent(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::remember($user->id, 'Fashion');
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Linen shirt',
            'slug' => 'linen-shirt',
            'category' => 'Fashion',
            'price' => '120.00',
            'stock_quantity' => 2,
            'low_stock_threshold' => 1,
            'status' => Product::STATUS_IN_STOCK,
            'is_active' => true,
        ]);

        $this->withoutSellerGates()->actingAs($user)
            ->patch(route('product-categories.update', $category), ['name' => 'Apparel'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Apparel', $product->refresh()->category);

        $this->withoutSellerGates()->actingAs($user)
            ->delete(route('product-categories.destroy', $category->refresh()))
            ->assertSessionHasNoErrors();

        $this->assertNull($product->refresh()->category);
    }

    public function test_saving_a_product_remembers_its_category(): void
    {
        $user = User::factory()->create();

        $this->withoutSellerGates()->actingAs($user)->post(route('products.store'), [
            'name' => 'Canvas tote',
            'category' => 'Bags',
            'price' => '85.00',
            'stock_quantity' => 3,
            'low_stock_threshold' => 1,
            'status' => Product::STATUS_IN_STOCK,
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_categories', [
            'user_id' => $user->id,
            'normalized_name' => 'bags',
        ]);
    }
}
