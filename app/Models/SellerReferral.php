<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerReferral extends Model
{
    use HasFactory;

    public const STATUS_JOINED = 'joined';

    protected $fillable = [
        'referral_link_id',
        'referrer_id',
        'referred_user_id',
        'status',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
        ];
    }

    public function referralLink()
    {
        return $this->belongsTo(ReferralLink::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
