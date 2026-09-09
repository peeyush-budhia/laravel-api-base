<?php

declare(strict_types=1);

namespace App\Enums;

enum AuditEvent: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';
    case ForceDeleted = 'force_deleted';
    case PermissionsSynced = 'permissions_synced';
    case RolesSynced = 'roles_synced';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Created',
            self::Updated => 'Updated',
            self::Deleted => 'Deleted',
            self::Restored => 'Restored',
            self::ForceDeleted => 'Force Deleted',
            self::PermissionsSynced => 'Permissions Synced',
            self::RolesSynced => 'Roles Synced',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Created => 'success',
            self::Updated, self::PermissionsSynced, self::RolesSynced => 'info',
            self::Deleted, self::ForceDeleted => 'danger',
            self::Restored => 'warning',
        };
    }
}
