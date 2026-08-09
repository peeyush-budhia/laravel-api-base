<?php

declare(strict_types=1);

namespace App\Constants;

final class PaginationConstants
{
    private function __construct()
    {
        //
    }

    /**
     * Default pagination size.
     */
    public const DEFAULT_PER_PAGE = 15;

    /**
     * Maximum allowed pagination size.
     */
    public const MAX_PER_PAGE = 100;

    /**
     * Minimum allowed pagination size.
     */
    public const MIN_PER_PAGE = 1;
}
