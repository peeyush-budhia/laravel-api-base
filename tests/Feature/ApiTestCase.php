<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Authenticate the given user or create one.
     */
    protected function authenticate(?User $user = null): User
    {
        $user ??= User::factory()->create();

        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * Standard JSON headers.
     */
    protected function jsonHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    /**
     * Assert successful API response.
     */
    protected function assertApiSuccess($response): void
    {
        $response
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data',
                'errors',
                'meta',
            ]);
    }

    /**
     * Assert unauthorized API response.
     */
    protected function assertApiUnauthorized($response): void
    {
        $response
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'status' => 401,
            ]);
    }

    /**
     * Assert validation API response.
     */
    protected function assertApiValidation($response): void
    {
        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status' => 422,
            ]);
    }
}
