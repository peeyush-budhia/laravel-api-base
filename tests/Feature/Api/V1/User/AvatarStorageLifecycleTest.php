<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Mockery;
use RuntimeException;
use Tests\TestCase;

final class AvatarStorageLifecycleTest extends TestCase
{
    use DatabaseMigrations;

    private FilesystemAdapter $disk;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $this->disk = $disk;
    }

    public function test_replacing_an_avatar_deletes_the_owned_previous_file(): void
    {
        $user = User::factory()->create([
        ]);
        $oldPath = "avatars/{$user->id}/old.jpg";
        $this->disk->put($oldPath, 'old avatar');
        $user->forceFill(['avatar' => $oldPath])->saveQuietly();
        Sanctum::actingAs($user);

        $this->post('/api/v1/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('new.jpg'),
        ], [
            'Accept' => 'application/json',
        ])->assertOk();

        $newPath = $user->fresh()->avatar;

        $this->assertIsString($newPath);
        $this->assertStringStartsWith("avatars/{$user->id}/", $newPath);
        $this->disk->assertExists($newPath);
        $this->disk->assertMissing($oldPath);
    }

    public function test_replacing_an_avatar_does_not_delete_an_unowned_path(): void
    {
        $user = User::factory()->create([
        ]);
        $foreignPath = 'avatars/another-user/private.jpg';
        $this->disk->put($foreignPath, 'foreign avatar');
        $user->forceFill(['avatar' => $foreignPath])->saveQuietly();
        Sanctum::actingAs($user);

        $this->post('/api/v1/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('new.jpg'),
        ], [
            'Accept' => 'application/json',
        ])->assertOk();

        $this->disk->assertExists($foreignPath);
    }

    public function test_new_file_is_removed_when_the_database_write_fails(): void
    {
        $user = User::factory()->create([
        ]);
        $oldPath = "avatars/{$user->id}/old.jpg";
        $this->disk->put($oldPath, 'old avatar');
        $user->forceFill(['avatar' => $oldPath])->saveQuietly();

        DB::listen(static function (QueryExecuted $query): void {
            $sql = strtolower($query->sql);

            if (str_contains($sql, 'update') && str_contains($sql, 'avatar')) {
                throw new RuntimeException('Simulated database failure.');
            }
        });

        try {
            app(UserService::class)->updateAvatar(
                $user,
                UploadedFile::fake()->image('new.jpg'),
            );

            $this->fail('The simulated database failure was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Simulated database failure.',
                $exception->getMessage(),
            );
        }

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'avatar' => $oldPath,
        ]);
        $this->disk->assertExists($oldPath);
        $this->assertSame(
            [$oldPath],
            $this->disk->allFiles("avatars/{$user->id}"),
        );
    }

    public function test_outer_transaction_rollback_removes_the_new_file(): void
    {
        $user = User::factory()->create();
        $oldPath = "avatars/{$user->id}/old.jpg";
        $this->disk->put($oldPath, 'old avatar');
        $user->forceFill(['avatar' => $oldPath])->saveQuietly();

        DB::beginTransaction();

        $updatedUser = app(UserService::class)->updateAvatar(
            $user,
            UploadedFile::fake()->image('new.jpg'),
        );
        $newPath = $updatedUser->avatar;

        $this->assertIsString($newPath);
        $this->disk->assertExists($newPath);
        $this->disk->assertExists($oldPath);

        DB::rollBack();

        $this->disk->assertMissing($newPath);
        $this->disk->assertExists($oldPath);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'avatar' => $oldPath,
        ]);
    }

    public function test_failed_upload_does_not_change_the_database(): void
    {
        $user = User::factory()->create();
        $avatar = Mockery::mock(UploadedFile::class);
        $avatar->shouldReceive('store')
            ->once()
            ->with("avatars/{$user->id}", 'public')
            ->andReturn(false);

        try {
            app(UserService::class)->updateAvatar($user, $avatar);

            $this->fail('The failed upload did not throw an exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Unable to store the avatar.',
                $exception->getMessage(),
            );
        }

        $this->assertNull($user->fresh()->avatar);
    }
}
