<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,

            'email' => $this->email,
            'phone' => $this->phone,

            'address' => $this->address,
            'job_title' => $this->job_title,
            'avatar' => $this->avatar,

            'email_verified_at' => $this->email_verified_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}