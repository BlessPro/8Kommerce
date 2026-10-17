<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const PLAN_FREE_TRIAL = 'FREE_TRIAL';
    public const PLAN_STARTER = 'STARTER';
    public const PLAN_GROWTH = 'GROWTH';
    public const PLAN_ENTERPRISE = 'ENTERPRISE';

    // Legacy plans (kept for backward compatibility with existing rows).
    public const PLAN_PROMOTION = 'PROMOTION';
    public const PLAN_PAYMENTS = 'PAYMENTS';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'pin_hash',
        'is_admin',
        'onboarding_state',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pin_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'pin_hash' => 'hashed',
            'is_admin' => 'boolean',
            'trial_started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'plan_started_at' => 'datetime',
            'plan_ends_at' => 'datetime',
            'suspended_at' => 'datetime',
            'onboarding_state' => 'array',
        ];
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function startTrialIfMissing(): void
    {
        if ($this->trial_started_at || $this->trial_ends_at || ($this->plan_type && $this->plan_type !== self::PLAN_FREE_TRIAL)) {
            return;
        }

        $days = (int) config('plans.trial_days', 9);
        $this->plan_type = self::PLAN_FREE_TRIAL;
        $this->trial_started_at = now();
        $this->trial_ends_at = now()->addDays($days);
        $this->save();
    }

    public function isOnTrial(): bool
    {
        if ($this->plan_type === self::PLAN_FREE_TRIAL && $this->trial_ends_at === null) {
            $this->startTrialIfMissing();
        }

        return $this->plan_type === self::PLAN_FREE_TRIAL
            && $this->trial_ends_at !== null
            && now()->lt($this->trial_ends_at);
    }

    public function trialExpired(): bool
    {
        return $this->trial_ends_at !== null && now()->gte($this->trial_ends_at);
    }

    public function hasActivePlan(): bool
    {
        $subscription = $this->relationLoaded('subscriptions')
            ? $this->subscriptions->sortByDesc('id')->first(fn (Subscription $item) => $item->grantsAccess())
            : $this->subscriptions()->latest('id')->get()->first(fn (Subscription $item) => $item->grantsAccess());
        if ($subscription) {
            return true;
        }

        if (! in_array($this->plan_type, [
            self::PLAN_STARTER,
            self::PLAN_GROWTH,
            self::PLAN_ENTERPRISE,
            self::PLAN_PROMOTION,
            self::PLAN_PAYMENTS,
        ], true)) {
            return false;
        }

        return $this->plan_ends_at !== null && now()->lt($this->plan_ends_at);
    }

    public function hasActiveAccess(): bool
    {
        return $this->isOnTrial() || $this->hasActivePlan();
    }

    public function canUsePaymentsFeature(): bool
    {
        // New pricing model: all active paid plans include payments.
        return $this->isOnTrial() || $this->hasActivePlan();
    }

    public function canUsePromotionFeatures(): bool
    {
        return $this->isOnTrial() || $this->hasActivePlan();
    }

    public function normalizedPlanType(): string
    {
        return match ($this->plan_type) {
            self::PLAN_PROMOTION => self::PLAN_STARTER,
            self::PLAN_PAYMENTS => self::PLAN_GROWTH,
            default => (string) ($this->plan_type ?: self::PLAN_FREE_TRIAL),
        };
    }

    public function productLimit(): ?int
    {
        if ($this->isOnTrial()) {
            return null;
        }

        return match ($this->normalizedPlanType()) {
            self::PLAN_STARTER => 100,
            self::PLAN_GROWTH => 300,
            self::PLAN_ENTERPRISE => null,
            default => 100,
        };
    }

    public function adminSeatLimit(): ?int
    {
        if ($this->isOnTrial()) {
            return null;
        }

        return match ($this->normalizedPlanType()) {
            self::PLAN_STARTER => 1,
            self::PLAN_GROWTH => 1,
            self::PLAN_ENTERPRISE => 1,
            default => 1,
        };
    }

    public function promotionSubmissionLimit(): int
    {
        if ($this->isOnTrial()) {
            return 1;
        }

        return match ($this->normalizedPlanType()) {
            self::PLAN_GROWTH => 2,
            self::PLAN_ENTERPRISE => 4,
            default => 1,
        };
    }

    public function commissionPercent(): string
    {
        if ($this->isOnTrial()) {
            return '0.01';
        }

        return (string) match ($this->normalizedPlanType()) {
            self::PLAN_STARTER => config('plans.starter.commission_percent', '0.019'),
            self::PLAN_GROWTH => config('plans.growth.commission_percent', '0.0125'),
            self::PLAN_ENTERPRISE => config('plans.enterprise.commission_percent', '0.01'),
            default => config('plans.payments.commission_percent', '0.01'),
        };
    }

    public function planDisplayName(): string
    {
        $plan = $this->normalizedPlanType();
        if ($plan === self::PLAN_FREE_TRIAL) {
            return 'Free Trial';
        }

        return ucwords(strtolower(str_replace('_', ' ', $plan)));
    }

    public function hasCompletedProfileOnboarding(): bool
    {
        $profile = $this->sellerProfile;

        return filled($this->email)
            && filled($profile?->business_name)
            && filled($profile?->phone);
    }

    public function sellerProfile()
    {
        return $this->hasOne(SellerProfile::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sellerNotifications()
    {
        return $this->hasMany(SellerNotification::class);
    }

    public function analyticsEvents()
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    public function twilioMessageLogs()
    {
        return $this->hasMany(TwilioMessageLog::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function adminAuditLogs()
    {
        return $this->hasMany(AdminAuditLog::class, 'admin_user_id');
    }

    public function dataDeletionRequests()
    {
        return $this->hasMany(DataDeletionRequest::class);
    }

    public function promotionSubmissions()
    {
        return $this->hasMany(PromotionSubmission::class);
    }

    public function featuredPlacements()
    {
        return $this->hasMany(FeaturedPlacement::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionInvoices()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function currentSubscription(): ?Subscription
    {
        return $this->subscriptions()->latest('id')->get()->first(fn (Subscription $item) => $item->grantsAccess())
            ?? $this->subscriptions()->latest('id')->first();
    }
}
