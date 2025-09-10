<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->admin === true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:80', 'unique:users,username'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'puesto' => ['nullable', 'string', 'max:120'],
            'asesor' => ['boolean'],
            'admin' => ['boolean'],
            'estado' => ['boolean'],
        ];
    }
}
