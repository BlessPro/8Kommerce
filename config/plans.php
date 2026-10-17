<?php

return [
    'trial_days' => 9,

    'starter' => [
        'name' => 'Starter',
        'price_monthly' => 15,
        'price_text' => 'GHS 15 / month',
        'best_for' => 'Individuals starting out',
        'transaction_fee' => '1.9%',
        'commission_percent' => '0.019',
        'limits' => [
            'products' => 100,
            'orders' => 'Unlimited',
            'admins' => 1,
            'team_members' => 'No',
        ],
        'features' => [
            'Public storefront + product pages',
            'Unlimited orders',
            'Basic dashboard',
            '1 admin user',
            'Cart + checkout + payment links',
            'Verified customer ratings',
            '1 promotion nomination each month',
            'Weekly roundup eligibility',
        ],
    ],

    'growth' => [
        'name' => 'Growth',
        'price_monthly' => 30,
        'price_text' => 'GHS 30 / month',
        'best_for' => 'Growing sellers',
        'transaction_fee' => '1.25%',
        'commission_percent' => '0.0125',
        'limits' => [
            'products' => 300,
            'orders' => 'Unlimited',
            'admins' => 1,
            'team_members' => 'No',
        ],
        'features' => [
            'Everything in Starter',
            'More products (up to 300)',
            '1 admin user',
            'Priority support',
            'Stronger operational limits',
            '2 promotion nominations each month',
            '3-day marketplace feature each month',
            'Seller story eligibility',
        ],
    ],

    'enterprise' => [
        'name' => 'Enterprise',
        'price_monthly' => 70,
        'price_text' => 'GHS 70 / month',
        'best_for' => 'Businesses and teams',
        'transaction_fee' => '1%',
        'commission_percent' => '0.01',
        'limits' => [
            'products' => 'Unlimited',
            'orders' => 'Unlimited',
            'admins' => 1,
            'team_members' => 'No',
        ],
        'features' => [
            'Everything in Growth',
            'Unlimited products',
            'Single owner account',
            'Best performance limits',
            'Top-priority support',
            '4 promotion nominations each month',
            '7-day marketplace feature each month',
            'Priority quarterly seller story',
        ],
        'sales_note' => 'Contact sales for assisted onboarding.',
    ],

    'payments' => [
        'price_range' => 'GHS 30-50 / month',
        // Platform commission charged on each successful payment (stored internally).
        'commission_percent' => '0.01', // 1%
        'commission_text' => '1% per successful payment',
    ],

    'promotion' => [
        'price_range' => 'GHS 60-100 / month',
        'commission_text' => 'No payment commission (payments disabled)',
    ],

];
