<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;

final class UserDeleteTest extends ApiTestCase
{
    public function test_user_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $response = $this->apiDelete("/users/{$user->id}");

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath('message', __('responses.deleted'));

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_non_existing_user_cannot_be_deleted(): void
    {
        $response = $this->apiDelete(
            '/users/01999999-9999-9999-9999-999999999999'
        );

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 404)
            ->assertJsonPath('message', __('responses.not_found'));
    }
}
