<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaitlistEntry extends Model
{
    public const STATUS_WAITING = 'waiting';

    protected $fillable = ['phone', 'status', 'source', 'joined_at'];

    protected function casts(): array
    {
        return ['joined_at' => 'datetime'];
    }
}
