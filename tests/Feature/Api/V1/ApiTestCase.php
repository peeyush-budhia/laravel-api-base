<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $baseUri = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        Sanctum::actingAs($this->user);
    }

    protected function apiGet(string $uri, array $headers = [])
    {
        return $this->getJson(
            $this->baseUri.$uri,
            $headers,
        );
    }

    protected function apiPost(
        string $uri,
        array $data = [],
        array $headers = [],
    ) {
        return $this->postJson(
            $this->baseUri.$uri,
            $data,
            $headers,
        );
    }

    protected function apiPut(
        string $uri,
        array $data = [],
        array $headers = [],
    ) {
        return $this->putJson(
            $this->baseUri.$uri,
            $data,
            $headers,
        );
    }

    protected function apiPatch(
        string $uri,
        array $data = [],
        array $headers = [],
    ) {
        return $this->patchJson(
            $this->baseUri.$uri,
            $data,
            $headers,
        );
    }

    protected function apiDelete(
        string $uri,
        array $headers = [],
    ) {
        return $this->deleteJson(
            $this->baseUri.$uri,
            [],
            $headers,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function validUserData(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => UserStatus::ACTIVE->value,
        ], $overrides);
    }
}
