<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        $user->loadMissing([
            'roles.permissions',
            'permissions',
        ]);

        return [
            'id' => $user->id,

            'first_name' => $user->first_name,

            'last_name' => $user->last_name,

            'full_name' => trim("{$user->first_name} {$user->last_name}"),

            'email' => $user->email,

            'avatar' => $user->avatar ? asset('storage/'.ltrim($user->avatar, '/')) : null,

            'role' => $user->getRoleNames()->first(),

            'permissions' => $user->getAllPermissions()
                ->pluck('name')
                ->unique()
                ->sort()
                ->values()
                ->all(),

            'status' => $user->status?->value,

            'email_verified_at' => $user->email_verified_at?->toIso8601String(),

            'last_login_at' => $user->last_login_at?->toIso8601String(),

            'created_at' => $user->created_at?->toIso8601String(),

            'updated_at' => $user->updated_at?->toIso8601String(),

            'deleted_at' => $user->deleted_at?->toIso8601String(),
        ];
    }
}
