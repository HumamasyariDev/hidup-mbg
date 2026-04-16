<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SchoolReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'daily_dispatch_id'    => $this->daily_dispatch_id,
            'school_id'            => $this->school_id,
            'receipt_date'         => $this->receipt_date?->toDateString(),
            'quantity_received'    => $this->quantity_received,
            'quantity_distributed' => $this->quantity_distributed,
            'quantity_damaged'     => $this->quantity_damaged,
            'condition'            => $this->condition,
            'notes'                => $this->notes,
            'photo_proof_path'     => $this->photo_proof_path,
            'reported_by_admin_id' => $this->reported_by_admin_id,
            'school'               => new SchoolResource($this->whenLoaded('school')),
            'daily_dispatch'       => new DailyDispatchResource($this->whenLoaded('dailyDispatch')),
            'created_at'           => $this->created_at?->toIso8601String(),
        ];
    }
}
