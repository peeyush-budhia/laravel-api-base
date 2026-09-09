<?php

declare(strict_types=1);

namespace Tests\Feature\Documentation;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class DisabledApiDocumentationTest extends TestCase
{
    public function createApplication(): Application
    {
        putenv('SCRAMBLE_DOCS_ENABLED=false');
        $_ENV['SCRAMBLE_DOCS_ENABLED'] = 'false';
        $_SERVER['SCRAMBLE_DOCS_ENABLED'] = 'false';

        return parent::createApplication();
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_documentation_routes_are_not_registered_when_disabled(): void
    {
        $this->assertFalse(Route::has('scramble.docs.ui'));
        $this->assertFalse(Route::has('scramble.docs.document'));
    }
}
