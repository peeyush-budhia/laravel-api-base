<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Enums\AuditEvent;
use App\Models\AuditLog;
use App\Support\AuditValueNormalizer;
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
        $eventValue = $this->resource instanceof AuditLog
            ? $this->resource->getAttribute('event')
            : null;
        $event = $eventValue instanceof AuditEvent
            ? $eventValue
            : (is_string($eventValue) ? AuditEvent::tryFrom($eventValue) : null);

        return [
            'id' => $this->id,

            'user_id' => $this->user_id,

            'event' => $this->event,

            'event_label' => $event?->label(),

            'event_tone' => $event?->tone(),

            'user' => $this->whenLoaded(
                'user',
                fn () => $this->user
                    ? AuditActorResource::make($this->user)->resolve($request)
                    : null,
            ),

            'auditable_type' => $this->auditable_type,
            'auditable_id' => $this->auditable_id,

            'old_values' => AuditValueNormalizer::normalize($this->old_values),
            'new_values' => AuditValueNormalizer::normalize($this->new_values),

            'url' => $this->url,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
