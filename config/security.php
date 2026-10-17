<?php

return [
    'retention' => [
        'webhook_days' => (int) env('SECURITY_WEBHOOK_RETENTION_DAYS', 180),
        'message_log_days' => (int) env('SECURITY_MESSAGE_LOG_RETENTION_DAYS', 90),
        'analytics_days' => (int) env('SECURITY_ANALYTICS_RETENTION_DAYS', 365),
    ],
];
