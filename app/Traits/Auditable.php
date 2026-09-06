<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\AuditEvent;
use App\Models\AuditLog;
use App\Support\AuditValueNormalizer;
use App\Support\DashboardCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Auditable
{
    /**
     * Boot the auditable trait.
     */
    public static function bootAuditable(): void
    {
        static::created(
            static function (Model $model): void {
                $model->createAuditLog(AuditEvent::Created);
            }
        );

        static::updated(
            static function (Model $model): void {
                $model->createAuditLog(AuditEvent::Updated);
            }
        );

        static::deleted(
            static function (Model $model): void {
                $event = method_exists($model, 'isForceDeleting')
                    && $model->isForceDeleting()
                    ? AuditEvent::ForceDeleted
                    : AuditEvent::Deleted;

                $model->createAuditLog($event);
            }
        );

        /*
         * Only models using SoftDeletes support the restored event.
         */
        if (in_array(
            SoftDeletes::class,
            class_uses_recursive(static::class),
            true,
        )) {
            static::restored(
                static function (Model $model): void {
                    $model->createAuditLog(AuditEvent::Restored);
                }
            );
        }
    }

    /**
     * Get audit logs for this model.
     *
     * @return MorphMany<AuditLog, $this>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(
            AuditLog::class,
            'auditable',
        );
    }

    /**
     * Get the audit event model name.
     */
    public function getAuditEventName(): string
    {
        return class_basename($this);
    }

    /**
     * Get attributes excluded from audit logs.
     *
     * @return array<int, string>
     */
    public function getAuditExcludeAttributes(): array
    {
        return [
            'updated_at',
            'last_login_at',
            'password',
            'remember_token',
        ];
    }

    /**
     * Create an audit log.
     */
    protected function createAuditLog(AuditEvent $event): void
    {
        $oldValues = $this->getOriginal();
        $newValues = $this->getAttributes();

        $excluded = $this->getAuditExcludeAttributes();

        $oldValues = array_diff_key(
            $oldValues,
            array_flip($excluded),
        );

        $newValues = array_diff_key(
            $newValues,
            array_flip($excluded),
        );

        if ($event === AuditEvent::Created) {
            $oldValues = [];
        }

        if (
            in_array(
                $event,
                [
                    AuditEvent::Deleted,
                    AuditEvent::ForceDeleted,
                ],
                true,
            )
        ) {
            $newValues = [];
        }

        if ($event === AuditEvent::Updated) {
            $changes = array_diff_key(
                $this->getChanges(),
                array_flip($excluded),
            );

            if ($changes === []) {
                return;
            }

            $newValues = array_intersect_key(
                $newValues,
                $changes,
            );

            $oldValues = array_intersect_key(
                $oldValues,
                $changes,
            );
        }

        $dateAttributes = $this->getAuditDateAttributes();
        $oldValues = AuditValueNormalizer::normalize(
            $oldValues,
            $dateAttributes,
        );
        $newValues = AuditValueNormalizer::normalize(
            $newValues,
            $dateAttributes,
        );

        $this->writeAuditLog(
            $event,
            $oldValues,
            $newValues,
        );
    }

    /**
     * Record a change to a model relationship.
     *
     * @param  array<int, string>  $oldValues
     * @param  array<int, string>  $newValues
     */
    public function auditRelationshipChange(
        AuditEvent $event,
        string $relationship,
        array $oldValues,
        array $newValues,
    ): void {
        if ($oldValues === $newValues) {
            return;
        }

        $this->writeAuditLog(
            $event,
            [$relationship => $oldValues],
            [$relationship => $newValues],
        );
    }

    /**
     * Persist an audit record and invalidate dashboard caches after commit.
     *
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    private function writeAuditLog(
        AuditEvent $event,
        array $oldValues,
        array $newValues,
    ): void {
        $this->auditLogs()->create([
            'user_id' => auth()->id(),
            'event' => $event->value,
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => (string) $this->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        DashboardCache::forgetAfterCommit($this->getConnectionName());
    }

    /**
     * Get model attributes that are cast to dates.
     *
     * @return array<int, string>
     */
    private function getAuditDateAttributes(): array
    {
        $dateAttributes = array_filter(
            $this->getDates(),
            is_string(...),
        );

        foreach ($this->getCasts() as $attribute => $cast) {
            if (! is_string($cast)) {
                continue;
            }

            $castType = explode(':', $cast, 2)[0];

            if (in_array($castType, [
                'date',
                'datetime',
                'immutable_date',
                'immutable_datetime',
            ], true)) {
                $dateAttributes[] = $attribute;
            }
        }

        return array_values(array_unique($dateAttributes));
    }
}
