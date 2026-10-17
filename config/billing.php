<?php

return [
    // Never enable this in production. It exists only for local UI testing.
    'manual_activation_enabled' => (bool) env('BILLING_MANUAL_ACTIVATION_ENABLED', false),

    // Recurring billing remains off until the complete Paystack webhook lifecycle
    // and subscription persistence are enabled together.
    'provider' => env('BILLING_PROVIDER', 'paystack'),
    'recurring_enabled' => (bool) env('BILLING_RECURRING_ENABLED', false),
    'plan_codes' => [
        'STARTER' => env('PAYSTACK_PLAN_STARTER'),
        'GROWTH' => env('PAYSTACK_PLAN_GROWTH'),
        'ENTERPRISE' => env('PAYSTACK_PLAN_ENTERPRISE'),
    ],
];
