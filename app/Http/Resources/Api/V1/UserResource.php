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
        return [
            'id' => $this->id,

            'first_name' => $this->first_name,

            'last_name' => $this->last_name,

            'full_name' => trim("{$this->first_name} {$this->last_name}"),

            'email' => $this->email,

            'avatar' => $this->avatar ? asset('storage/'.ltrim($this->avatar, '/')) : null,

            'role' => $this->getRoleNames()->first(),

            'status' => $this->status?->value,

            'email_verified_at' => $this->email_verified_at?->toIso8601String(),

            'last_login_at' => $this->last_login_at?->toIso8601String(),

            'created_at' => $this->created_at?->toIso8601String(),

            'updated_at' => $this->updated_at?->toIso8601String(),

            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
