<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class CorsTest extends TestCase
{
    public function test_configured_frontend_origin_can_preflight_the_api(): void
    {
        $origin = (string) config('cors.allowed_origins.0');

        $response = $this->withHeaders([
            'Origin' => $origin,
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/v1/health');

        $response
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', $origin)
            ->assertHeader('Access-Control-Allow-Methods');
    }
}
