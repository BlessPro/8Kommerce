<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SellerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SellerPublicPreviewController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $profile = $user->sellerProfile;

        if (! $profile) {
            return redirect()->route('profile.edit')
                ->withErrors(['profile' => 'Complete your seller profile to preview your public page.']);
        }

        $theme = $request->query('theme', $profile->storefront_theme ?: 'classic');
        if (! in_array($theme, SellerProfile::STOREFRONT_THEMES, true)) {
            $theme = $profile->storefront_theme ?: 'classic';
        }

        $template = 'products';

        $statusFilter = $request->query('status', 'all');
        $allowedStatusFilters = array_merge(['all'], array_keys(Product::statusOptions()));
        if (! in_array($statusFilter, $allowedStatusFilters, true)) {
            $statusFilter = 'all';
        }

        $allProducts = $user->products()
            ->where('is_active', true)
            ->where('status', '!=', Product::STATUS_UNAVAILABLE)
            ->latest()
            ->get();
        $allProducts = $this->sortProducts($allProducts, $profile->resolvedStorefrontSettings()['sort_order']);
        $allProducts = $this->promoteFeaturedProducts($allProducts, $profile->resolvedStorefrontSettings()['featured_product_ids']);
        $products = $statusFilter === 'all'
            ? $allProducts
            : $allProducts->where('status', $statusFilter)->values();

        $statusTabs = [
            ['key' => 'all', 'label' => 'All products', 'count' => $allProducts->count()],
            ['key' => Product::STATUS_IN_STOCK, 'label' => 'In stock', 'count' => $allProducts->where('status', Product::STATUS_IN_STOCK)->count()],
            ['key' => Product::STATUS_LOW_STOCK, 'label' => 'Low stock', 'count' => $allProducts->where('status', Product::STATUS_LOW_STOCK)->count()],
            ['key' => Product::STATUS_PRE_ORDER, 'label' => 'Pre-order', 'count' => $allProducts->where('status', Product::STATUS_PRE_ORDER)->count()],
            ['key' => Product::STATUS_SOLD_OUT, 'label' => 'Sold out', 'count' => $allProducts->where('status', Product::STATUS_SOLD_OUT)->count()],
        ];

        return view('dashboard.public-preview', [
            'profile' => $profile,
            'products' => $products,
            'currency' => config('services.paystack.currency', 'GHS'),
            'template' => $template,
            'storefrontTheme' => $theme,
            'statusFilter' => $statusFilter,
            'statusTabs' => $statusTabs,
            'cart' => ['items' => collect(), 'count' => 0, 'total' => '0.00'],
            'isOwner' => true,
            'isPreview' => true,
            'paymentsEnabled' => $user->canUsePaymentsFeature(),
        ]);
    }

    public function update(Request $request)
    {
        $profile = $request->user()->sellerProfile;
        if (! $profile) {
            return redirect()->route('profile.edit')
                ->withErrors(['profile' => 'Complete your seller profile before choosing a storefront design.']);
        }

        $validated = $request->validate([
            'storefront_theme' => ['required', 'string', Rule::in(SellerProfile::STOREFRONT_THEMES)],
            'storefront_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'storefront_accent' => ['nullable', 'string', Rule::in([...array_keys(SellerProfile::STOREFRONT_ACCENTS), 'custom'])],
            'custom_accent' => ['nullable', 'required_if:storefront_accent,custom', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'storefront_tagline' => ['nullable', 'string', 'max:80'],
            'storefront_description' => ['nullable', 'string', 'max:240'],
            'announcement_text' => ['nullable', 'string', 'max:100'],
            'announcement_url' => ['nullable', 'url:http,https', 'max:2048'],
            'image_ratio' => ['nullable', 'string', Rule::in(SellerProfile::STOREFRONT_IMAGE_RATIOS)],
            'grid_density' => ['nullable', 'string', Rule::in(SellerProfile::STOREFRONT_DENSITIES)],
            'show_description' => ['nullable', 'boolean'],
            'show_stock' => ['nullable', 'boolean'],
            'show_quick_add' => ['nullable', 'boolean'],
            'show_whatsapp' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'string', Rule::in(SellerProfile::STOREFRONT_SORTS)],
            'storefront_hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_hero_image' => ['nullable', 'boolean'],
            'hero_layout' => ['nullable', 'string', Rule::in(SellerProfile::STOREFRONT_HERO_LAYOUTS)],
            'featured_product_ids_present' => ['nullable', 'boolean'],
            'featured_product_ids' => ['nullable', 'array', 'max:6'],
            'featured_product_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
            'show_search' => ['nullable', 'boolean'],
            'show_trust_bar' => ['nullable', 'boolean'],
            'show_social_links' => ['nullable', 'boolean'],
            'delivery_message' => ['nullable', 'string', 'max:80'],
            'returns_message' => ['nullable', 'string', 'max:80'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:2048'],
            'facebook_url' => ['nullable', 'url:http,https', 'max:2048'],
            'tiktok_url' => ['nullable', 'url:http,https', 'max:2048'],
            'instagram_username' => ['nullable', 'string', 'max:30', 'regex:/^@?[A-Za-z0-9._]+$/'],
            'tiktok_username' => ['nullable', 'string', 'max:24', 'regex:/^@?[A-Za-z0-9._]+$/'],
            'facebook_username' => ['nullable', 'string', 'max:50', 'regex:/^@?[A-Za-z0-9.]+$/'],
            'font_preset' => ['nullable', 'string', Rule::in(SellerProfile::STOREFRONT_FONT_PRESETS)],
            'card_style' => ['nullable', 'string', Rule::in(SellerProfile::STOREFRONT_CARD_STYLES)],
            'card_corner' => ['nullable', 'string', Rule::in(SellerProfile::STOREFRONT_CARD_CORNERS)],
            'header_alignment' => ['nullable', 'string', Rule::in(SellerProfile::STOREFRONT_HEADER_ALIGNMENTS)],
            'button_style' => ['nullable', 'string', Rule::in(SellerProfile::STOREFRONT_BUTTON_STYLES)],
            'show_business_info' => ['nullable', 'boolean'],
            'business_address' => ['nullable', 'string', 'max:160'],
            'business_hours' => ['nullable', 'string', 'max:120'],
            'seo_title' => ['nullable', 'string', 'max:60'],
            'seo_description' => ['nullable', 'string', 'max:160'],
        ]);

        $settings = $profile->resolvedStorefrontSettings();
        $textSettings = [
            'storefront_accent' => 'accent',
            'custom_accent' => 'custom_accent',
            'storefront_tagline' => 'tagline',
            'storefront_description' => 'description',
            'announcement_text' => 'announcement_text',
            'announcement_url' => 'announcement_url',
            'image_ratio' => 'image_ratio',
            'grid_density' => 'grid_density',
            'sort_order' => 'sort_order',
            'hero_layout' => 'hero_layout',
            'delivery_message' => 'delivery_message',
            'returns_message' => 'returns_message',
            'instagram_url' => 'instagram_url',
            'facebook_url' => 'facebook_url',
            'tiktok_url' => 'tiktok_url',
            'font_preset' => 'font_preset',
            'card_style' => 'card_style',
            'card_corner' => 'card_corner',
            'header_alignment' => 'header_alignment',
            'button_style' => 'button_style',
            'business_address' => 'business_address',
            'business_hours' => 'business_hours',
            'seo_title' => 'seo_title',
            'seo_description' => 'seo_description',
        ];
        foreach ($textSettings as $input => $setting) {
            if ($request->exists($input)) {
                $value = trim((string) ($validated[$input] ?? ''));
                $settings[$setting] = $value !== '' ? $value : null;
            }
        }

        foreach ([
            'instagram_username' => ['setting' => 'instagram_username', 'url' => 'instagram_url', 'base' => 'https://instagram.com/'],
            'tiktok_username' => ['setting' => 'tiktok_username', 'url' => 'tiktok_url', 'base' => 'https://tiktok.com/@'],
            'facebook_username' => ['setting' => 'facebook_username', 'url' => 'facebook_url', 'base' => 'https://facebook.com/'],
        ] as $input => $social) {
            if ($request->exists($input)) {
                $username = ltrim(trim((string) ($validated[$input] ?? '')), '@');
                $settings[$social['setting']] = $username !== '' ? $username : null;
                $settings[$social['url']] = $username !== '' ? $social['base'].$username : null;
            }
        }

        foreach (['show_description', 'show_stock', 'show_quick_add', 'show_whatsapp', 'show_search', 'show_trust_bar', 'show_social_links', 'show_business_info'] as $setting) {
            if ($request->exists($setting)) {
                $settings[$setting] = $request->boolean($setting);
            }
        }

        if ($request->boolean('featured_product_ids_present')) {
            $settings['featured_product_ids'] = array_map('intval', $validated['featured_product_ids'] ?? []);
        }

        if ($request->hasFile('storefront_logo')) {
            $previousLogo = $settings['logo_path'];
            $settings['logo_path'] = $request->file('storefront_logo')->store('storefront-logos', 'public');
            $this->deleteStorefrontLogo($previousLogo);
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteStorefrontLogo($settings['logo_path']);
            $settings['logo_path'] = null;
        }

        if ($request->hasFile('storefront_hero_image')) {
            $previousHeroImage = $settings['hero_image_path'];
            $settings['hero_image_path'] = $request->file('storefront_hero_image')->store('storefront-heroes', 'public');
            $this->deleteStorefrontImage($previousHeroImage, 'storefront-heroes/');
        } elseif ($request->boolean('remove_hero_image')) {
            $this->deleteStorefrontImage($settings['hero_image_path'], 'storefront-heroes/');
            $settings['hero_image_path'] = null;
        }

        $profile->update([
            'storefront_theme' => $validated['storefront_theme'],
            'storefront_layout' => 'products',
            'storefront_settings' => $settings,
        ]);

        return redirect()->route('public.preview')->with('status', 'storefront-updated');
    }

    public function reset(Request $request)
    {
        $profile = $request->user()->sellerProfile;
        if (! $profile) {
            return redirect()->route('profile.edit');
        }

        $this->deleteStorefrontLogo($profile->resolvedStorefrontSettings()['logo_path']);
        $this->deleteStorefrontImage($profile->resolvedStorefrontSettings()['hero_image_path'], 'storefront-heroes/');
        $profile->update(['storefront_settings' => null]);

        return redirect()->route('public.preview')->with('status', 'storefront-reset');
    }

    private function sortProducts($products, ?string $sortOrder)
    {
        return match ($sortOrder) {
            'price_asc' => $products->sortBy(fn (Product $product) => (float) $product->price)->values(),
            'price_desc' => $products->sortByDesc(fn (Product $product) => (float) $product->price)->values(),
            'name' => $products->sortBy(fn (Product $product) => mb_strtolower($product->name))->values(),
            'availability' => $products->sortBy(fn (Product $product) => match ($product->status) {
                Product::STATUS_IN_STOCK => 0,
                Product::STATUS_LOW_STOCK => 1,
                Product::STATUS_PRE_ORDER => 2,
                Product::STATUS_SOLD_OUT => 3,
                default => 4,
            })->values(),
            default => $products->sortByDesc('created_at')->values(),
        };
    }

    private function deleteStorefrontLogo(?string $path): void
    {
        if ($path && str_starts_with($path, 'storefront-logos/')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function deleteStorefrontImage(?string $path, string $directory): void
    {
        if ($path && str_starts_with($path, $directory)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function promoteFeaturedProducts($products, array $featuredIds)
    {
        $featuredIds = array_map('intval', $featuredIds);
        $featured = collect($featuredIds)
            ->map(fn (int $id) => $products->firstWhere('id', $id))
            ->filter();

        return $featured->concat($products->whereNotIn('id', $featuredIds))->values();
    }
}
