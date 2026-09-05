<?php

declare(strict_types=1);

namespace App\Traits;

use DateTimeInterface;

trait SerializesDatesToIso8601
{
    /**
     * Serialize dates using ISO 8601.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->toIso8601String();
    }
}
