<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoterConversion extends Model
{
    protected $fillable = [
        'promoter_profile_id', 'payment_id', 'order_id', 'amount', 'currency', 'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'converted_at' => 'datetime',
        ];
    }

    public function promoter()
    {
        return $this->belongsTo(PromoterProfile::class, 'promoter_profile_id');
    }
}
