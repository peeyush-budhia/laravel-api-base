<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserRestoreTest extends ApiTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->givePermission(
            $this->user,
            'users.restore',
        );
    }

    public function test_soft_deleted_user_can_be_restored(): void
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);

        $response = $this->apiPatch(
            "/users/{$user->id}/restore"
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath('message', __('responses.restored'))
            ->assertJsonPath('data.id', $user->id);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_user_that_is_not_deleted_can_still_be_restored(): void
    {
        $user = User::factory()->create();

        $response = $this->apiPatch(
            "/users/{$user->id}/restore"
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath('message', __('responses.restored'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_non_existing_user_cannot_be_restored(): void
    {
        $response = $this->apiPatch(
            '/users/01999999-9999-9999-9999-999999999999/restore'
        );

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 404)
            ->assertJsonPath('message', __('responses.not_found'));
    }
}
