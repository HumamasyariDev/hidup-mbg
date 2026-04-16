<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SchoolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'name'                   => $this->name,
            'npsn'                   => $this->npsn,
            'address'                => $this->address,
            'city'                   => $this->city,
            'province'               => $this->province,
            'phone'                  => $this->phone,
            'email'                  => $this->email,
            'level'                  => $this->level,
            'student_count'          => $this->student_count,
            'geofence_radius_meters' => $this->geofence_radius_meters,
            'is_active'              => $this->is_active,
            'sppg_provider_id'       => $this->sppg_provider_id,
            'sppg_provider'          => new SppgProviderResource($this->whenLoaded('sppgProvider')),
            'created_at'             => $this->created_at?->toIso8601String(),
            'updated_at'             => $this->updated_at?->toIso8601String(),
        ];
    }
}
