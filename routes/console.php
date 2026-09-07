<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Register your custom Artisan commands here.

Schedule::command('auth:clear-resets')->daily();
Schedule::command('queue:prune-failed', [
    '--hours' => (string) (config('maintenance.failed_job_retention_days') * 24),
])->daily();
Schedule::command('audit:prune', [
    '--days' => (string) config('maintenance.audit_log_retention_days'),
])->daily();
