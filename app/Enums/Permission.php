<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    case DASHBOARD_VIEW = 'dashboard.view';

    case AUDIT_LOGS_VIEW = 'audit-logs.view';

    case USERS_VIEW = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_UPDATE = 'users.update';
    case USERS_DELETE = 'users.delete';
    case USERS_RESTORE = 'users.restore';

    case ROLES_VIEW = 'roles.view';
    case ROLES_CREATE = 'roles.create';
    case ROLES_UPDATE = 'roles.update';
    case ROLES_DELETE = 'roles.delete';
    case ROLES_MANAGE_PERMISSIONS = 'roles.manage-permissions';
}
