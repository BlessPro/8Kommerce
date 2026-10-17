<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionSubmission extends Model
{
    use HasFactory;

    public const STATUSES = [
        'submitted' => 'Submitted',
        'under_review' => 'Under review',
        'selected' => 'Selected',
        'preparing' => 'Content preparation',
        'scheduled' => 'Scheduled',
        'published' => 'Published',
        'not_selected' => 'Not selected',
        'completed' => 'Completed',
    ];

    public const PURPOSES = [
        'new_product' => 'New product',
        'back_in_stock' => 'Back in stock',
        'bestseller' => 'Bestseller',
        'offer' => 'Sale or offer',
        'seasonal' => 'Seasonal product',
        'brand_awareness' => 'Brand awareness',
    ];

    public const CHANNELS = [
        'whatsapp' => 'WhatsApp Channel',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'discover' => 'Discover article',
        'marketplace' => 'Marketplace feature',
        'bundle' => 'Social bundle',
    ];

    protected $fillable = ['user_id', 'product_id', 'purpose', 'seller_note', 'status', 'channel', 'tracking_code', 'admin_notes', 'published_url', 'scheduled_at', 'published_at'];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'published_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
