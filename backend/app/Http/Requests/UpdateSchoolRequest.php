<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('school'));
    }

    public function rules(): array
    {
        $id = $this->route('school')->id;

        return [
            'name'                    => ['sometimes', 'string', 'max:255'],
            'npsn'                    => ['sometimes', 'string', 'size:8', Rule::unique('schools')->ignore($id)],
            'address'                 => ['sometimes', 'string', 'max:500'],
            'city'                    => ['sometimes', 'string', 'max:100'],
            'province'                => ['sometimes', 'string', 'max:100'],
            'phone'                   => ['sometimes', 'string', 'max:20'],
            'email'                   => ['sometimes', 'email', 'max:255', Rule::unique('schools')->ignore($id)],
            'level'                   => ['sometimes', 'string', 'in:sd,smp,sma,smk'],
            'student_count'           => ['sometimes', 'integer', 'min:1'],
            'geofence_radius_meters'  => ['sometimes', 'integer', 'min:10', 'max:1000'],
            'sppg_provider_id'        => ['sometimes', 'uuid', 'exists:sppg_providers,id'],
            'is_active'               => ['sometimes', 'boolean'],
            'latitude'                => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude'               => ['sometimes', 'numeric', 'between:-180,180'],
        ];
    }
}
