<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('admin'));
    }

    public function rules(): array
    {
        $id = $this->route('admin')->id;

        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => ['sometimes', 'email', 'max:255', Rule::unique('admins')->ignore($id)],
            'password'  => ['sometimes', 'string', 'min:12', 'max:128'],
            'is_active' => ['sometimes', 'boolean'],
            'role'      => ['sometimes', 'string', Rule::in(['super_admin', 'admin_sppg', 'admin_school'])],
        ];
    }
}
