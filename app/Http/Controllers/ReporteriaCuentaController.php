<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Cuenta;
use App\Models\User;
use App\Http\Controllers\ReportExportController as Exporter;

class ReporteriaCuentaController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $asesor = $request->input('asesor');
        $tipo = $request->input('tipo');

        $defFrom = Carbon::today()->subDays(90)->format('Y-m-d');
        $defTo = Carbon::today()->format('Y-m-d');
        $from ??= $defFrom;
        $to ??= $defTo;

        $query = Cuenta::query()
            ->leftJoin('clientes', 'clientes.id', '=', 'cuentas.cliente_id')
            ->leftJoin('users as asesores', 'asesores.id', '=', 'cuentas.asesor_id')
            ->leftJoin('tipos_cuenta as tc', 'tc.id', '=', 'cuentas.tipo_cuenta_id')
            ->when($from, fn($q) => $q->whereDate('cuentas.fecha_apertura', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('cuentas.fecha_apertura', '<=', $to))
            ->when($asesor, function ($q) use ($asesor) {
                $ids = is_array($asesor) ? $asesor : [$asesor];
                $q->whereIn('cuentas.asesor_id', array_filter($ids));
            })
            ->when($tipo, function ($q) use ($tipo) {
                $ids = is_array($tipo) ? $tipo : [$tipo];
                $q->whereIn('cuentas.tipo_cuenta_id', array_filter($ids));
            })
            ->select([
                'cuentas.id',
                'clientes.nombre_cliente as cliente',
                'tc.nombre as tipo',
                'asesores.name as asesor',
                'cuentas.fecha_apertura',
            ])
            ->orderByDesc('cuentas.id');

        $rows = $query->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'cliente' => $c->cliente,
                'tipo' => $c->tipo,
                'asesor' => $c->asesor,
                'fecha_apertura' => optional($c->fecha_apertura)->format('Y-m-d'),
            ];
        });

        $total = $rows->count();
        $asesoresList = User::where(function ($q) {
            $q->where('asesor', 1)->orWhere('admin', 1);
        })->select('id', 'name')->orderBy('name')->get();
        $tiposCuenta = DB::table('tipos_cuenta')->select('id', 'nombre')->orderBy('nombre')->get();

        if ($request->boolean('export')) {
            $headings = ['N°', 'Cliente', 'Tipo de cuenta', 'Asesor', 'Fecha de apertura'];
            $rowsForXlsx = $rows->map(fn($r) => [
                $r['id'],
                $r['cliente'],
                $r['tipo'],
                $r['asesor'],
                $r['fecha_apertura'],
            ])->toArray();

            $formats = ['E' => 'yyyy-mm-dd'];

            return app(Exporter::class)->downloadExcel(
                $headings,
                $rowsForXlsx,
                'reporteria_cuentas',
                'Reportería de cuentas — CHN',
                $formats
            );
        }

        return Inertia::render('Reportes/Cuentas/Index', [
            'filters' => ['from' => $from, 'to' => $to, 'asesor' => $asesor, 'tipo' => $tipo],
            'rows' => $rows,
            'kpis' => ['total' => $total],
            'catalogs' => ['asesores' => $asesoresList, 'tipos' => $tiposCuenta],
        ]);
    }
}
