<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id', 'user_id', 'provider', 'provider_invoice_code', 'reference',
        'status', 'amount', 'currency', 'due_at', 'paid_at', 'failed_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2', 'due_at' => 'datetime', 'paid_at' => 'datetime',
            'failed_at' => 'datetime', 'metadata' => 'array',
        ];
    }

    public function subscription() { return $this->belongsTo(Subscription::class); }
    public function user() { return $this->belongsTo(User::class); }
}
