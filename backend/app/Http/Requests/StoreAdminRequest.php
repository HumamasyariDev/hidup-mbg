<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Admin::class);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password'    => ['required', 'string', 'min:12', 'max:128'],
            'role'        => ['required', 'string', Rule::in(['super_admin', 'admin_sppg', 'admin_school'])],
            'entity_id'   => ['required_unless:role,super_admin', 'nullable', 'uuid'],
            'entity_type' => ['required_unless:role,super_admin', 'nullable', 'string', Rule::in(['sppg_providers', 'schools'])],
        ];
    }
}
