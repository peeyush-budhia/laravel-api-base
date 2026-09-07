<?php

namespace App\Contracts;

use App\Enums\AuditEvent;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Auditable
{
    public function auditLogs(): MorphMany;

    public function getAuditEventName(): string;

    public function getAuditExcludeAttributes(): array;

    /**
     * @param  array<int, string>  $oldValues
     * @param  array<int, string>  $newValues
     */
    public function auditRelationshipChange(
        AuditEvent $event,
        string $relationship,
        array $oldValues,
        array $newValues,
    ): void;
}
