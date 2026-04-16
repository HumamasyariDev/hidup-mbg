<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSppgProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('sppg_provider'));
    }

    public function rules(): array
    {
        $id = $this->route('sppg_provider')->id;

        return [
            'name'             => ['sometimes', 'string', 'max:255'],
            'license_number'   => ['sometimes', 'string', 'max:100', Rule::unique('sppg_providers')->ignore($id)],
            'address'          => ['sometimes', 'string', 'max:500'],
            'city'             => ['sometimes', 'string', 'max:100'],
            'province'         => ['sometimes', 'string', 'max:100'],
            'phone'            => ['sometimes', 'string', 'max:20'],
            'email'            => ['sometimes', 'email', 'max:255', Rule::unique('sppg_providers')->ignore($id)],
            'is_active'        => ['sometimes', 'boolean'],
            'capacity_per_day' => ['sometimes', 'integer', 'min:1', 'max:50000'],
            'latitude'         => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude'        => ['sometimes', 'numeric', 'between:-180,180'],
        ];
    }
}
