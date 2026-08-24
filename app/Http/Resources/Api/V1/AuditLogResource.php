<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AuditLogResource extends JsonResource
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

            'user_id' => $this->user_id,

            'event' => $this->event,

            'user' => $this->whenLoaded(
                'user',
                fn () => $this->user
                    ? [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                    ]
                    : null,
            ),

            'auditable_type' => $this->auditable_type,
            'auditable_id' => $this->auditable_id,

            'old_values' => $this->old_values,
            'new_values' => $this->new_values,

            'url' => $this->url,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
