<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_NON_RENEWING = 'non_renewing';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id', 'plan_type', 'provider', 'provider_plan_code',
        'provider_subscription_code', 'provider_customer_code', 'provider_email_token',
        'checkout_reference', 'billing_email', 'status', 'amount', 'currency', 'interval',
        'current_period_start', 'current_period_end', 'next_payment_at', 'grace_ends_at',
        'cancel_at_period_end', 'cancelled_at', 'last_payment_at', 'failed_at',
        'last_event_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'provider_email_token' => 'encrypted',
            'amount' => 'decimal:2',
            'cancel_at_period_end' => 'boolean',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'next_payment_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'last_payment_at' => 'datetime',
            'failed_at' => 'datetime',
            'last_event_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function invoices() { return $this->hasMany(SubscriptionInvoice::class); }

    public function grantsAccess(): bool
    {
        if ($this->status === self::STATUS_ACTIVE || $this->status === self::STATUS_NON_RENEWING) {
            return $this->current_period_end?->isFuture() === true;
        }

        return $this->status === self::STATUS_PAST_DUE && $this->grace_ends_at?->isFuture();
    }
}
