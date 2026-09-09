<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 *
 * @property-read User $resource
 */
final class UserSummaryResource extends JsonResource
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
        $status = $user->getAttribute('status');
        $formatDate = static fn (mixed $date): ?string => $date instanceof \DateTimeInterface
            ? $date->format(DATE_ATOM)
            : null;

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => trim("{$user->first_name} {$user->last_name}"),
            'email' => $user->email,
            'avatar' => $user->avatar ? asset('storage/'.ltrim($user->avatar, '/')) : null,
            'status' => $status instanceof UserStatus ? $status->value : null,
            'status_label' => $status instanceof UserStatus ? $status->label() : null,
            'status_tone' => $status instanceof UserStatus ? $status->tone() : null,
            'email_verified_at' => $formatDate($user->getAttribute('email_verified_at')),
            'last_login_at' => $formatDate($user->getAttribute('last_login_at')),
            'created_at' => $formatDate($user->getAttribute('created_at')),
            'updated_at' => $formatDate($user->getAttribute('updated_at')),
            'deleted_at' => $formatDate($user->getAttribute('deleted_at')),
        ];
    }
}
