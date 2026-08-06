<?php

declare(strict_types=1);

namespace Tests\Feature;

class HealthTest extends ApiTestCase
{
    public function test_health_endpoint(): void
    {
        $response = $this->getJson(
            '/api/v1/health',
            $this->jsonHeaders(),
        );

        $response->assertOk();

        $this->assertApiSuccess($response);
    }
}
