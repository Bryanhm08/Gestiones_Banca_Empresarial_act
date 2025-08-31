<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cuenta;
use App\Models\Cliente;
use App\Models\TipoCuenta;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class CuentaApiController extends Controller
{
    public function clientes()
    {
        return Cliente::select('id', 'nombre')->orderBy('nombre')->get();
    }

    public function tiposCuenta()
    {
        return TipoCuenta::select('id', 'nombre')->orderBy('nombre')->get();
    }
public function asesores()
{
    return User::where('asesor', 1)
        ->select('id', 'name as nombre')
        ->orderBy('name')
        ->get();
}


    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'cliente_id' => ['required', 'exists:clientes,id'],
            'tipo_cuenta_id' => ['required', 'exists:tipos_cuentas,id'],
            'asesor_id' => ['required', 'exists:users,id'],
            'fecha_apertura' => ['required', 'date', 'before_or_equal:today'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => $v->errors()->first()], 422);
        }

        $cuenta = Cuenta::create([
            'cliente_id' => $request->cliente_id,
            'tipo_cuenta_id' => $request->tipo_cuenta_id,
            'asesor_id' => $request->asesor_id,
            'fecha_apertura' => $request->fecha_apertura,
        ]);

        return response()->json(['id' => $cuenta->id], 201);
    }
    public function recent()
    {
        // Si tienes relaciones definidas (cliente, tipo, asesor), puedes usarlas:
        $rows = Cuenta::query()
            ->with(['cliente:id,nombre', 'tipo:id,nombre', 'asesor:id,name'])
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'cliente' => $c->cliente->nombre ?? '—',
                    'tipo_cuenta' => $c->tipo->nombre ?? '—',
                    'asesor' => $c->asesor->name ?? '—',
                    'fecha_apertura' => $c->fecha_apertura,
                    'created_at' => $c->created_at?->toDateString(),
                ];
            });

        return response()->json($rows);
    }

}
