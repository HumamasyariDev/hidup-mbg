<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DailyDispatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'sppg_provider_id'     => $this->sppg_provider_id,
            'school_id'            => $this->school_id,
            'mbg_menu_id'          => $this->mbg_menu_id,
            'dispatch_date'        => $this->dispatch_date?->toDateString(),
            'quantity_sent'        => $this->quantity_sent,
            'vehicle_plate'        => $this->vehicle_plate,
            'driver_name'          => $this->driver_name,
            'dispatched_at'        => $this->dispatched_at?->toIso8601String(),
            'photo_proof_path'     => $this->photo_proof_path,
            'reported_by_admin_id' => $this->reported_by_admin_id,
            'sppg_provider'        => new SppgProviderResource($this->whenLoaded('sppgProvider')),
            'school'               => new SchoolResource($this->whenLoaded('school')),
            'menu'                 => new MbgMenuResource($this->whenLoaded('menu')),
            'receipt'              => new SchoolReceiptResource($this->whenLoaded('receipt')),
            'created_at'           => $this->created_at?->toIso8601String(),
        ];
    }
}
