<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id'      => ['required', 'exists:clientes,id'],
            'tipo_credito_id' => ['required', 'exists:tipos_credito,id'],
            'garantia_id'     => ['required', 'exists:garantias,id'],
            'monto'           => ['required', 'numeric', 'min:0.01'],
            'plazo'           => ['required', 'integer', 'min:1', 'max:360'],
            // fecha_concesion ya no se usa en el pipeline
            'asesor_id'       => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required'      => 'Seleccioná un cliente.',
            'tipo_credito_id.required' => 'Seleccioná el tipo de crédito.',
            'garantia_id.required'     => 'Seleccioná la garantía.',
            'monto.required'           => 'Ingresá el monto.',
            'plazo.required'           => 'Ingresá el plazo en meses.',
        ];
    }
}
