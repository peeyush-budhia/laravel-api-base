<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Get paginated users.
     */
    public function index(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->latest()
            ->paginate($perPage);
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

            /** @var User $user */
            $user = User::create($data);

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

            $user->update($data);

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
     * Search users.
     */
    public function search(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->where(function (Builder $query) use ($search): void {
                $query
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);
    }
}
