<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class ExceptionHandlerTest extends TestCase
{
    public function test_production_api_errors_do_not_expose_http_exception_details(): void
    {
        config(['app.debug' => false]);

        $response = ExceptionHandler::render(
            Request::create('/api/v1/test'),
            new HttpException(500, 'database credentials leaked'),
        );

        $this->assertSame(__('responses.server_error'), $response->getData(true)['message']);
    }
}
