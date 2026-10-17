<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditorialArticle extends Model
{
    use HasFactory;

    public const TYPES = [
        'seller_spotlight' => 'Seller spotlight',
        'product_collection' => 'Product collection',
        'buying_guide' => 'Buying guide',
        'business_intro' => 'Business introduction',
        'seasonal_guide' => 'Seasonal guide',
        'seller_of_month' => 'Seller of the Month',
        'product_launch' => 'Product launch',
    ];

    protected $fillable = ['seller_user_id', 'created_by_admin_id', 'type', 'title', 'slug', 'excerpt', 'body', 'cover_image_path', 'product_ids', 'status', 'seo_title', 'seo_description', 'published_at'];

    protected function casts(): array
    {
        return ['product_ids' => 'array', 'published_at' => 'datetime'];
    }

    public function seller() { return $this->belongsTo(User::class, 'seller_user_id'); }
    public function author() { return $this->belongsTo(User::class, 'created_by_admin_id'); }

    public function products()
    {
        return Product::query()->whereIn('id', $this->product_ids ?? [])->where('is_active', true)->get();
    }

    public function resolvedCoverImagePath(): ?string
    {
        if ($this->cover_image_path) {
            return $this->cover_image_path;
        }

        return $this->seller?->sellerProfile?->resolvedStorefrontSettings()['hero_image_path'] ?? null;
    }
}
