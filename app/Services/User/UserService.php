<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\Role as EnumsRole;
use App\Enums\UserStatus;
use App\Exceptions\RoleProtectionException;
use App\Exceptions\UserRoleException;
use App\Models\User;
use App\Notifications\User\UserCreatedNotification;
use App\Policies\PasswordPolicy;
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
        private readonly PasswordPolicy $passwordPolicy,
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

            // Only one user can have the super-admin role.
            $this->ensureSuperAdminIsAvailable($role);

            $temporaryPassword = Str::password(
                length: max(12, $this->passwordPolicy->minLength()),
                numbers: $this->passwordPolicy->requiresNumbers(),
                symbols: $this->passwordPolicy->requiresSymbols(),
            );

            $data['must_change_password'] = true;

            /** @var User $user */
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
    public function update(
        User $actor,
        User $user,
        array $data,
    ): User {
        return DB::transaction(function () use (
            $actor,
            $user,
            $data,
        ): User {
            $this->ensureCanManageUser(
                $actor,
                $user,
            );

            if (empty($data['password'])) {
                unset($data['password']);
            }

            $role = $data['role'] ?? null;
            unset($data['role']);

            if ($role !== null) {
                // Only one user can have the super-admin role.
                $this->ensureSuperAdminIsAvailable(
                    $role,
                    $user,
                );

                // An existing super-admin cannot be demoted.
                if (
                    $user->hasRole(EnumsRole::SUPER_ADMIN->value) &&
                    $role !== EnumsRole::SUPER_ADMIN->value
                ) {
                    throw new UserRoleException(
                        __('users.cannot_remove_super_admin_role'),
                    );
                }
            }

            $statusChangedToBlocked = isset($data['status'])
                && ! UserStatus::from($data['status'])->canLogin();

            $passwordChanged = isset($data['password']);

            $user->update($data);

            if ($statusChangedToBlocked || $passwordChanged) {
                $user->tokens()->delete();
            }

            if ($role !== null) {
                $user->syncRoles($role);
            }

            return $user->fresh();
        });
    }

    /**
     * Update user profile.
     */
    public function updateProfile(
        User $user,
        array $data,
    ): User {
        return DB::transaction(function () use (
            $user,
            $data,
        ): User {
            if (empty($data['password'])) {
                unset($data['password']);
            }

            unset($data['role']);

            $user->update($data);

            return $user->fresh();
        });
    }

    /**
     * Soft delete a user.
     */
    public function delete(
        User $actor,
        string $id,
    ): bool {
        $user = User::findOrFail($id);

        $this->ensureCanManageUser(
            $actor,
            $user,
        );

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
     * Permanently delete a soft deleted user.
     */
    public function forceDelete(
        User $actor,
        string $id,
    ): bool {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->deleted_at === null) {
            abort(404);
        }

        $this->ensureCanManageUser(
            $actor,
            $user,
        );

        return (bool) $user->forceDelete();
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

    /**
     * Determine whether an actor can delete the target user.
     */
    private function ensureCanDeleteUser(
        User $actor,
        User $target,
    ): void {
        if ($actor->is($target)) {
            abort(403, __('responses.user_cannot_delete_self'));
        }

        if (
            $target->hasRole(EnumsRole::SUPER_ADMIN->value)
            && ! $actor->hasRole(EnumsRole::SUPER_ADMIN->value)
        ) {
            abort(403, __('responses.super_admin_delete_forbidden'));
        }
    }

    /**
     * Determine whether an actor can change the target user's role.
     */
    /**
     * Determine whether an actor can manage the target user.
     */
    private function ensureCanManageUser(
        User $actor,
        User $target,
    ): void {
        if ($actor->is($target)) {
            abort(
                403,
                __('responses.user_cannot_manage_self'),
            );
        }

        if ($target->hasRole(EnumsRole::SUPER_ADMIN->value)) {
            abort(
                403,
                __('responses.super_admin_manage_forbidden'),
            );
        }
    }

    private function ensureSuperAdminIsAvailable(
        string $role,
        ?User $currentUser = null,
    ): void {
        if ($role !== EnumsRole::SUPER_ADMIN->value) {
            return;
        }

        $query = User::role(
            EnumsRole::SUPER_ADMIN->value,
            'sanctum',
        );

        if ($currentUser) {
            $query->whereKeyNot($currentUser->getKey());
        }

        if ($query->exists()) {
            throw new RoleProtectionException(
                __('users.super_admin_already_assigned'),
            );
        }
    }
}
