<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';

    /**
     * Determine whether the user can authenticate.
     */
    public function canLogin(): bool
    {
        return $this === self::ACTIVE;
    }
}
