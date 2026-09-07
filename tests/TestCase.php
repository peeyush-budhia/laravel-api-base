<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $storagePath = sys_get_temp_dir().'/laravel-api-base/storage';

        foreach ([
            $storagePath,
            $storagePath.'/app/public',
            $storagePath.'/framework/cache',
            $storagePath.'/framework/sessions',
            $storagePath.'/framework/testing',
            $storagePath.'/framework/views',
        ] as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
        }

        $app->useStoragePath($storagePath);

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Assert that a value is formatted as ISO 8601.
     */
    protected function assertIso8601DateTime(?string $value): void
    {
        $this->assertNotNull($value);

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})$/',
            $value,
        );
    }
}
