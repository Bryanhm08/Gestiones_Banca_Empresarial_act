<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Credito;
use App\Models\User;
use App\Http\Controllers\ReportExportController as Exporter;


class ReporteCreditoController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $search = $request->input('q');
        $asesor = $request->input('asesor');
        $tipos = (array) $request->input('tipos');
        $estados = (array) $request->input('estados');

        $defFrom = Carbon::today()->subDays(90)->format('Y-m-d');
        $defTo = Carbon::today()->format('Y-m-d');
        $from ??= $defFrom;
        $to ??= $defTo;

        $lastPerCredito = DB::table('estado_credito as ec1')
            ->select('ec1.credito_id', DB::raw('MAX(ec1.id) as last_id'))
            ->groupBy('ec1.credito_id');

        $lastEstados = DB::table('estado_credito as ec')
            ->joinSub($lastPerCredito, 't', function ($join) {
                $join->on('ec.id', '=', 't.last_id');
            })
            ->leftJoin('estados_credito as cat', 'cat.id', '=', 'ec.estado_id')
            ->select('ec.credito_id', 'ec.estado_id', 'cat.nombre as estado_nombre');

        $query = Credito::query()
            ->leftJoinSub($lastEstados, 'le', function ($join) {
                $join->on('le.credito_id', '=', 'creditos.id');
            })
            ->leftJoin('clientes', 'clientes.id', '=', 'creditos.cliente_id')
            ->leftJoin('users as asesores', 'asesores.id', '=', 'creditos.asesor_id')
            ->leftJoin('tipos_credito as tc', 'tc.id', '=', 'creditos.tipo_credito_id')
            ->leftJoin('garantias as g', 'g.id', '=', 'creditos.garantia_id')
            ->when($from, fn($q) => $q->whereDate('creditos.fecha_concesion', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('creditos.fecha_concesion', '<=', $to))
            ->when($search, function ($builder) use ($search) {
                $like = '%' . trim($search) . '%';
                $builder->where(function ($w) use ($like) {
                    $w->where('clientes.nombre_cliente', 'like', $like)
                        ->orWhere('clientes.nit', 'like', $like);
                });
            })
            ->when($asesor, function ($q) use ($asesor) {
                $ids = is_array($asesor) ? $asesor : [$asesor];
                $q->whereIn('creditos.asesor_id', array_filter($ids));
            })
            ->when($tipos, fn($q) => $q->whereIn('creditos.tipo_credito_id', array_filter($tipos)))
            ->when($estados, fn($q) => $q->whereIn('le.estado_id', array_filter($estados)))
            ->select([
                'creditos.id',
                'clientes.nombre_cliente as cliente',
                'tc.nombre as tipo',
                'g.nombre as garantia',
                'creditos.monto',
                'creditos.plazo',
                'creditos.fecha_concesion',
                'creditos.fecha_vencimiento',
                'asesores.name as asesor',
                'creditos.asesor_id',
                'le.estado_nombre as estado_actual',
            ])
            ->orderByDesc('creditos.id');

        $rows = $query->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'cliente' => $c->cliente,
                'tipo' => $c->tipo,
                'garantia' => $c->garantia,
                'monto' => (float) $c->monto,
                'plazo' => (int) $c->plazo,
                'fecha_concesion' => optional($c->fecha_concesion)->format('Y-m-d'),
                'fecha_vencimiento' => optional($c->fecha_vencimiento)->format('Y-m-d'),
                'asesor' => $c->asesor,
                'asesor_id' => $c->asesor_id,
                'estado' => $c->estado_actual ?? '—',
            ];
        });

        $total = $rows->count();
        $montoTotal = $rows->sum('monto');
        $promedio = $total ? round($montoTotal / $total, 2) : 0;
        $vencidos = $rows->where('fecha_vencimiento', '<', Carbon::today()->format('Y-m-d'))->count();

        $tiposCredito = DB::table('tipos_credito')->select('id', 'nombre')->orderBy('nombre')->get();
        $estadosCat = DB::table('estados_credito')->select('id', 'nombre')->orderBy('id')->get();
        $asesoresList = User::where(function ($q) {
            $q->where('asesor', 1)->orWhere('admin', 1);
        })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        if ($request->boolean('export')) {
            $headings = ['N°', 'Cliente', 'Tipo', 'Garantía', 'Monto (GTQ)', 'Plazo (meses)', 'Concesión', 'Vencimiento', 'Asesor', 'Estado'];
            $rowsForXlsx = $rows->map(fn($r) => [
                $r['id'],
                $r['cliente'],
                $r['tipo'],
                $r['garantia'],
                (float) $r['monto'],
                (int) $r['plazo'],
                $r['fecha_concesion'],
                $r['fecha_vencimiento'],
                $r['asesor'],
                $r['estado'],
            ])->toArray();

            $formats = [
                'E' => '#,##0.00',
                'F' => '0',
                'G' => 'yyyy-mm-dd',
                'H' => 'yyyy-mm-dd',
            ];

            return app(Exporter::class)->downloadExcel(
                $headings,
                $rowsForXlsx,
                'reporte_creditos',
                'Reporte de créditos — CHN',
                $formats
            );
        }

        return Inertia::render('Reportes/Creditos/Index', [
            'filters' => [
                'from' => $from,
                'to' => $to,
                'search' => $search,
                'asesor' => $asesor,
                'tipos' => $tipos,
                'estados' => $estados,
            ],
            'rows' => $rows,
            'kpis' => [
                'total' => $total,
                'montoTotal' => $montoTotal,
                'promedio' => $promedio,
                'vencidos' => $vencidos,
            ],
            'catalogs' => [
                'asesores' => $asesoresList,
                'tipos' => $tiposCredito,
                'estados' => $estadosCat,
            ],
        ]);
    }
}
