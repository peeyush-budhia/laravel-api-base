<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Enums\UserStatus;
use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;

final class UserStatusTest extends ApiTestCase
{
    public function test_active_user_can_be_marked_inactive(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->apiPatch("/users/{$user->id}/status", [
            'status' => UserStatus::INACTIVE->value,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath('message', __('responses.status_changed'))
            ->assertJsonPath('data.status', UserStatus::INACTIVE->value);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => UserStatus::INACTIVE->value,
        ]);
    }

    public function test_inactive_user_can_be_marked_active(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::INACTIVE,
        ]);

        $response = $this->apiPatch("/users/{$user->id}/status", [
            'status' => UserStatus::ACTIVE->value,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', UserStatus::ACTIVE->value);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => UserStatus::ACTIVE->value,
        ]);
    }

    public function test_invalid_status_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $response = $this->apiPatch("/users/{$user->id}/status", [
            'status' => 'invalid-status',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_guest_cannot_change_status(): void
    {
        auth()->forgetGuards();

        $user = User::factory()->create();

        $response = $this->json(
            'PATCH',
            "/api/v1/users/{$user->id}/status",
            [
                'status' => UserStatus::INACTIVE->value,
            ]
        );

        $response->assertUnauthorized();
    }

    public function test_non_existing_user_cannot_change_status(): void
    {
        $response = $this->apiPatch(
            '/users/01999999-9999-9999-9999-999999999999/status',
            [
                'status' => UserStatus::INACTIVE->value,
            ]
        );

        $response->assertNotFound();
    }
}
