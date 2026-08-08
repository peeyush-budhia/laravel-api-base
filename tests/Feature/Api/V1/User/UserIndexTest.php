<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;

final class UserIndexTest extends ApiTestCase
{
    public function test_users_can_be_listed(): void
    {
        User::factory()->count(10)->create();

        $response = $this->apiGet('/users');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', __('responses.success'))
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data' => [
                    '*' => [
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
                ],
                'meta',
            ]);
    }

    public function test_users_are_paginated(): void
    {
        User::factory()->count(30)->create();

        $response = $this->apiGet('/users');

        //         dd($response->json());

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);
    }

    public function test_users_can_be_paginated_with_custom_per_page(): void
    {
        User::factory()->count(30)->create();

        $response = $this->apiGet('/users?per_page=5');

        $response
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5);
    }
}
