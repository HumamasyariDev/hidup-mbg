<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SppgProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'license_number'   => $this->license_number, // decrypted via cast
            'address'          => $this->address,
            'city'             => $this->city,
            'province'         => $this->province,
            'phone'            => $this->phone,
            'email'            => $this->email,
            'is_active'        => $this->is_active,
            'capacity_per_day' => $this->capacity_per_day,
            'schools_count'    => $this->whenCounted('schools'),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
