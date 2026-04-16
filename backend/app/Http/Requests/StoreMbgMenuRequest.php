<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMbgMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\MbgMenu::class);
    }

    public function rules(): array
    {
        return [
            'sppg_provider_id' => ['required', 'uuid', 'exists:sppg_providers,id'],
            'menu_name'        => ['required', 'string', 'max:255'],
            'description'      => ['sometimes', 'string', 'max:1000'],
            'serve_date'       => ['required', 'date', 'after_or_equal:today'],
            'meal_type'        => ['required', 'string', 'in:breakfast,lunch'],
            'nutrition_data'   => ['sometimes', 'array'],
            'calories'         => ['required', 'numeric', 'min:0', 'max:5000'],
            'protein_g'        => ['required', 'numeric', 'min:0', 'max:500'],
            'carbs_g'          => ['required', 'numeric', 'min:0', 'max:500'],
            'fat_g'            => ['required', 'numeric', 'min:0', 'max:500'],
            'photo'            => ['sometimes', 'image', 'max:5120'], // 5MB
        ];
    }
}
