<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateMbgMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('mbg_menu'));
    }

    public function rules(): array
    {
        return [
            'menu_name'        => ['sometimes', 'string', 'max:255'],
            'description'      => ['sometimes', 'string', 'max:1000'],
            'serve_date'       => ['sometimes', 'date'],
            'meal_type'        => ['sometimes', 'string', 'in:breakfast,lunch'],
            'nutrition_data'   => ['sometimes', 'array'],
            'calories'         => ['sometimes', 'numeric', 'min:0', 'max:5000'],
            'protein_g'        => ['sometimes', 'numeric', 'min:0', 'max:500'],
            'carbs_g'          => ['sometimes', 'numeric', 'min:0', 'max:500'],
            'fat_g'            => ['sometimes', 'numeric', 'min:0', 'max:500'],
            'photo'            => ['sometimes', 'image', 'max:5120'],
        ];
    }
}
