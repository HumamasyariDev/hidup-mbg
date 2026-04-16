<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\School::class);
    }

    public function rules(): array
    {
        return [
            'name'                    => ['required', 'string', 'max:255'],
            'npsn'                    => ['required', 'string', 'size:8', 'unique:schools,npsn'],
            'address'                 => ['required', 'string', 'max:500'],
            'city'                    => ['required', 'string', 'max:100'],
            'province'                => ['required', 'string', 'max:100'],
            'phone'                   => ['required', 'string', 'max:20'],
            'email'                   => ['required', 'email', 'max:255', 'unique:schools,email'],
            'level'                   => ['required', 'string', 'in:sd,smp,sma,smk'],
            'student_count'           => ['required', 'integer', 'min:1'],
            'geofence_radius_meters'  => ['sometimes', 'integer', 'min:10', 'max:1000'],
            'sppg_provider_id'        => ['required', 'uuid', 'exists:sppg_providers,id'],
            'latitude'                => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude'               => ['sometimes', 'numeric', 'between:-180,180'],
        ];
    }
}
