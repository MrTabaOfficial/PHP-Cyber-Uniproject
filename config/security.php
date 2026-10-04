<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication For Administrators
    |--------------------------------------------------------------------------
    |
    | When enabled, an administrator who has not turned on two-factor
    | authentication is sent to the setup page instead of the admin area.
    |
    */

    'admin_requires_two_factor' => (bool) env('SECURITY_ADMIN_REQUIRES_2FA', true),

    /*
    |--------------------------------------------------------------------------
    | Audit Log
    |--------------------------------------------------------------------------
    |
    | Security events (sign-ins, failures, two-factor and role changes) are
    | written to the security_events table. Rows older than the retention
    | period are removed by the scheduled "model:prune" command.
    |
    */

    'audit_log' => [
        'retention_days' => (int) env('SECURITY_AUDIT_RETENTION_DAYS', 90),
    ],

];
