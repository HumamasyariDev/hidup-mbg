<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MbgMenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'sppg_provider_id' => $this->sppg_provider_id,
            'sppg_provider'    => new SppgProviderResource($this->whenLoaded('sppgProvider')),
            'menu_name'        => $this->menu_name,
            'description'      => $this->description,
            'serve_date'       => $this->serve_date?->toDateString(),
            'meal_type'        => $this->meal_type,
            'nutrition_data'   => $this->nutrition_data,
            'photo_path'       => $this->photo_path,
            'calories'         => (float) $this->calories,
            'protein_g'        => (float) $this->protein_g,
            'carbs_g'          => (float) $this->carbs_g,
            'fat_g'            => (float) $this->fat_g,
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
