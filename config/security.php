<?php

return [

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
