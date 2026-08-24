<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Auditable
{
    public function auditLogs(): MorphMany;

    public function getAuditEventName(): string;

    public function getAuditExcludeAttributes(): array;
}
