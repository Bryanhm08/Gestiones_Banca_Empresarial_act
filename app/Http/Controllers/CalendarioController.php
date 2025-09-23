<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Credito;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class CalendarioController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $clientes = Cliente::query()
            ->select('id', 'nombre_cliente', 'asesor_id')
            ->when(!$user->admin, fn($q) => $q->where('asesor_id', $user->id))
            ->orderBy('nombre_cliente')
            ->get();

        // Para admin: lista de asesores; para asesor, sólo él mismo
        $asesores = $user->admin
            ? User::query()->select('id','name')->orderBy('name')->get()
            : collect([['id' => $user->id, 'name' => $user->name]]);

        return Inertia::render('Calendario/Index', [
            'clientes' => $clientes,
            'asesores' => $asesores,
            'isAdmin'  => (bool) $user->admin,
        ]);
    }

    /**
     * Fuente de eventos para el calendario:
     * - Reuniones (calendar_events del asesor)
     * - Vencimientos de créditos (del asesor)
     * - Actualización de cuentas = fecha_apertura/created_at + 365 días
     */
    public function events(Request $request)
    {
        $user = Auth::user();

        // 1) Reuniones
        $meetings = CalendarEvent::query()
            ->with('cliente:id,nombre_cliente')
            ->when(!$user->admin, fn($q) => $q->where('asesor_id', $user->id))
            ->orderBy('start_at')
            ->get()
            ->map(function($e){
                return [
                    'id'        => "M{$e->id}",
                    'type'      => 'meeting',
                    'title'     => $e->title,
                    'description' => $e->description,
                    'start'     => optional($e->start_at)->toAtomString(),
                    'end'       => optional($e->end_at)->toAtomString(),
                    'location'  => $e->location,
                    'cliente'   => $e->cliente ? ['id' => $e->cliente->id, 'nombre' => $e->cliente->nombre_cliente] : null,
                ];
            });

        // 2) Vencimientos de créditos
        $creditos = Credito::query()
            ->select('id','cliente_id','asesor_id','fecha_vencimiento','monto')
            ->with('cliente:id,nombre_cliente')
            ->when(!$user->admin, fn($q) => $q->where('asesor_id', $user->id))
            ->whereNotNull('fecha_vencimiento')
            ->get()
            ->map(function($c){
                return [
                    'id'      => "C{$c->id}",
                    'type'    => 'credito_vencimiento',
                    'title'   => 'Vencimiento de crédito',
                    'start'   => optional($c->fecha_vencimiento)->toAtomString(),
                    'end'     => null,
                    'cliente' => $c->cliente ? ['id' => $c->cliente->id, 'nombre' => $c->cliente->nombre_cliente] : null,
                    'monto'   => (float) $c->monto,
                ];
            });

        // 3) Actualización de cuentas
        $cuentas = Cuenta::query()
            ->select('id','cliente_id','asesor_id','fecha_apertura','created_at')
            ->with('cliente:id,nombre_cliente')
            ->when(!$user->admin, fn($q) => $q->where('asesor_id', $user->id))
            ->get()
            ->map(function($c){
                $base = $c->fecha_apertura ?? $c->created_at;
                $next = $base ? Carbon::parse($base)->addDays(365) : null;
                return [
                    'id'      => "A{$c->id}",
                    'type'    => 'cuenta_actualizacion',
                    'title'   => 'Actualización de cuenta',
                    'start'   => $next?->toAtomString(),
                    'end'     => null,
                    'cliente' => $c->cliente ? ['id' => $c->cliente->id, 'nombre' => $c->cliente->nombre_cliente] : null,
                ];
            })
            ->filter(fn($e) => !empty($e['start']))
            ->values();

        return response()->json([
            'events' => $meetings->merge($creditos)->merge($cuentas)->values(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'cliente_id'  => ['nullable', 'exists:clientes,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at'    => ['required', 'date'],
            'end_at'      => ['nullable', 'date','after_or_equal:start_at'],
            'location'    => ['nullable', 'string', 'max:255'],
        ]);

        // Un asesor sólo puede agendar con sus clientes
        if (!$user->admin && !empty($data['cliente_id'])) {
            $owner = \DB::table('clientes')->where('id', $data['cliente_id'])->value('asesor_id');
            if ((int)$owner !== (int)$user->id) abort(403);
        }

        $data['asesor_id'] = $user->id;
        $event = CalendarEvent::create($data);

        return response()->json(['ok' => true, 'id' => $event->id]);
    }

    public function destroy(int $id)
    {
        $user  = Auth::user();
        $event = CalendarEvent::findOrFail($id);

        if (!$user->admin && $event->asesor_id !== $user->id) abort(403);

        $event->delete();
        return response()->json(['ok' => true]);
    }

    /** Exporta CSV con filtros (admin ve todo; asesor sólo lo propio). */
    public function report(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'cliente_id'   => ['nullable', 'integer', 'exists:clientes,id'],
            'asesor_id'    => ['nullable', 'integer', 'exists:users,id'],
            'from'         => ['nullable', 'date'],
            'to'           => ['nullable', 'date', 'after_or_equal:from'],
            'include'      => ['nullable', 'array'],
            'include.*'    => ['in:meeting,credito_vencimiento,cuenta_actualizacion'],
        ]);

        $include = $data['include'] ?? ['meeting','credito_vencimiento','cuenta_actualizacion'];

        if (!$user->admin) {
            $data['asesor_id'] = $user->id;
        }

        $from = !empty($data['from']) ? Carbon::parse($data['from'])->startOfDay() : null;
        $to   = !empty($data['to'])   ? Carbon::parse($data['to'])->endOfDay()   : null;

        // Reuniones
        $meetings = collect();
        if (in_array('meeting', $include, true)) {
            $meetings = CalendarEvent::query()
                ->with(['cliente:id,nombre_cliente', 'asesor:id,name'])
                ->when(!empty($data['asesor_id']), fn($q) => $q->where('asesor_id', $data['asesor_id']))
                ->when(!empty($data['cliente_id']), fn($q) => $q->where('cliente_id', $data['cliente_id']))
                ->when($from, fn($q) => $q->where('start_at', '>=', $from))
                ->when($to,   fn($q) => $q->where('start_at', '<=', $to))
                ->orderBy('start_at')
                ->get()
                ->map(function($e){
                    return [
                        'fecha'   => optional($e->start_at)->format('Y-m-d H:i'),
                        'tipo'    => 'VISITA',
                        'cliente' => $e->cliente?->nombre_cliente ?? '',
                        'asesor'  => $e->asesor?->name ?? '',
                        'detalle' => $e->title,
                        'monto'   => '',
                        'lugar'   => $e->location ?? '',
                        'id'      => "M{$e->id}",
                    ];
                });
        }

        // Vencimientos crédito
        $credits = collect();
        if (in_array('credito_vencimiento', $include, true)) {
            $credits = Credito::query()
                ->select('id','cliente_id','asesor_id','fecha_vencimiento','monto')
                ->with(['cliente:id,nombre_cliente', 'asesor:id,name'])
                ->when(!empty($data['asesor_id']), fn($q) => $q->where('asesor_id', $data['asesor_id']))
                ->when(!empty($data['cliente_id']), fn($q) => $q->where('cliente_id', $data['cliente_id']))
                ->whereNotNull('fecha_vencimiento')
                ->when($from, fn($q) => $q->where('fecha_vencimiento', '>=', $from))
                ->when($to,   fn($q) => $q->where('fecha_vencimiento', '<=', $to))
                ->get()
                ->map(function($c){
                    return [
                        'fecha'   => optional($c->fecha_vencimiento)->format('Y-m-d'),
                        'tipo'    => 'VENCIMIENTO_CREDITO',
                        'cliente' => $c->cliente?->nombre_cliente ?? '',
                        'asesor'  => $c->asesor?->name ?? '',
                        'detalle' => "Crédito #{$c->id}",
                        'monto'   => (string) $c->monto,
                        'lugar'   => '',
                        'id'      => "C{$c->id}",
                    ];
                });
        }

        // Actualizaciones de cuenta
        $accounts = collect();
        if (in_array('cuenta_actualizacion', $include, true)) {
            $accounts = Cuenta::query()
                ->select('id','cliente_id','asesor_id','fecha_apertura','created_at')
                ->with(['cliente:id,nombre_cliente', 'asesor:id,name'])
                ->when(!empty($data['asesor_id']), fn($q) => $q->where('asesor_id', $data['asesor_id']))
                ->when(!empty($data['cliente_id']), fn($q) => $q->where('cliente_id', $data['cliente_id']))
                ->get()
                ->map(function($c){
                    $base = $c->fecha_apertura ?? $c->created_at;
                    $next = $base ? Carbon::parse($base)->addDays(365) : null;
                    return [
                        'fecha'   => $next?->format('Y-m-d'),
                        'tipo'    => 'ACTUALIZACION_CUENTA',
                        'cliente' => $c->cliente?->nombre_cliente ?? '',
                        'asesor'  => $c->asesor?->name ?? '',
                        'detalle' => "Cuenta #{$c->id}",
                        'monto'   => '',
                        'lugar'   => '',
                        'id'      => "A{$c->id}",
                    ];
                })
                ->filter(function($row) use ($from, $to){
                    if (empty($row['fecha'])) return false;
                    $d = Carbon::parse($row['fecha']);
                    if ($from && $d->lt($from)) return false;
                    if ($to && $d->gt($to)) return false;
                    return true;
                })
                ->values();
        }

        $rows = $meetings->merge($credits)->merge($accounts)->sortBy('fecha')->values();
        $filename = 'reporte_calendario_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Fecha','Tipo','Cliente','Asesor','Detalle','Monto','Lugar','ID']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['fecha'], $r['tipo'], $r['cliente'], $r['asesor'],
                    $r['detalle'], $r['monto'], $r['lugar'], $r['id']
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
