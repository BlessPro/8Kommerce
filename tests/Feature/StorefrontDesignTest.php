<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorefrontDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_preview_and_publish_a_storefront_design(): void
    {
        [$user, $profile] = $this->sellerWithProfile();
        Product::create([
            'user_id' => $user->id,
            'name' => 'Signature Service',
            'slug' => 'signature-service',
            'description' => 'A clear description for the storefront preview.',
            'price' => '85.00',
            'stock_quantity' => 10,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);
        $this->actingAs($user)
            ->get(route('public.preview', ['theme' => 'fashion']))
            ->assertOk()
            ->assertSee('Shape your customer experience')
            ->assertSee('storefront-editor-workspace', false)
            ->assertSee('storefront-editor-section', false)
            ->assertSee('storefront-phone-device', false)
            ->assertSee('Phone preview')
            ->assertSee('iPhone 14 · 390 × 844')
            ->assertSee('Colour palette')
            ->assertSee('storefront-palette-swatch', false)
            ->assertSee('data-phone-preview-scroll', false)
            ->assertDontSee('storefront-phone-screen" inert', false)
            ->assertSee('Marketplace')
            ->assertSee('Boutique')
            ->assertSee('Fashion')
            ->assertSee('storefront-theme-fashion', false)
            ->assertSee('storefront-layout-products', false)
            ->assertSee('Signature Service')
            ->assertSee('Quick view');

        $this->actingAs($user)
            ->patch(route('public.preview.update'), [
                'storefront_theme' => 'fashion',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('public.preview'));

        $profile->refresh();
        $this->assertSame('fashion', $profile->storefront_theme);
        $this->assertSame('products', $profile->storefront_layout);
    }

    public function test_storefront_design_rejects_unknown_values(): void
    {
        [$user, $profile] = $this->sellerWithProfile();

        $this->actingAs($user)
            ->from(route('public.preview'))
            ->patch(route('public.preview.update'), [
                'storefront_theme' => 'unsafe-custom-css',
            ])
            ->assertSessionHasErrors(['storefront_theme'])
            ->assertRedirect(route('public.preview'));

        $this->assertSame('classic', $profile->refresh()->storefront_theme);
    }

    public function test_public_store_uses_the_saved_design_instead_of_query_overrides(): void
    {
        [$user, $profile] = $this->sellerWithProfile([
            'storefront_theme' => 'market',
            'storefront_layout' => 'products',
        ]);
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Paquin Shoulder Bag',
            'slug' => 'paquin-shoulder-bag',
            'category' => 'Bags & travel',
            'price' => '248.00',
            'stock_quantity' => 4,
            'low_stock_threshold' => 1,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);

        $this->withSession([
            'public_cart:'.$profile->public_slug => [
                $product->id => ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->get(route('public.listing', [
            'public_slug' => $profile->public_slug,
            'theme' => 'studio',
            'template' => 'products',
        ]))
            ->assertOk()
            ->assertSee('storefront-theme-market', false)
            ->assertSee('storefront-layout-products', false)
            ->assertSee('Popular right now')
            ->assertSee('Curated marketplace')
            ->assertSee('storefront-market-hero-actions', false)
            ->assertSee('storefront-market-card-category', false)
            ->assertSee('data-cart-update-form', false)
            ->assertSee('data-cart-quantity', false)
            ->assertSee('Changes save automatically')
            ->assertSee('Bags &amp; travel', false)
            ->assertSee('data-market-filter-dialog', false)
            ->assertSee('data-market-load-more', false)
            ->assertSee('storefront-market-mobile-nav', false)
            ->assertSee('storefront-market-quick-add', false)
            ->assertDontSee('storefront-theme-studio', false);
    }

    public function test_seller_can_save_product_categories_for_marketplace_filters(): void
    {
        [$user] = $this->sellerWithProfile();

        $this->actingAs($user)
            ->post(route('products.store'), [
                'products' => [[
                    'name' => 'Pour Electric Kettle',
                    'description' => 'A quiet everyday kettle.',
                    'category' => '  Kitchen  ',
                    'price' => '49.00',
                    'stock_quantity' => 8,
                    'low_stock_threshold' => 2,
                    'status' => Product::STATUS_IN_STOCK,
                    'is_active' => '1',
                ]],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'user_id' => $user->id,
            'name' => 'Pour Electric Kettle',
            'category' => 'Kitchen',
        ]);
    }

    public function test_cart_quantity_can_update_in_the_background_without_rendering_the_storefront(): void
    {
        [$user, $profile] = $this->sellerWithProfile();
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Background update product',
            'slug' => 'background-update-product',
            'price' => '25.00',
            'stock_quantity' => 8,
            'low_stock_threshold' => 1,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);

        $this->withSession([
            'public_cart:'.$profile->public_slug => [
                $product->id => ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->patchJson(route('public.cart.update', $profile->public_slug), [
            'items' => [
                $product->id => ['quantity' => 3],
            ],
        ])->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('total', 'GHS 75.00')
            ->assertJsonPath('items.'.$product->id.'.quantity', 3)
            ->assertJsonPath('items.'.$product->id.'.line_total', 'GHS 75.00');

        $this->assertSame(3, session('public_cart:'.$profile->public_slug.'.'.$product->id.'.quantity'));

        $this->patchJson(route('public.cart.update', $profile->public_slug), [
            'items' => [
                $product->id => ['quantity' => 0],
            ],
        ])->assertOk()
            ->assertJsonPath('count', 0)
            ->assertJsonPath('total', 'GHS 0.00')
            ->assertJsonPath('items', []);
    }

    public function test_seller_can_save_brand_and_catalogue_settings(): void
    {
        [$user, $profile] = $this->sellerWithProfile();

        $this->actingAs($user)
            ->patch(route('public.preview.update'), [
                'storefront_theme' => 'studio',
                'storefront_accent' => 'ocean',
                'storefront_tagline' => 'Made for daily life',
                'storefront_description' => 'Useful products selected by a local independent business.',
                'announcement_text' => 'Free delivery this weekend',
                'announcement_url' => 'https://example.com/delivery',
                'image_ratio' => 'square',
                'grid_density' => 'compact',
                'show_description' => '0',
                'show_stock' => '1',
                'show_quick_add' => '0',
                'show_whatsapp' => '1',
                'sort_order' => 'price_asc',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('public.preview'));

        $profile->refresh();
        $this->assertSame('studio', $profile->storefront_theme);
        $this->assertSame('ocean', $profile->storefront_settings['accent']);
        $this->assertSame('square', $profile->storefront_settings['image_ratio']);
        $this->assertFalse($profile->storefront_settings['show_description']);
        $this->assertFalse($profile->storefront_settings['show_quick_add']);

        $this->get(route('public.listing', $profile->public_slug))
            ->assertOk()
            ->assertSee('Made for daily life')
            ->assertSee('Free delivery this weekend')
            ->assertSee('storefront-image-square', false)
            ->assertSee('storefront-density-compact', false)
            ->assertSee('storefront-hide-description', false)
            ->assertSee('--store-accent: #185fa5', false)
            ->assertSee('--store-secondary: #ef8354', false)
            ->assertSee('--store-surface: #f4f7fb', false)
            ->assertSee('--store-ink: #172b3d', false);
    }

    public function test_storefront_settings_reject_unsafe_or_unknown_values(): void
    {
        [$user, $profile] = $this->sellerWithProfile();

        $this->actingAs($user)
            ->from(route('public.preview'))
            ->patch(route('public.preview.update'), [
                'storefront_theme' => 'classic',
                'storefront_accent' => 'custom-script',
                'announcement_url' => 'javascript:alert(1)',
                'image_ratio' => 'giant',
            ])
            ->assertSessionHasErrors(['storefront_accent', 'announcement_url', 'image_ratio'])
            ->assertRedirect(route('public.preview'));

        $this->assertNull($profile->refresh()->storefront_settings);
    }

    public function test_seller_can_upload_and_reset_a_storefront_logo(): void
    {
        Storage::fake('public');
        [$user, $profile] = $this->sellerWithProfile();

        $this->actingAs($user)
            ->patch(route('public.preview.update'), [
                'storefront_theme' => 'classic',
                'storefront_logo' => UploadedFile::fake()->createWithContent(
                    'north-star.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                ),
            ])
            ->assertSessionHasNoErrors();

        $profile->refresh();
        $logoPath = $profile->storefront_settings['logo_path'];
        Storage::disk('public')->assertExists($logoPath);

        $this->actingAs($user)
            ->delete(route('public.preview.reset'))
            ->assertRedirect(route('public.preview'))
            ->assertSessionHas('status', 'storefront-reset');

        $this->assertNull($profile->refresh()->storefront_settings);
        Storage::disk('public')->assertMissing($logoPath);
    }

    public function test_public_store_uses_the_saved_product_sort_order(): void
    {
        [$user, $profile] = $this->sellerWithProfile([
            'storefront_settings' => ['sort_order' => 'price_asc'],
        ]);

        foreach ([['Premium Item', 'premium-item', '80.00'], ['Value Item', 'value-item', '10.00']] as [$name, $slug, $price]) {
            Product::create([
                'user_id' => $user->id,
                'name' => $name,
                'slug' => $slug,
                'price' => $price,
                'stock_quantity' => 5,
                'low_stock_threshold' => 1,
                'is_active' => true,
                'status' => Product::STATUS_IN_STOCK,
            ]);
        }

        $this->get(route('public.listing', $profile->public_slug))
            ->assertOk()
            ->assertSeeInOrder(['Value Item', 'Premium Item']);
    }

    public function test_seller_can_publish_phase_two_merchandising_settings(): void
    {
        Storage::fake('public');
        [$user, $profile] = $this->sellerWithProfile();
        $standard = Product::create([
            'user_id' => $user->id,
            'name' => 'Standard Product',
            'slug' => 'standard-product',
            'price' => '10.00',
            'stock_quantity' => 5,
            'low_stock_threshold' => 1,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);
        $featured = Product::create([
            'user_id' => $user->id,
            'name' => 'Featured Product',
            'slug' => 'featured-product',
            'price' => '90.00',
            'stock_quantity' => 5,
            'low_stock_threshold' => 1,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);

        $this->actingAs($user)
            ->patch(route('public.preview.update'), [
                'storefront_theme' => 'classic',
                'storefront_hero_image' => UploadedFile::fake()->createWithContent(
                    'hero.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                ),
                'hero_layout' => 'split',
                'featured_product_ids_present' => '1',
                'featured_product_ids' => [$featured->id],
                'show_search' => '1',
                'show_trust_bar' => '1',
                'show_social_links' => '1',
                'delivery_message' => 'Delivery in 1–2 days',
                'returns_message' => 'Returns within 7 days',
                'instagram_url' => 'https://instagram.com/northstar',
            ])
            ->assertSessionHasNoErrors();

        $profile->refresh();
        Storage::disk('public')->assertExists($profile->storefront_settings['hero_image_path']);
        $this->assertSame([$featured->id], $profile->storefront_settings['featured_product_ids']);

        $this->get(route('public.listing', $profile->public_slug))
            ->assertOk()
            ->assertSee('storefront-hero-layout-split', false)
            ->assertSee('data-storefront-search', false)
            ->assertSee('Delivery in 1–2 days')
            ->assertSee('Returns within 7 days')
            ->assertSee('instagram.com/northstar', false)
            ->assertSeeInOrder([$featured->name, $standard->name]);
    }

    public function test_seller_cannot_feature_another_sellers_product(): void
    {
        [$user] = $this->sellerWithProfile();
        [$otherUser] = $this->sellerWithProfile(['public_slug' => 'another-store']);
        $otherProduct = Product::create([
            'user_id' => $otherUser->id,
            'name' => 'Another Seller Product',
            'slug' => 'another-seller-product',
            'price' => '30.00',
            'stock_quantity' => 5,
            'low_stock_threshold' => 1,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);

        $this->actingAs($user)
            ->from(route('public.preview'))
            ->patch(route('public.preview.update'), [
                'storefront_theme' => 'classic',
                'featured_product_ids_present' => '1',
                'featured_product_ids' => [$otherProduct->id],
            ])
            ->assertSessionHasErrors(['featured_product_ids.0'])
            ->assertRedirect(route('public.preview'));
    }

    public function test_seller_can_publish_phase_three_style_business_and_seo_settings(): void
    {
        [$user, $profile] = $this->sellerWithProfile();

        $this->actingAs($user)
            ->patch(route('public.preview.update'), [
                'storefront_theme' => 'studio',
                'font_preset' => 'editorial',
                'card_style' => 'elevated',
                'header_alignment' => 'center',
                'button_style' => 'outline',
                'show_business_info' => '1',
                'business_address' => 'Osu, Accra',
                'business_hours' => 'Monday to Saturday, 9am to 6pm',
                'seo_title' => 'North Star Goods in Accra',
                'seo_description' => 'Shop useful, carefully selected products from North Star Shop.',
            ])
            ->assertSessionHasNoErrors();

        $profile->refresh();
        $this->assertSame('editorial', $profile->storefront_settings['font_preset']);
        $this->assertSame('elevated', $profile->storefront_settings['card_style']);

        $this->get(route('public.listing', $profile->public_slug))
            ->assertOk()
            ->assertSee('<title>North Star Goods in Accra</title>', false)
            ->assertSee('content="Shop useful, carefully selected products from North Star Shop."', false)
            ->assertSee('storefront-font-editorial', false)
            ->assertSee('storefront-cards-elevated', false)
            ->assertSee('storefront-header-center', false)
            ->assertSee('storefront-buttons-outline', false)
            ->assertSee('Osu, Accra')
            ->assertSee('Monday to Saturday, 9am to 6pm');
    }

    public function test_editor_uses_six_sections_and_exposes_local_qr_downloads(): void
    {
        [$user] = $this->sellerWithProfile();

        $response = $this->actingAs($user)->get(route('public.preview'));

        $response
            ->assertOk()
            ->assertSee('data-storefront-qr', false)
            ->assertSee('data-qr-download="png"', false)
            ->assertSee('data-qr-download="svg"', false)
            ->assertSee('Open full preview');

        $this->assertSame(6, substr_count($response->getContent(), '<details class="storefront-editor-section'));
    }

    public function test_seller_can_publish_delivery_and_pickup_options(): void
    {
        [$user, $profile] = $this->sellerWithProfile();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Delivery and pickup')
            ->assertSee('data-add-fulfillment-option', false);

        $this->actingAs($user)
            ->put(route('profile.seller.update'), [
                'business_name' => $profile->business_name,
                'phone_country' => '+233',
                'phone_number' => '0541900229',
                'payout_method' => 'MOMO',
                'fulfillment_options' => [
                    ['type' => 'pickup', 'label' => 'East Legon, Boundary Road', 'fee' => '5.50', 'details' => 'Collect Monday to Saturday, 9am to 6pm.'],
                    ['type' => 'delivery', 'label' => 'Greater Accra', 'fee' => '25', 'details' => 'Same-day delivery for orders placed before noon.'],
                    ['type' => 'delivery', 'label' => 'Tema Community 1–25', 'fee' => '35', 'details' => 'Delivery within one business day.'],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $options = $profile->refresh()->resolvedFulfillmentOptions();
        $this->assertCount(3, $options);
        $this->assertSame('pickup', $options[0]['type']);
        $this->assertSame('5.50', $options[0]['fee']);
        $this->assertSame('25.00', $options[1]['fee']);
        $this->assertSame('35.00', $options[2]['fee']);

        $this->get(route('public.listing', $profile->public_slug))
            ->assertOk()
            ->assertSee('Delivery &amp; pickup', false)
            ->assertSee('East Legon, Boundary Road')
            ->assertSee('Greater Accra')
            ->assertSee('Tema Community 1–25')
            ->assertSee('GHS 25.00');
    }

    public function test_seller_can_resolve_a_supported_momo_account_name(): void
    {
        [$user] = $this->sellerWithProfile();
        $paystack = \Mockery::mock(PaystackService::class);
        $paystack->shouldReceive('listBanks')
            ->once()
            ->with('GHS', 'mobile_money')
            ->andReturn([['name' => 'MTN Mobile Money', 'code' => 'MTN', 'type' => 'mobile_money']]);
        $paystack->shouldReceive('resolveAccount')
            ->once()
            ->with('0541900229', 'MTN')
            ->andReturn(['account_name' => 'Akosua Mensah', 'account_number' => '0541900229']);
        $this->app->instance(PaystackService::class, $paystack);

        $this->actingAs($user)
            ->postJson(route('profile.seller.resolve-account'), [
                'payout_method' => 'MOMO',
                'settlement_bank_code' => 'MTN',
                'account_number' => '0541900229',
            ])
            ->assertOk()
            ->assertJson([
                'account_name' => 'Akosua Mensah',
                'account_number' => '0541900229',
            ]);
    }

    public function test_one_store_settings_submission_updates_account_security_and_fulfillment(): void
    {
        [$user, $profile] = $this->sellerWithProfile();
        $user->update(['password' => Hash::make('old-password')]);

        $page = $this->actingAs($user)->get(route('profile.edit'));
        $page->assertOk()->assertSee('Save store settings')->assertDontSee('Update account')->assertDontSee('Update password');
        $this->assertSame(1, substr_count($page->getContent(), 'Save store settings'));

        $this->actingAs($user)
            ->put(route('profile.seller.update'), [
                'name' => 'Ama Owusu',
                'email' => 'ama@example.com',
                'business_name' => 'Ama Market',
                'phone_country' => '+233',
                'phone_number' => '0541900229',
                'payout_method' => 'MOMO',
                'current_password' => 'old-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
                'fulfillment_options' => [
                    ['type' => 'pickup', 'label' => 'Osu pickup', 'fee' => '0', 'details' => 'Monday to Saturday'],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user->refresh();
        $this->assertSame('Ama Owusu', $user->name);
        $this->assertSame('ama@example.com', $user->email);
        $this->assertTrue(Hash::check('new-secure-password', $user->password));
        $this->assertSame('Ama Market', $profile->refresh()->business_name);
        $this->assertSame('Osu pickup', $profile->resolvedFulfillmentOptions()[0]['label']);
    }

    public function test_seller_can_publish_a_custom_colour_social_handles_and_square_cards(): void
    {
        [$user, $profile] = $this->sellerWithProfile();

        $this->actingAs($user)
            ->patch(route('public.preview.update'), [
                'storefront_theme' => 'market',
                'storefront_accent' => 'custom',
                'custom_accent' => '#336699',
                'instagram_username' => '@north.star',
                'tiktok_username' => '@north_star',
                'facebook_username' => 'north.star.shop',
                'card_corner' => 'square',
                'show_social_links' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('public.preview'));

        $profile->refresh();
        $settings = $profile->storefront_settings;

        $this->assertSame('custom', $settings['accent']);
        $this->assertSame('#336699', $settings['custom_accent']);
        $this->assertSame('north.star', $settings['instagram_username']);
        $this->assertSame('https://instagram.com/north.star', $settings['instagram_url']);
        $this->assertSame('https://tiktok.com/@north_star', $settings['tiktok_url']);
        $this->assertSame('https://facebook.com/north.star.shop', $settings['facebook_url']);
        $this->assertSame('square', $settings['card_corner']);

        $this->get(route('public.listing', $profile->public_slug))
            ->assertOk()
            ->assertSee('storefront-card-corners-square', false)
            ->assertSee('--store-accent: #336699', false)
            ->assertSee('instagram.com/north.star', false)
            ->assertSee('tiktok.com/@north_star', false);
    }

    public function test_storefront_rejects_invalid_social_handles_and_custom_colours(): void
    {
        [$user] = $this->sellerWithProfile();

        $this->actingAs($user)
            ->from(route('public.preview'))
            ->patch(route('public.preview.update'), [
                'storefront_theme' => 'classic',
                'storefront_accent' => 'custom',
                'custom_accent' => 'javascript:alert(1)',
                'instagram_username' => 'https://example.com/not-a-handle',
                'card_corner' => 'circular',
            ])
            ->assertSessionHasErrors(['custom_accent', 'instagram_username', 'card_corner'])
            ->assertRedirect(route('public.preview'));
    }

    public function test_perfume_template_uses_the_seller_identity_cover_and_commerce_navigation(): void
    {
        [$user, $profile] = $this->sellerWithProfile([
            'storefront_theme' => 'perfume',
            'storefront_layout' => 'products',
            'storefront_settings' => [
                'hero_image_path' => 'storefront-heroes/perfume-cover.jpg',
                'tagline' => 'A scent that feels like you.',
                'description' => 'Intimate fragrances selected for everyday rituals.',
                'accent' => 'plum',
            ],
        ]);

        Product::create([
            'user_id' => $user->id,
            'name' => 'Santal 03',
            'slug' => 'santal-03',
            'description' => 'Woody · Sandalwood, cardamom',
            'price' => '88.00',
            'stock_quantity' => 4,
            'low_stock_threshold' => 1,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);

        Product::create([
            'user_id' => $user->id,
            'name' => 'Iris 09',
            'slug' => 'iris-09',
            'description' => 'Powdery · Iris, soft musk',
            'price' => '92.00',
            'stock_quantity' => 0,
            'low_stock_threshold' => 1,
            'is_active' => true,
            'status' => Product::STATUS_SOLD_OUT,
        ]);

        $this->get(route('public.listing', $profile->public_slug))
            ->assertOk()
            ->assertSee('storefront-theme-perfume', false)
            ->assertSee('North Star Shop')
            ->assertSee('storefront-heroes/perfume-cover.jpg', false)
            ->assertSee('A scent that feels like you.')
            ->assertSee('The signature collection')
            ->assertSee('Santal 03')
            ->assertSee('Iris 09')
            ->assertSee('data-can-pay="0"', false)
            ->assertSee('data-perfume-product-dialog', false)
            ->assertSee('data-perfume-product-open', false)
            ->assertSee('Your bag (0)')
            ->assertSee('Your next signature awaits.')
            ->assertSee('Finding your fragrances')
            ->assertSee('storefront-mobile-nav', false)
            ->assertSee('Track your order')
            ->assertDontSee('Secure social commerce');
    }

    public function test_fashion_template_uses_campaign_navigation_quick_view_and_wishlist(): void
    {
        [$user, $profile] = $this->sellerWithProfile([
            'storefront_theme' => 'fashion',
            'storefront_layout' => 'products',
            'storefront_settings' => [
                'hero_image_path' => 'storefront-heroes/fashion-campaign.jpg',
                'tagline' => 'Wear often. Keep longer.',
                'description' => 'Considered pieces for everyday expression.',
                'accent' => 'ocean',
            ],
        ]);

        Product::create([
            'user_id' => $user->id,
            'name' => 'Cotton Utility Jacket',
            'slug' => 'cotton-utility-jacket',
            'description' => 'Relaxed everyday layer.',
            'category' => 'Clothing',
            'price' => '128.00',
            'stock_quantity' => 6,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'status' => Product::STATUS_IN_STOCK,
        ]);

        $this->get(route('public.listing', $profile->public_slug))
            ->assertOk()
            ->assertSee('storefront-theme-fashion', false)
            ->assertSee('storefront-heroes/fashion-campaign.jpg', false)
            ->assertSee('Wear often. Keep longer.')
            ->assertSee('Your next everyday favourites')
            ->assertSee('Cotton Utility Jacket')
            ->assertSee('Quick view')
            ->assertSee('data-fashion-wishlist', false)
            ->assertSee('data-fashion-wishlist-dialog', false)
            ->assertSee('storefront-fashion-mobile-nav', false)
            ->assertSee('Your bag (0)')
            ->assertDontSee('Menu &amp; services', false);
    }

    private function sellerWithProfile(array $profileAttributes = []): array
    {
        $user = User::factory()->create([
            'pin_hash' => '482951',
            'plan_type' => User::PLAN_FREE_TRIAL,
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays(7),
        ]);

        $profile = SellerProfile::create(array_merge([
            'user_id' => $user->id,
            'business_name' => 'North Star Shop',
            'phone' => '+233541900229',
            'public_slug' => 'north-star-shop',
        ], $profileAttributes));

        return [$user, $profile];
    }
}
