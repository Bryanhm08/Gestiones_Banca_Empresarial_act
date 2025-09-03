<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCreditoRequest;
use App\Models\Credito;
use App\Models\EstadoCredito;
use App\Models\CreditoEstado;
use App\Models\Amortizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class CreditoController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = (bool) $user->admin;

        $query = Credito::with([
            'cliente:id,nombre_cliente',
            'asesor:id,name',
            'tipoCredito:id,nombre',
            'garantia:id,nombre',
        ]);
        if (!$isAdmin) {
            $query->where('asesor_id', $user->id);
        }

        $creditos = $query->latest()->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'cliente' => $c->cliente?->nombre_cliente,
                'tipo' => $c->tipoCredito?->nombre,
                'garantia' => $c->garantia?->nombre,
                'monto' => (float) $c->monto,
                'plazo' => (int) $c->plazo,
                'fecha_concesion' => optional($c->fecha_concesion)->format('Y-m-d'),
                'fecha_vencimiento' => optional($c->fecha_vencimiento)->format('Y-m-d'),
                'asesor' => $c->asesor?->name,
                'asesor_id' => $c->asesor_id,
            ];
        });
        $canCreate = true;

        return Inertia::render('Creditos/Index', [
            'creditos' => $creditos,
            'canCreate' => $canCreate,
            'isAdmin' => $isAdmin,
            'userId' => $user->id,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $clientesQuery = DB::table('clientes')->select('id', 'nombre_cliente')->orderBy('nombre_cliente');
        if (!$user->admin) {
            $clientesQuery->where('asesor_id', $user->id);
        }
        $clientes = $clientesQuery->get();

        $tipos = DB::table('tipos_credito')->select('id', 'nombre')->orderBy('nombre')->get();
        $garantias = DB::table('garantias')->select('id', 'nombre')->orderBy('nombre')->get();

        $asesores = [];
        if ($user->admin) {
            $asesores = DB::table('users')->select('id', 'name')
                ->where('estado', 1)
                ->where('asesor', 1)
                ->orderBy('name')->get();
        }

        return Inertia::render('Creditos/Create', [
            'clientes' => $clientes,
            'tipos' => $tipos,
            'garantias' => $garantias,
            'asesores' => $asesores,
            'isAdmin' => (bool) $user->admin,
            'defaultAsesorId' => $user->id,
        ]);
    }

    public function store(StoreCreditoRequest $request)
    {
        $user = Auth::user();
        $concesion = Carbon::parse($request->fecha_concesion);
        $vencimiento = (clone $concesion)->addMonths((int) $request->plazo);

        $asesorId = $user->admin ? ($request->asesor_id ?? $user->id) : $user->id;

        Credito::create([
            'cliente_id' => $request->cliente_id,
            'tipo_credito_id' => $request->tipo_credito_id,
            'garantia_id' => $request->garantia_id,
            'monto' => $request->monto,
            'plazo' => $request->plazo,
            'fecha_concesion' => $concesion->format('Y-m-d'),
            'fecha_vencimiento' => $vencimiento->format('Y-m-d'),
            'asesor_id' => $asesorId,
        ]);

        return redirect()->route('mis.asignaciones')->with('success', 'Crédito creado con éxito.');

    }


    public function edit(int $id)
    {
        $user = Auth::user();

        $credito = Credito::with([
            'cliente:id,nombre_cliente',
            'asesor:id,name',
            'tipoCredito:id,nombre',
            'garantia:id,nombre',
        ])->findOrFail($id);

        if (!$user->admin && $credito->asesor_id !== $user->id) {
            abort(403);
        }

        $estadosCatalog = EstadoCredito::select('id', 'nombre')->orderBy('id')->get();

        $timeline = CreditoEstado::query()
            ->with(['estado:id,nombre'])
            ->where('credito_id', $id)
            ->orderBy('created_at')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'estado_id' => $e->estado_id,
                'estado' => $e->estado?->nombre,
                'created_at' => $e->created_at?->format('Y-m-d H:i'),
            ]);

        $amortizaciones = Amortizacion::where('credito_id', $id)
            ->orderBy('fecha_pago')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'fecha_pago' => $a->fecha_pago?->format('Y-m-d'),
                'status' => $a->status,
            ]);

        $tipos = DB::table('tipos_credito')->select('id', 'nombre')->orderBy('nombre')->get();
        $garantias = DB::table('garantias')->select('id', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Creditos/Edit', [
            'credito' => [
                'id' => $credito->id,
                'cliente' => $credito->cliente?->nombre_cliente,
                'cliente_id' => $credito->cliente_id,
                'asesor' => $credito->asesor?->name,
                'asesor_id' => $credito->asesor_id,
                'tipo' => $credito->tipoCredito?->nombre,
                'tipo_credito_id' => $credito->tipo_credito_id,
                'garantia' => $credito->garantia?->nombre,
                'garantia_id' => $credito->garantia_id,
                'monto' => (float) $credito->monto,
                'plazo' => (int) $credito->plazo,
                'fecha_concesion' => optional($credito->fecha_concesion)->format('Y-m-d'),
                'fecha_vencimiento' => optional($credito->fecha_vencimiento)->format('Y-m-d'),
            ],
            'tipos' => $tipos,
            'garantias' => $garantias,
            'estadosCatalog' => $estadosCatalog,
            'timeline' => $timeline,
            'amortizaciones' => $amortizaciones,
            'isAdmin' => (bool) $user->admin,
        ]);
    }


    public function update(Request $request, int $id)
    {
        $user = Auth::user();
        $credito = Credito::findOrFail($id);

        if (!$user->admin && $credito->asesor_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'tipo_credito_id' => ['required', 'exists:tipos_credito,id'],
            'garantia_id' => ['required', 'exists:garantias,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'plazo' => ['required', 'integer', 'min:1', 'max:360'],
            'fecha_concesion' => ['required', 'date'],
        ]);

        $concesion = Carbon::parse($data['fecha_concesion']);
        $data['fecha_vencimiento'] = $concesion->copy()->addMonths((int) $data['plazo'])->format('Y-m-d');
        $data['fecha_concesion'] = $concesion->format('Y-m-d');

        $credito->update($data);

        return back()->with('success', 'Crédito actualizado.');
    }

    public function addEstado(Request $request, int $id)
    {
        $user = Auth::user();
        $credito = Credito::findOrFail($id);
        if (!$user->admin && $credito->asesor_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'estado_id' => ['required', 'exists:estados_credito,id'],
        ]);

        $entry = CreditoEstado::create([
            'credito_id' => $id,
            'estado_id' => $validated['estado_id'],
        ])->load('estado');

        return response()->json([
            'id' => $entry->id,
            'estado_id' => $entry->estado_id,
            'estado' => $entry->estado?->nombre,
            'created_at' => $entry->created_at?->format('Y-m-d H:i'),
            'message' => 'Etapa agregada.',
        ], 201);
    }

    public function storeAmortizacion(Request $request, int $id)
    {
        $user = Auth::user();
        $credito = Credito::findOrFail($id);
        if (!$user->admin && $credito->asesor_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'fecha_pago' => ['required', 'date'],
            'status' => ['nullable', 'in:Pagado,Pendiente'],
        ]);

        $a = Amortizacion::create([
            'credito_id' => $id,
            'fecha_pago' => $validated['fecha_pago'],
            'status' => $validated['status'] ?? 'Pendiente',
        ]);

        return response()->json([
            'id' => $a->id,
            'fecha_pago' => $a->fecha_pago?->format('Y-m-d'),
            'status' => $a->status,
            'message' => 'Amortización añadida.',
        ], 201);
    }

    public function toggleAmortizacion(int $amortizacionId)
    {
        $a = Amortizacion::findOrFail($amortizacionId);
        $user = Auth::user();
        if (!$user->admin && $a->credito?->asesor_id !== $user->id) {
            abort(403);
        }

        $a->status = $a->status === 'Pagado' ? 'Pendiente' : 'Pagado';
        $a->save();

        return response()->json([
            'id' => $a->id,
            'status' => $a->status,
            'message' => 'Estado actualizado.',
        ]);
    }

    public function destroyAmortizacion(int $amortizacionId)
    {
        $a = Amortizacion::findOrFail($amortizacionId);
        $user = Auth::user();
        if (!$user->admin && $a->credito?->asesor_id !== $user->id) {
            abort(403);
        }
        $a->delete();

        return response()->json(['message' => 'Amortización eliminada.']);
    }
    public function show(int $id)
    {
        $user = auth()->user();

        $credito = Credito::with([
            'cliente:id,nombre_cliente',
            'asesor:id,name',
            'tipoCredito:id,nombre',
            'garantia:id,nombre',
        ])->findOrFail($id);

        if (!$user->admin && $credito->asesor_id !== $user->id) {
            abort(403, 'No autorizado para ver este crédito.');
        }

        return Inertia::render('Creditos/Show', [
            'credito' => [
                'id' => $credito->id,
                'cliente' => $credito->cliente?->nombre_cliente,
                'tipo' => $credito->tipoCredito?->nombre,
                'garantia' => $credito->garantia?->nombre,
                'monto' => (float) $credito->monto,
                'plazo' => (int) $credito->plazo,
                'fecha_concesion' => optional($credito->fecha_concesion)->format('Y-m-d'),
                'fecha_vencimiento' => optional($credito->fecha_vencimiento)->format('Y-m-d'),
                'asesor' => $credito->asesor?->name,
            ],
        ]);
    }

}
