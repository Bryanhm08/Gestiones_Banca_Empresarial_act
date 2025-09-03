<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Credito;
use App\Models\Amortizacion;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = (bool) $user->admin;
        $isAsesor = (bool) $user->asesor;

        $today = Carbon::today();
        $next30 = Carbon::today()->addDays(30);

        if ($isAdmin) {
            $stats = [
                'clientes' => Cliente::count(),
                'cuentas' => Cuenta::count(),
                'creditos' => Credito::count(),
                'montoTotal' => (float) Credito::sum('monto'),
            ];
            $upcomingVenc = Credito::with('cliente:id,nombre_cliente')
                ->whereBetween('fecha_vencimiento', [$today, $next30])
                ->orderBy('fecha_vencimiento')
                ->limit(8)
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'cliente' => $c->cliente?->nombre_cliente,
                    'fecha_vencimiento' => optional($c->fecha_vencimiento)->format('Y-m-d'),
                    'monto' => (float) $c->monto,
                ]);

            // Amortizaciones pendientes (top 8)
            $pendingAmort = Amortizacion::select('amortizaciones.*')
                ->join('creditos', 'creditos.id', '=', 'amortizaciones.credito_id')
                ->where('amortizaciones.status', 'Pendiente')
                ->whereBetween('amortizaciones.fecha_pago', [$today, $next30])
                ->orderBy('amortizaciones.fecha_pago')
                ->limit(8)
                ->get()
                ->map(fn($a) => [
                    'id' => $a->id,
                    'credito_id' => $a->credito_id,
                    'fecha_pago' => optional($a->fecha_pago)->format('Y-m-d'),
                    'status' => $a->status,
                ]);
        } else {
            $stats = [
                'clientes' => Cliente::where('asesor_id', $user->id)->count(),
                'cuentas' => Cuenta::where('asesor_id', $user->id)->count(),
                'creditos' => Credito::where('asesor_id', $user->id)->count(),
                'montoTotal' => (float) Credito::where('asesor_id', $user->id)->sum('monto'),
            ];

            $upcomingVenc = Credito::with('cliente:id,nombre_cliente')
                ->where('asesor_id', $user->id)
                ->whereBetween('fecha_vencimiento', [$today, $next30])
                ->orderBy('fecha_vencimiento')
                ->limit(8)
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'cliente' => $c->cliente?->nombre_cliente,
                    'fecha_vencimiento' => optional($c->fecha_vencimiento)->format('Y-m-d'),
                    'monto' => (float) $c->monto,
                ]);

            $pendingAmort = Amortizacion::select('amortizaciones.*')
                ->join('creditos', 'creditos.id', '=', 'amortizaciones.credito_id')
                ->where('creditos.asesor_id', $user->id)
                ->where('amortizaciones.status', 'Pendiente')
                ->whereBetween('amortizaciones.fecha_pago', [$today, $next30])
                ->orderBy('amortizaciones.fecha_pago')
                ->limit(8)
                ->get()
                ->map(fn($a) => [
                    'id' => $a->id,
                    'credito_id' => $a->credito_id,
                    'fecha_pago' => optional($a->fecha_pago)->format('Y-m-d'),
                    'status' => $a->status,
                ]);
        }
        $recentClientes = Cliente::with('asesor:id,name')
            ->latest()->limit(6)->get(['id', 'nombre_cliente', 'nit', 'telefono', 'email', 'asesor_id', 'created_at'])
            ->map(fn($c) => [
                'id' => $c->id,
                'nombre_cliente' => $c->nombre_cliente,
                'nit' => $c->nit,
                'telefono' => $c->telefono,
                'email' => $c->email,
                'asesor' => $c->asesor?->name,
                'created_at' => optional($c->created_at)->format('Y-m-d'),
            ]);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'upcomingVenc' => $upcomingVenc,
            'pendingAmort' => $pendingAmort,
            'recentClientes' => $recentClientes,
            'isAdmin' => $isAdmin,
            'isAsesor' => $isAsesor,
        ]);
    }
}
