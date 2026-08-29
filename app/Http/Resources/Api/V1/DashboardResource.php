<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'summary' => $this->resource['summary'],

            'users' => [
                'by_status' => $this->resource['users']['by_status'],
                'recent' => UserResource::collection(
                    $this->resource['users']['recent'],
                ),
                'recently_active' => UserResource::collection(
                    $this->resource['users']['recently_active'],
                ),
            ],

            'audit' => [
                'by_event' => $this->resource['audit']['by_event'],
                'recent' => $this->resource['audit']['recent'],
            ],
        ];
    }
}
