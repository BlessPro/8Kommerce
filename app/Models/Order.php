<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    use HasUuids;

    public const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';

    public const STATUS_PAID = 'PAID';

    public const STATUS_ACCEPTED = 'ACCEPTED';

    public const STATUS_PROCESSING = 'PROCESSING';

    public const STATUS_READY = 'READY';

    public const STATUS_SHIPPED = 'SHIPPED';

    public const STATUS_DELIVERED = 'DELIVERED';

    public const STATUS_CANNOT_FULFILL = 'CANNOT_FULFILL';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_DISPUTED_PENDING_ADMIN = 'DISPUTED_PENDING_ADMIN';

    public const STATUS_REFUNDED = 'REFUNDED';

    public const STATUS_COMPLAINT_REJECTED = 'COMPLAINT_REJECTED';

    public const STATUS_TRASHED = 'TRASHED';

    public const FULFILLMENT_DELIVERY = 'delivery';

    public const FULFILLMENT_PICKUP = 'pickup';

    public const FULFILLMENT_SERVICE = 'service';

    public const REFUND_REQUESTED = 'REQUESTED';

    public const REFUND_PROCESSING = 'PROCESSING';

    public const REFUND_COMPLETED = 'COMPLETED';

    public const REFUND_FAILED = 'FAILED';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'reference',
        'status',
        'payment_status',
        'refund_status',
        'customer_name',
        'customer_phone',
        'customer_location',
        'delivery_required',
        'fulfillment_type',
        'delivery_note',
        'subtotal',
        'coupon_code',
        'discount_amount',
        'total',
        'currency',
        'paid_at',
        'seller_response_due_at',
        'accepted_at',
        'processing_at',
        'ready_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_required' => 'boolean',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'seller_response_due_at' => 'datetime',
            'accepted_at' => 'datetime',
            'processing_at' => 'datetime',
            'ready_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public static function fulfillmentStatuses(): array
    {
        return [
            self::STATUS_PAID,
            self::STATUS_ACCEPTED,
            self::STATUS_PROCESSING,
            self::STATUS_READY,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_COMPLETED,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function feedbackTokens()
    {
        return $this->hasMany(OrderFeedbackToken::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(OrderFeedback::class);
    }
}
