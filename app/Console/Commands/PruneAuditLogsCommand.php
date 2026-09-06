<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

final class PruneAuditLogsCommand extends Command
{
    protected $signature = 'audit:prune
        {--days= : Delete audit logs older than this many days}';

    protected $description = 'Delete audit logs older than the configured retention period';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('maintenance.audit_log_retention_days'));

        if ($days < 1) {
            $this->error('The audit retention period must be at least one day.');

            return self::FAILURE;
        }

        $deleted = AuditLog::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Deleted {$deleted} audit log(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
