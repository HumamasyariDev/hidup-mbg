<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserFeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'school_id'         => $this->school_id,
            'mbg_menu_id'       => $this->mbg_menu_id,
            'feedback_date'     => $this->feedback_date?->toDateString(),
            'zkp_identity_hash' => $this->zkp_identity_hash,
            'rating'            => $this->rating,
            'taste_rating'      => $this->taste_rating,
            'portion_rating'    => $this->portion_rating,
            'comment'           => $this->comment,
            'photo_path'        => $this->photo_path,
            'school'            => new SchoolResource($this->whenLoaded('school')),
            'menu'              => new MbgMenuResource($this->whenLoaded('menu')),
            'created_at'        => $this->created_at?->toIso8601String(),
        ];
    }
}
