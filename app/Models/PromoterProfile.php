<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PromoterProfile extends Model
{
    use HasUuids;

    public const STATUS_ACTIVE = 'active';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id', 'public_code', 'name', 'phone', 'access_pin_hash', 'city', 'channels', 'note',
        'status', 'visits_count', 'conversions_count', 'conversion_amount', 'last_visited_at', 'last_login_at',
    ];

    protected $hidden = ['access_pin_hash'];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'conversion_amount' => 'decimal:2',
            'last_visited_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversions()
    {
        return $this->hasMany(PromoterConversion::class);
    }

    public function visits()
    {
        return $this->hasMany(PromoterVisit::class);
    }
}
