<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OpenGraphMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_has_complete_current_brand_social_metadata(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:image:secure_url"', false)
            ->assertSee('property="og:image:type" content="image/jpeg"', false)
            ->assertSee('property="og:image:alt"', false)
            ->assertSee('/images/8kommerce-social-preview-v1.jpg', false)
            ->assertDontSee('PayLink');
    }

    public function test_product_page_exposes_product_specific_social_preview_metadata(): void
    {
        Storage::fake('public');

        $seller = User::factory()->create();
        SellerProfile::create([
            'user_id' => $seller->id,
            'business_name' => 'Grace Store',
            'phone' => '+233541900229',
            'public_slug' => 'grace-store',
        ]);
        $product = Product::create([
            'user_id' => $seller->id,
            'name' => 'Kente Tote',
            'slug' => 'kente-tote',
            'description' => 'A carefully made everyday tote.',
            'price' => 120,
            'stock_quantity' => 5,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);

        $response = $this->get(route('public.product', $product->slug));

        $response->assertOk()
            ->assertSee('property="og:title" content="Kente Tote - Grace Store"', false)
            ->assertSee('property="og:image:secure_url"', false)
            ->assertSee('property="og:image:type" content="image/jpeg"', false)
            ->assertSee('property="og:image:alt" content="Kente Tote from Grace Store"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);

        if (extension_loaded('gd')) {
            $response->assertSee('/storage/og/products/'.$product->id.'-v5.jpg', false);
            Storage::disk('public')->assertExists('og/products/'.$product->id.'-v5.jpg');
        } else {
            $response->assertSee('/images/8kommerce-social-preview-v1.jpg', false);
        }
    }
}
