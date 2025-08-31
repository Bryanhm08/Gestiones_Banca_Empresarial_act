<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_cliente' => ['required', 'string', 'max:255'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'nit' => ['required', 'string', 'max:30', 'unique:clientes,nit'],
            'fecha_nacimiento' => ['required', 'date'],
            'telefono' => ['required', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:255'],
            'asesor_id' => ['required', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_cliente.required' => 'El nombre del cliente es obligatorio.',
            'categoria_id.required' => 'Seleccioná una categoría.',
            'nit.required' => 'El NIT es obligatorio.',
            'nit.unique' => 'Ese NIT ya está registrado.',
            'fecha_nacimiento.required' => 'La fecha es obligatoria.',
            'telefono.required' => 'Ingresá un teléfono.',
            'email.email' => 'Ingresá un correo válido.',
            'asesor_id.required' => 'Seleccioná el asesor asignado.',
        ];
    }
}
