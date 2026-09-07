<?php

declare(strict_types=1);

return [
    'failed_job_retention_days' => (int) env('FAILED_JOB_RETENTION_DAYS', 3),

    'audit_log_retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 30),
];
