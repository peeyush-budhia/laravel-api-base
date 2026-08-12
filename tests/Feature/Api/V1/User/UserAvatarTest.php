<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Api\V1\ApiTestCase;

final class UserAvatarTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_authenticated_user_can_update_avatar(): void
    {
        $avatar = UploadedFile::fake()->image(
            'avatar.jpg',
            300,
            300,
        );

        $response = $this->post(
            '/api/v1/profile/avatar',
            [
                'avatar' => $avatar,
            ],
            [
                'Accept' => 'application/json',
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonPath(
                'message',
                __('responses.updated'),
            )
            ->assertJsonPath(
                'data.id',
                $this->user->id,
            );

        $avatarPath = $this->user->fresh()->avatar;

        $this->assertNotNull($avatarPath);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        $disk->assertExists($avatarPath);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'avatar' => $avatarPath,
        ]);
    }

    public function test_avatar_can_be_png(): void
    {
        $avatar = UploadedFile::fake()->image(
            'avatar.png',
            300,
            300,
        );

        $response = $this->post(
            '/api/v1/profile/avatar',
            [
                'avatar' => $avatar,
            ],
            [
                'Accept' => 'application/json',
            ],
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200);

        $avatarPath = $this->user->fresh()->avatar;

        $this->assertNotNull($avatarPath);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        $disk->assertExists($avatarPath);
    }

    public function test_avatar_is_required(): void
    {
        $response = $this->post(
            '/api/v1/profile/avatar',
            [],
            [
                'Accept' => 'application/json',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure([
                'errors' => [
                    'avatar',
                ],
            ]);
    }

    public function test_avatar_must_be_an_image(): void
    {
        $file = UploadedFile::fake()->create(
            'document.pdf',
            100,
            'application/pdf',
        );

        $response = $this->post(
            '/api/v1/profile/avatar',
            [
                'avatar' => $file,
            ],
            [
                'Accept' => 'application/json',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure([
                'errors' => [
                    'avatar',
                ],
            ]);
    }

    public function test_avatar_must_not_exceed_5_mb(): void
    {
        $avatar = UploadedFile::fake()->create(
            'large-avatar.jpg',
            5121,
            'image/jpeg',
        );

        $response = $this->post(
            '/api/v1/profile/avatar',
            [
                'avatar' => $avatar,
            ],
            [
                'Accept' => 'application/json',
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure([
                'errors' => [
                    'avatar',
                ],
            ]);
    }

    public function test_guest_cannot_update_avatar(): void
    {
        $this->app['auth']->forgetGuards();

        $avatar = UploadedFile::fake()->image(
            'avatar.jpg',
            300,
            300,
        );

        $response = $this->post(
            '/api/v1/profile/avatar',
            [
                'avatar' => $avatar,
            ],
            [
                'Accept' => 'application/json',
            ],
        );

        $response
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 401);
    }
}
