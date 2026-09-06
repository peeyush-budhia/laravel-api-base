<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;

final class AuditValueNormalizer
{
    /**
     * Normalize datetime values inside an audit snapshot.
     *
     * @param  array<string, mixed>|null  $values
     * @param  array<int, string>  $dateAttributes
     * @return array<string, mixed>|null
     */
    public static function normalize(
        ?array $values,
        array $dateAttributes = [],
    ): ?array {
        if ($values === null) {
            return null;
        }

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = self::normalize($value, $dateAttributes);

                continue;
            }

            if (! self::isDateAttribute($key, $dateAttributes)) {
                continue;
            }

            $values[$key] = self::normalizeDate($value);
        }

        return $values;
    }

    /**
     * @param  array<int, string>  $dateAttributes
     */
    private static function isDateAttribute(
        int|string $key,
        array $dateAttributes,
    ): bool {
        return is_string($key)
            && (str_ends_with($key, '_at')
                || in_array($key, $dateAttributes, true));
    }

    private static function normalizeDate(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (! is_string($value) || $value === '') {
            return $value;
        }

        try {
            return CarbonImmutable::parse($value)->toIso8601String();
        } catch (InvalidFormatException) {
            return $value;
        }
    }
}
