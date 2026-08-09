<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserShowTest extends ApiTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->givePermission(
            $this->user,
            'users.view',
        );
    }

    public function test_user_can_be_viewed(): void
    {
        $user = User::factory()->create();

        $response = $this->apiGet("/users/{$user->id}");

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath('message', __('responses.success'))
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.first_name', $user->first_name)
            ->assertJsonPath('data.last_name', $user->last_name)
            ->assertJsonPath('data.full_name', "{$user->first_name} {$user->last_name}")
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.status', $user->status->value)
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'full_name',
                    'email',
                    'avatar',
                    'status',
                    'email_verified_at',
                    'last_login_at',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'errors',
                'meta',
            ]);
    }

    public function test_non_existing_user_returns_not_found(): void
    {
        $response = $this->apiGet('/users/01999999-9999-9999-9999-999999999999');

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 404)
            ->assertJsonPath('message', __('responses.not_found'));
    }
}
