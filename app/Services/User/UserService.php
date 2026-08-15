<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\User\UserCreatedNotification;
use App\Query\QueryExecutor;
use App\Query\QueryParameters;
use App\Query\UserQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        private readonly UserQuery $userQuery,
        private readonly QueryExecutor $queryExecutor,
    ) {}

    /**
     * Get paginated users.
     */
    public function index(
        QueryParameters $parameters
    ): LengthAwarePaginator {
        return $this->queryExecutor->paginate(
            $this->userQuery,
            $parameters,
        );
    }

    /**
     * Get a single user.
     */
    public function show(User $user): User
    {
        return $user;
    }

    /**
     * Create a new user.
     */
    public function store(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $data['status'] ??= UserStatus::ACTIVE;

            $role = $data['role'];
            unset($data['role']);

            $temporaryPassword = Str::password(12);

            $data['must_change_password'] = true;

            $user = User::create([
                ...$data,
                'password' => Hash::make($temporaryPassword),
            ]);

            $user->assignRole($role);

            // Send temporary credentials after successful creation.
            $user->notify(
                new UserCreatedNotification($temporaryPassword),
            );

            return $user->fresh();
        });
    }

    /**
     * Update an existing user.
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            if (empty($data['password'])) {
                unset($data['password']);
            }

            $role = $data['role'] ?? null;
            unset($data['role']);

            $user->update($data);

            if ($role !== null) {
                $user->syncRoles($role);
            }

            return $user->fresh();
        });
    }

    /**
     * Soft delete a user.
     */
    public function destroy(User $user): bool
    {
        return (bool) $user->delete();
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore(string $id): User
    {
        $user = User::withTrashed()->findOrFail($id);

        $user->restore();

        return $user->fresh();
    }

    /**
     * Permanently delete a user.
     */
    public function forceDelete(string $id): bool
    {
        $user = User::withTrashed()->findOrFail($id);

        return (bool) $user->forceDelete();
    }

    /**
     * Change user status.
     */
    public function changeStatus(User $user, UserStatus $status): User
    {
        $user->update([
            'status' => $status,
        ]);

        return $user->fresh();
    }

    /**
     * Update the authenticated user's avatar.
     */
    public function updateAvatar(
        User $user,
        UploadedFile $avatar,
    ): User {
        return DB::transaction(function () use ($user, $avatar): User {
            $oldAvatar = $user->avatar;

            $path = $avatar->store('avatars', 'public');

            $user->update([
                'avatar' => $path,
            ]);

            if ($oldAvatar) {
                Storage::disk('public')->delete($oldAvatar);
            }

            return $user->fresh();
        });
    }
}
