<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoterVisit extends Model
{
    protected $fillable = [
        'promoter_profile_id', 'product_id', 'destination', 'visitor_hash', 'referrer_host', 'visited_at',
    ];

    protected function casts(): array
    {
        return ['visited_at' => 'datetime'];
    }

    public function promoter()
    {
        return $this->belongsTo(PromoterProfile::class, 'promoter_profile_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
