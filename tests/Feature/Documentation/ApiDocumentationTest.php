<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_openapi_documentation_endpoint_is_available(): void
    {
        $response = $this->getJson('/docs/api.json');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_openapi_documentation_has_expected_version(): void
    {
        $response = $this->getJson('/docs/api.json');

        $response->assertOk();

        $response->assertJsonPath(
            'info.version',
            '0.7.0'
        );
    }

    public function test_openapi_documentation_has_api_server(): void
    {
        $response = $this->getJson('/docs/api.json');

        $response->assertOk();

        $response->assertJsonPath(
            'servers.0.url',
            config('app.url').'/api/v1'
        );
    }

    public function test_openapi_documentation_contains_expected_api_paths(): void
    {
        $response = $this->getJson('/docs/api.json');

        $response->assertOk();

        $paths = $response->json('paths');

        $expectedPaths = [
            '/health',
            '/auth/login',
            '/auth/forgot-password',
            '/auth/reset-password',
            '/auth/logout',
            '/auth/me',
            '/auth/change-password',
            '/roles',
            '/roles/permissions',
            '/roles/{role}/permissions',
            '/roles/{role}',
            '/profile',
            '/profile/avatar',
            '/users',
            '/users/{user}',
            '/users/{id}',
            '/users/{user}/restore',
            '/users/{id}/force',
        ];

        foreach ($expectedPaths as $path) {
            $this->assertArrayHasKey(
                $path,
                $paths,
                "OpenAPI documentation does not contain path [{$path}]."
            );
        }
    }

    public function test_openapi_documentation_contains_bearer_security_scheme(): void
    {
        $response = $this->getJson('/docs/api.json');

        $response->assertOk();

        $response->assertJsonPath(
            'components.securitySchemes.http.type',
            'http'
        );

        $response->assertJsonPath(
            'components.securitySchemes.http.scheme',
            'bearer'
        );
    }
}
