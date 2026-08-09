<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\Api\V1\ApiTestCase;
use Tests\Feature\Api\V1\Concerns\InteractsWithPermissions;

final class UserAuthorizationTest extends ApiTestCase
{
    use InteractsWithPermissions;

    public function test_guest_cannot_list_users(): void
    {
        Auth::forgetGuards();

        $response = $this->getJson('/api/v1/users');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_without_permission_cannot_list_users(): void
    {
        $response = $this->apiGet('/users');

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);
    }

    public function test_authenticated_user_with_view_permission_can_list_users(): void
    {
        $this->givePermission(
            $this->user,
            'users.view',
        );

        User::factory()->count(3)->create();

        $response = $this->apiGet('/users');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200);
    }
}
