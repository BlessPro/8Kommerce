<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SellerProfile extends Model
{
    use HasFactory;

    public const STOREFRONT_THEMES = ['classic', 'perfume', 'market', 'studio', 'fashion'];

    public const STOREFRONT_LAYOUTS = ['products', 'services'];

    public const STOREFRONT_ACCENTS = [
        'forest' => ['accent' => '#126b57', 'dark' => '#0c5545', 'tint' => '#eaf3ef', 'secondary' => '#c58a43', 'surface' => '#f6f3eb', 'ink' => '#18312a'],
        'ocean' => ['accent' => '#185fa5', 'dark' => '#124a81', 'tint' => '#eaf2fa', 'secondary' => '#ef8354', 'surface' => '#f4f7fb', 'ink' => '#172b3d'],
        'terracotta' => ['accent' => '#9b432f', 'dark' => '#773224', 'tint' => '#f7e9e3', 'secondary' => '#63785b', 'surface' => '#fbf5ee', 'ink' => '#38251f'],
        'charcoal' => ['accent' => '#343a37', 'dark' => '#202522', 'tint' => '#ecefed', 'secondary' => '#b59b68', 'surface' => '#f5f3ee', 'ink' => '#202522'],
        'plum' => ['accent' => '#73456f', 'dark' => '#593455', 'tint' => '#f2eaf1', 'secondary' => '#c69255', 'surface' => '#faf6f8', 'ink' => '#342532'],
        'amber' => ['accent' => '#a85408', 'dark' => '#7f3e05', 'tint' => '#fbefe2', 'secondary' => '#315f68', 'surface' => '#fff8ee', 'ink' => '#3a2a1c'],
        'rose' => ['accent' => '#b91c5c', 'dark' => '#8f1647', 'tint' => '#fbe9f0', 'secondary' => '#536f63', 'surface' => '#fff6f8', 'ink' => '#3d2330'],
        'teal' => ['accent' => '#0e7490', 'dark' => '#0a596f', 'tint' => '#e5f4f7', 'secondary' => '#d28445', 'surface' => '#f3f8f8', 'ink' => '#18343b'],
        'ink' => ['accent' => '#171714', 'dark' => '#090908', 'tint' => '#efefec', 'secondary' => '#3157c8', 'surface' => '#f6f6f2', 'ink' => '#171714'],
    ];

    public const STOREFRONT_IMAGE_RATIOS = ['square', 'portrait', 'landscape', 'natural'];

    public const STOREFRONT_DENSITIES = ['compact', 'comfortable', 'spacious'];

    public const STOREFRONT_SORTS = ['newest', 'price_asc', 'price_desc', 'name', 'availability'];

    public const STOREFRONT_HERO_LAYOUTS = ['compact', 'split', 'banner'];

    public const STOREFRONT_FONT_PRESETS = ['clean', 'editorial', 'rounded'];

    public const STOREFRONT_CARD_STYLES = ['bordered', 'flat', 'elevated'];

    public const STOREFRONT_CARD_CORNERS = ['rounded', 'square'];

    public const STOREFRONT_HEADER_ALIGNMENTS = ['left', 'center'];

    public const STOREFRONT_BUTTON_STYLES = ['solid', 'outline'];

    protected $fillable = [
        'user_id',
        'business_name',
        'phone',
        'public_slug',
        'storefront_theme',
        'storefront_layout',
        'storefront_settings',
        'paystack_subaccount_code',
        'payout_method',
        'settlement_bank_code',
        'account_number',
        'account_name',
        'percent_charge',
    ];

    protected function casts(): array
    {
        return [
            'percent_charge' => 'decimal:2',
            'storefront_settings' => 'array',
        ];
    }

    public function resolvedStorefrontSettings(): array
    {
        return array_replace([
            'logo_path' => null,
            'accent' => 'forest',
            'custom_accent' => '#126b57',
            'tagline' => null,
            'description' => null,
            'announcement_text' => null,
            'announcement_url' => null,
            'image_ratio' => 'portrait',
            'grid_density' => 'comfortable',
            'show_description' => true,
            'show_stock' => true,
            'show_quick_add' => true,
            'show_whatsapp' => true,
            'sort_order' => 'newest',
            'hero_image_path' => null,
            'hero_layout' => 'compact',
            'featured_product_ids' => [],
            'show_search' => true,
            'show_trust_bar' => true,
            'show_social_links' => true,
            'delivery_message' => null,
            'returns_message' => null,
            'fulfillment_options' => [],
            'fulfillment_pickup_enabled' => false,
            'pickup_station' => null,
            'pickup_price' => '0.00',
            'pickup_description' => null,
            'delivery_accra_enabled' => false,
            'delivery_accra_price' => '0.00',
            'delivery_accra_description' => null,
            'delivery_tema_enabled' => false,
            'delivery_tema_price' => '0.00',
            'delivery_tema_description' => null,
            'instagram_url' => null,
            'facebook_url' => null,
            'tiktok_url' => null,
            'instagram_username' => null,
            'facebook_username' => null,
            'tiktok_username' => null,
            'font_preset' => 'clean',
            'card_style' => 'bordered',
            'card_corner' => 'rounded',
            'header_alignment' => 'left',
            'button_style' => 'solid',
            'show_business_info' => false,
            'business_address' => null,
            'business_hours' => null,
            'seo_title' => null,
            'seo_description' => null,
        ], is_array($this->storefront_settings) ? $this->storefront_settings : []);
    }

    public function resolvedFulfillmentOptions(): array
    {
        $stored = is_array($this->storefront_settings) ? $this->storefront_settings : [];
        if (array_key_exists('fulfillment_options', $stored)) {
            return collect($stored['fulfillment_options'] ?? [])
                ->filter(fn ($option) => is_array($option) && in_array($option['type'] ?? null, ['pickup', 'delivery'], true))
                ->values()
                ->map(fn (array $option, int $index) => [
                    'key' => 'option_'.$index,
                    'type' => $option['type'],
                    'label' => trim((string) ($option['label'] ?? '')),
                    'fee' => number_format(max(0, (float) ($option['fee'] ?? 0)), 2, '.', ''),
                    'details' => trim((string) ($option['details'] ?? '')) ?: null,
                ])
                ->filter(fn (array $option) => $option['label'] !== '')
                ->values()
                ->all();
        }

        $settings = $this->resolvedStorefrontSettings();
        $legacy = [];
        if ($settings['fulfillment_pickup_enabled']) {
            $legacy[] = ['key' => 'pickup', 'type' => 'pickup', 'label' => $settings['pickup_station'] ?: 'Seller pickup station', 'fee' => (string) $settings['pickup_price'], 'details' => $settings['pickup_description']];
        }
        foreach (['accra' => 'Accra', 'tema' => 'Tema'] as $area => $label) {
            if ($settings['delivery_'.$area.'_enabled']) {
                $legacy[] = ['key' => 'delivery_'.$area, 'type' => 'delivery', 'label' => $label, 'fee' => (string) $settings['delivery_'.$area.'_price'], 'details' => $settings['delivery_'.$area.'_description']];
            }
        }

        return $legacy;
    }

    public function storefrontAccentPalette(): array
    {
        $settings = $this->resolvedStorefrontSettings();
        $accent = $settings['accent'];

        if ($accent === 'custom' && preg_match('/^#[0-9a-fA-F]{6}$/', (string) $settings['custom_accent'])) {
            $custom = strtoupper($settings['custom_accent']);

            return [
                'accent' => $custom,
                'dark' => self::mixHex($custom, '#000000', 0.24),
                'tint' => self::mixHex($custom, '#FFFFFF', 0.9),
                'secondary' => self::mixHex($custom, '#D08B52', 0.5),
                'surface' => self::mixHex($custom, '#FFFFFF', 0.94),
                'ink' => self::mixHex($custom, '#111827', 0.78),
            ];
        }

        return self::STOREFRONT_ACCENTS[$accent] ?? self::STOREFRONT_ACCENTS['forest'];
    }

    private static function mixHex(string $first, string $second, float $weight): string
    {
        $first = ltrim($first, '#');
        $second = ltrim($second, '#');
        $channels = [];
        for ($index = 0; $index < 3; $index++) {
            $start = hexdec(substr($first, $index * 2, 2));
            $end = hexdec(substr($second, $index * 2, 2));
            $channels[] = str_pad(dechex((int) round($start + (($end - $start) * $weight))), 2, '0', STR_PAD_LEFT);
        }

        return '#'.strtoupper(implode('', $channels));
    }

    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base !== '' ? $base : Str::random(8);

        $suffix = 1;
        $candidate = $slug;
        while (self::where('public_slug', $candidate)->exists()) {
            $suffix++;
            $candidate = $slug.'-'.$suffix;
        }

        return $candidate;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
