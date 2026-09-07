<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\AuditEvent;
use App\Enums\Role as EnumsRole;
use App\Enums\UserStatus;
use App\Exceptions\RoleProtectionException;
use App\Exceptions\UserRoleException;
use App\Models\Role;
use App\Models\User;
use App\Notifications\User\UserCreatedNotification;
use App\Query\QueryExecutor;
use App\Query\QueryParameters;
use App\Query\UserQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

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
        return $user->loadMissing([
            'roles.permissions',
            'permissions',
        ]);
    }

    /**
     * Create a new user.
     */
    public function store(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $data['status'] ??= UserStatus::ACTIVE;

            $role = $data['role'];
            unset($data['role'], $data['avatar']);

            // Only one user can have the super-admin role.
            $this->ensureSuperAdminIsAvailable($role);

            /** @var User $user */
            $user = User::create([
                ...$data,
                'password' => Hash::make(Str::random(64)),
            ]);

            $user->assignRole($role);
            $user->auditRelationshipChange(
                AuditEvent::RolesSynced,
                'roles',
                [],
                $this->roleNames($user),
            );

            $activationToken = Password::createToken($user);

            $user->notify(
                new UserCreatedNotification($activationToken),
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
            unset($data['role'], $data['avatar']);

            $oldRoles = $role !== null
                ? $this->roleNames($user)
                : [];

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

            $this->updateAttributes($user, $data);

            if ($statusChangedToBlocked || $passwordChanged) {
                $user->tokens()->delete();
            }

            if ($role !== null) {
                $user->syncRoles($role);
                $user->auditRelationshipChange(
                    AuditEvent::RolesSynced,
                    'roles',
                    $oldRoles,
                    $this->roleNames($user),
                );
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

            unset($data['role'], $data['avatar']);

            $this->updateAttributes($user, $data);

            return $user->fresh();
        });
    }

    /**
     * Update a user and clear verification when the email address changes.
     */
    private function updateAttributes(User $user, array $data): void
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }

    /**
     * Get the user's assigned role names in deterministic order.
     *
     * @return array<int, string>
     */
    private function roleNames(User $user): array
    {
        return $user->roles()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();
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

        return DB::transaction(function () use ($user): bool {
            $deleted = (bool) $user->delete();

            if ($deleted) {
                $user->tokens()->delete();
            }

            return $deleted;
        });
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
        $oldAvatar = $user->avatar;
        $path = $avatar->store(
            $this->avatarDirectory($user),
            'public',
        );

        if (
            ! is_string($path)
            || ! $this->isOwnedAvatarPath($user, $path)
        ) {
            if (is_string($path)) {
                $this->deleteAvatarFile($user, $path);
            }

            throw new RuntimeException('Unable to store the avatar.');
        }

        /** @var Connection $connection */
        $connection = DB::connection($user->getConnectionName());

        try {
            return $connection->transaction(function () use (
                $connection,
                $user,
                $oldAvatar,
                $path,
            ): User {
                $user->update([
                    'avatar' => $path,
                ]);

                /** @var User $updatedUser */
                $updatedUser = $user->fresh();

                $connection->afterRollBack(
                    fn (): bool => $this->deleteAvatarFile($user, $path),
                );

                if (
                    is_string($oldAvatar)
                    && $this->isOwnedAvatarPath($user, $oldAvatar)
                ) {
                    $connection->afterCommit(
                        fn (): bool => $this->deleteAvatarFile(
                            $user,
                            $oldAvatar,
                        ),
                    );
                }

                return $updatedUser;
            });
        } catch (Throwable $exception) {
            $this->deleteAvatarFile($user, $path);

            throw $exception;
        }
    }

    private function avatarDirectory(User $user): string
    {
        return 'avatars/'.$user->getKey();
    }

    private function isOwnedAvatarPath(User $user, string $path): bool
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $segments = explode('/', $normalizedPath);

        return $normalizedPath === ltrim($normalizedPath, '/')
            && ! in_array('..', $segments, true)
            && ! in_array('.', $segments, true)
            && str_starts_with(
                $normalizedPath,
                $this->avatarDirectory($user).'/',
            );
    }

    private function deleteAvatarFile(User $user, string $path): bool
    {
        if (! $this->isOwnedAvatarPath($user, $path)) {
            Log::warning('Refused to delete an unowned avatar path.', [
                'user_id' => $user->getKey(),
                'path' => $path,
            ]);

            return false;
        }

        try {
            $deleted = Storage::disk('public')->delete($path);
        } catch (Throwable $exception) {
            Log::warning('Unable to delete an avatar file.', [
                'path' => $path,
                'exception' => $exception::class,
            ]);

            return false;
        }

        if (! $deleted) {
            Log::warning('Unable to delete an avatar file.', [
                'path' => $path,
            ]);
        }

        return $deleted;
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

        Role::query()
            ->where('name', EnumsRole::SUPER_ADMIN->value)
            ->where('guard_name', 'sanctum')
            ->lockForUpdate()
            ->firstOrFail();

        $query = User::role(
            EnumsRole::SUPER_ADMIN->value,
            'sanctum',
        );

        $query->withTrashed();

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
