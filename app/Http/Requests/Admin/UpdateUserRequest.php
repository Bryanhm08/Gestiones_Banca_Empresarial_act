<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->admin === true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? null;

        return [
            'username' => [
                'required','string','max:50',
                Rule::unique('users','username')->whereNull('deleted_at')->ignore($userId),
            ],
            'name'     => ['required','string','max:120'],
            'email'    => [
                'required','email','max:120',
                Rule::unique('users','email')->whereNull('deleted_at')->ignore($userId),
            ],
            'puesto'   => ['nullable','string','max:120'],
            'area_id'  => ['nullable','integer','exists:areas,id'],
            'asesor'   => ['required','boolean'],
            'admin'    => ['required','boolean'],
            'estado'   => ['required','boolean'],
            // En edición la contraseña es OPCIONAL
            'password' => ['nullable','string','min:6'],
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
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
            'area_id.exists'    => 'El área seleccionada no es válida.',
        ];
    }
}
