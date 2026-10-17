<?php

return [
    'dispute_refund_percent' => (float) env('ORDER_DISPUTE_REFUND_PERCENT', 0.90),
    'support_phone' => (string) env('ORDER_SUPPORT_PHONE', ''),
    'seller_response_hours' => (int) env('ORDER_SELLER_RESPONSE_HOURS', 24),
    'legacy_reference_tracking_enabled' => filter_var(env('ORDER_LEGACY_REFERENCE_TRACKING_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
];
