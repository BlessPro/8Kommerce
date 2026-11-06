<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'visits_count',
        'last_visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visits_count' => 'integer',
            'last_visited_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrals()
    {
        return $this->hasMany(SellerReferral::class);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
