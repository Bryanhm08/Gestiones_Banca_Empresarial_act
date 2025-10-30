<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->admin === true;
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required','string','max:50',
                Rule::unique('users','username')->whereNull('deleted_at'),
            ],
            'name'     => ['required','string','max:120'],
            'email'    => [
                'required','email','max:120',
                Rule::unique('users','email')->whereNull('deleted_at'),
            ],
            'puesto'   => ['nullable','string','max:120'],
            'area_id'  => ['nullable','integer','exists:areas,id'],
            'asesor'   => ['required','boolean'],
            'admin'    => ['required','boolean'],
            'estado'   => ['required','boolean'],
            'password' => ['required','string','min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Ingresá el usuario.',
            'username.unique'   => 'Ese usuario ya existe.',
            'name.required'     => 'Ingresá el nombre.',
            'email.required'    => 'Ingresá el email.',
            'email.unique'      => 'Ese email ya existe.',
            'password.required' => 'Ingresá la contraseña.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
            'area_id.exists'    => 'El área seleccionada no es válida.',
        ];
    }
}
