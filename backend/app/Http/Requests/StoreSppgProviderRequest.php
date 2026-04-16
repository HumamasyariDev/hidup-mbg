<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreSppgProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\SppgProvider::class);
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'license_number'   => ['required', 'string', 'max:100', 'unique:sppg_providers,license_number'],
            'address'          => ['required', 'string', 'max:500'],
            'city'             => ['required', 'string', 'max:100'],
            'province'         => ['required', 'string', 'max:100'],
            'phone'            => ['required', 'string', 'max:20'],
            'email'            => ['required', 'email', 'max:255', 'unique:sppg_providers,email'],
            'capacity_per_day' => ['required', 'integer', 'min:1', 'max:50000'],
            'latitude'         => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude'        => ['sometimes', 'numeric', 'between:-180,180'],
        ];
    }
}
