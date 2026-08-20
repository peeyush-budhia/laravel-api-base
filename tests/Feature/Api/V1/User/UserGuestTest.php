<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserGuestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_users(): void
    {
        $response = $this->getJson('/api/v1/users');

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_create_user(): void
    {
        $response = $this->postJson('/api/v1/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_show_user(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_update_user(): void
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/v1/users/{$user->id}", [
            'first_name' => 'Updated',
        ]);

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_restore_user(): void
    {
        $user = User::factory()->create();

        $user->delete();

        $response = $this->patchJson(
            "/api/v1/users/{$user->id}/restore"
        );

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_patch_user(): void
    {
        $user = User::factory()->create();

        $response = $this->patchJson("/api/v1/users/{$user->id}", [
            'first_name' => 'Updated',
        ]);

        $response->assertUnauthorized();
    }
}
