<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';

    /**
     * Return all enum values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Return options for dropdowns or API responses.
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status) => [
                'label' => ucfirst(strtolower($status->name)),
                'value' => $status->value,
            ],
            self::cases()
        );
    }

    /**
     * Determine whether the user can authenticate.
     */
    public function canLogin(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
        };
    }
}
