<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->admin === true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id ?? null;

        return [
            'username' => ['required', 'string', 'max:80', Rule::unique('users', 'username')->ignore($userId)],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'puesto' => ['nullable', 'string', 'max:120'],
            'asesor' => ['boolean'],
            'admin' => ['boolean'],
            'estado' => ['boolean'],
        ];
    }
}
