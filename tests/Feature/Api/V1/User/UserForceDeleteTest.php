<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserForceDeleteTest extends ApiTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->givePermission(
            $this->user,
            'users.delete',
        );
    }

    public function test_soft_deleted_user_can_be_permanently_deleted(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Deleted',
        ]);

        $user->delete();

        $response = $this->apiDelete(
            "/users/{$user->id}/force",
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'message',
                __('responses.deleted'),
            );

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_active_user_cannot_be_force_deleted_through_deleted_user_flow(): void
    {
        $user = User::factory()->create();

        $response = $this->apiDelete(
            "/users/{$user->id}/force",
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_force_delete_returns_not_found_for_unknown_user(): void
    {
        $response = $this->apiDelete(
            '/users/non-existent-id/force',
        );

        $response->assertNotFound();
    }
}
