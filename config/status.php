<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public incident notice
    |--------------------------------------------------------------------------
    |
    | This lightweight switch lets operations publish a clear notice without
    | exposing infrastructure details. It can later be replaced by an external
    | status provider while the public /status address stays unchanged.
    |
    */
    'incident' => [
        'active' => env('STATUS_INCIDENT_ACTIVE', false),
        'severity' => env('STATUS_INCIDENT_SEVERITY', 'maintenance'),
        'title' => env('STATUS_INCIDENT_TITLE', ''),
        'message' => env('STATUS_INCIDENT_MESSAGE', ''),
        'updated_at' => env('STATUS_INCIDENT_UPDATED_AT'),
    ],
];
