<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Credito;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MisAsignacionesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $asesorId = $user->id;
        $clientesQuery = Cliente::query()
            ->with([
                'categoria:id,nombre',
                'asesor:id,name,email',
            ])
            ->where('asesor_id', $asesorId)
            ->orderByDesc('created_at');

        $cuentasQuery = Cuenta::query()
            ->with([
                'cliente:id,nombre_cliente,nit,telefono,email',
                'tipo:id,nombre',
                'asesor:id,name,email',
            ])
            ->where('asesor_id', $asesorId)
            ->orderByDesc('created_at');

        $creditosQuery = Credito::query()
            ->with([
                'cliente:id,nombre_cliente',
                'tipoCredito:id,nombre',
                'garantia:id,nombre',
                'asesor:id,name,email',
            ])
            ->where('asesor_id', $asesorId)
            ->orderByDesc('created_at');
        $stats = [
            'clientes' => (clone $clientesQuery)->count(),
            'cuentas' => (clone $cuentasQuery)->count(),
            'creditos' => (clone $creditosQuery)->count(),
            'montoTotal' => (float) (clone $creditosQuery)->sum('monto'),
        ];
        $clientes = $clientesQuery
            ->take(10)
            ->get([
                'id',
                'nombre_cliente',
                'categoria_id',
                'nit',
                'telefono',
                'email',
                'fecha_nacimiento',
                'asesor_id',
                'created_at',
            ])
            ->map(fn($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre_cliente,
                'nit' => $c->nit,
                'telefono' => $c->telefono,
                'email' => $c->email,
                'fecha_nacimiento' => optional($c->fecha_nacimiento)->format('Y-m-d'),
                'creado' => optional($c->created_at)->format('Y-m-d'),

                // para el modal: objetos anidados
                'categoria' => $c->categoria ? [
                    'id' => $c->categoria_id,
                    'nombre' => $c->categoria->nombre,
                ] : null,
                'asesor' => $c->asesor ? [
                    'id' => $c->asesor->id,
                    'name' => $c->asesor->name,
                    'email' => $c->asesor->email,
                ] : null,
            ]);
        $cuentas = $cuentasQuery
            ->take(10)
            ->get([
                'id',
                'cliente_id',
                'tipo_cuenta_id',
                'asesor_id',
                'fecha_apertura',
                'created_at',
            ])
            ->map(fn($c) => [
                'id' => $c->id,
                'fecha_apertura' => optional($c->fecha_apertura)->format('Y-m-d'),

                // objetos anidados para el modal
                'cliente' => $c->cliente ? [
                    'id' => $c->cliente_id,
                    'nombre_cliente' => $c->cliente->nombre_cliente,
                    'nit' => $c->cliente->nit,
                    'telefono' => $c->cliente->telefono,
                    'email' => $c->cliente->email,
                ] : null,
                'tipo' => $c->tipo ? [
                    'id' => $c->tipo_cuenta_id,
                    'nombre' => $c->tipo->nombre,
                ] : null,
                'asesor' => $c->asesor ? [
                    'id' => $c->asesor->id,
                    'name' => $c->asesor->name,
                    'email' => $c->asesor->email,
                ] : null,
            ]);
        $creditos = $creditosQuery
            ->take(10)
            ->get([
                'id',
                'cliente_id',
                'tipo_credito_id',
                'garantia_id',
                'asesor_id',
                'monto',
                'plazo',
                'fecha_concesion',
                'fecha_vencimiento',
                'created_at',
            ])
            ->map(fn($c) => [
                'id' => $c->id,
                'cliente' => $c->cliente?->nombre_cliente,
                'tipo' => $c->tipoCredito?->nombre,
                'garantia' => $c->garantia?->nombre,
                'monto' => (float) $c->monto,
                'plazo' => (int) $c->plazo,
                'fecha_concesion' => optional($c->fecha_concesion)->format('Y-m-d'),
                'fecha_vencimiento' => optional($c->fecha_vencimiento)->format('Y-m-d'),
                'asesor' => $c->asesor ? [
                    'id' => $c->asesor->id,
                    'name' => $c->asesor->name,
                    'email' => $c->asesor->email,
                ] : null,
            ]);

        $canGoClientesModule = $user->admin || !$user->asesor;

        return Inertia::render('MisAsignaciones/Index', [
            'stats' => $stats,
            'clientes' => $clientes,
            'cuentas' => $cuentas,
            'creditos' => $creditos,
            'canGoClientesModule' => $canGoClientesModule,
        ]);
    }
}
