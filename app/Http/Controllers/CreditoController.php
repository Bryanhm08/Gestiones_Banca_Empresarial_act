<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\EstadoCredito;
use App\Models\CreditoEstado;
use App\Models\Amortizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class CreditoController extends Controller
{
    /**
     * Listado de créditos.
     * - Admin: ve todo
     * - Asesor: solo los suyos
     */
    public function index(): Response
    {
        $user    = Auth::user();
        $isAdmin = (bool) $user->admin;

        $query = Credito::with([
            'cliente:id,nombre_cliente,asesor_id',
            'asesor:id,name',
            'tipoCredito:id,nombre',
            'garantia:id,nombre',
        ])->latest();

        if (!$isAdmin) {
            $query->where('asesor_id', $user->id);
        }

        $creditos = $query->paginate(12)->withQueryString();

        return Inertia::render('Creditos/Index', [
            'creditos'  => $creditos,
            'canCreate' => true,
            'isAdmin'   => $isAdmin,
            'userId'    => $user->id,
        ]);
    }

    /**
     * Formulario de creación:
     * - Admin: puede elegir cualquier cliente y asesor
     * - Asesor: solo clientes propios y se fija defaultAsesorId = me.id
     */
    public function create(): Response
    {
        $user = Auth::user();

        $clientesQuery = DB::table('clientes')
            ->select('id', 'nombre_cliente')
            ->orderBy('nombre_cliente');

        if (!$user->admin) {
            $clientesQuery->where('asesor_id', $user->id);
        }

        $clientes  = $clientesQuery->get();
        $tipos     = DB::table('tipos_credito')->select('id', 'nombre')->orderBy('nombre')->get();
        $garantias = DB::table('garantias')->select('id', 'nombre')->orderBy('nombre')->get();

        $asesores = [];
        if ($user->admin) {
            $asesores = DB::table('users')
                ->select('id', 'name')
                ->when(DB::getSchemaBuilder()->hasColumn('users', 'estado'), fn ($q) => $q->where('estado', 1))
                ->when(DB::getSchemaBuilder()->hasColumn('users', 'asesor'), fn ($q) => $q->where('asesor', 1))
                ->orderBy('name')
                ->get();
        }

        return Inertia::render('Creditos/Create', [
            'clientes'        => $clientes,
            'tipos'           => $tipos,
            'garantias'       => $garantias,
            'asesores'        => $asesores,
            'isAdmin'         => (bool) $user->admin,
            'defaultAsesorId' => $user->id,
        ]);
    }

    /**
     * Crea un crédito:
     * - Admin: puede elegir asesor_id (opcional); si no, se usa su propio id
     * - Asesor: se fuerza asesor_id = auth()->id()
     * - La fecha de concesión se coloca automáticamente HOY.
     * - Vencimiento = hoy + plazo (meses)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validación sin fecha_concesion
        $data = $request->validate([
            'cliente_id'       => ['required', 'exists:clientes,id'],
            'tipo_credito_id'  => ['required', 'exists:tipos_credito,id'],
            'garantia_id'      => ['required', 'exists:garantias,id'],
            'monto'            => ['required', 'numeric', 'min:0.01'],
            'plazo'            => ['required', 'integer', 'min:1', 'max:360'],
            'asesor_id'        => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $asesorId = $user->admin ? ($data['asesor_id'] ?? $user->id) : $user->id;

        // Si es asesor, valida que el cliente sea suyo
        if (!$user->admin) {
            $clienteOwner = DB::table('clientes')
                ->where('id', $data['cliente_id'])
                ->value('asesor_id');

            if ((int) $clienteOwner !== (int) $user->id) {
                abort(403, 'No puedes asignar créditos a clientes de otro asesor.');
            }
        }

        $concesion   = Carbon::today();
        $vencimiento = (clone $concesion)->addMonths((int) $data['plazo']);

        DB::transaction(function () use ($data, $concesion, $vencimiento, $asesorId) {
            Credito::create([
                'cliente_id'        => $data['cliente_id'],
                'tipo_credito_id'   => $data['tipo_credito_id'],
                'garantia_id'       => $data['garantia_id'],
                'monto'             => $data['monto'],
                'plazo'             => $data['plazo'],
                'fecha_concesion'   => $concesion->format('Y-m-d'),
                'fecha_vencimiento' => $vencimiento->format('Y-m-d'),
                'asesor_id'         => $asesorId,
            ]);
        });

        return redirect()->route('creditos.index')->with('success', 'Crédito creado con éxito.');
    }

    /**
     * Vista de edición con timeline y catálogo de etapas.
     * Se envían TODAS las etapas nuevas (vigentes) que aún no se han usado,
     * marcando cuáles están habilitadas para seleccionar según las reglas.
     */
    public function edit(int $id): Response
    {
        $user    = Auth::user();

        $credito = Credito::with([
            'cliente:id,nombre_cliente,asesor_id',
            'asesor:id,name',
            'tipoCredito:id,nombre',
            'garantia:id,nombre',
        ])->findOrFail($id);

        if (!$user->admin && $credito->asesor_id !== $user->id) {
            abort(403);
        }

        // Catálogo de NUEVAS etapas (solo vigentes, con orden)
        $estadosAll = EstadoCredito::vigentes()
            ->select('id', 'nombre', 'orden')
            ->orderBy('orden')
            ->get();

        // Timeline actual
        $timeline = CreditoEstado::query()
            ->with(['estado:id,nombre,orden'])
            ->where('credito_id', $id)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($e) => [
                'id'         => $e->id,
                'estado_id'  => $e->estado_id,
                'estado'     => $e->estado?->nombre,
                'orden'      => $e->estado?->orden,
                'created_at' => $e->created_at?->format('Y-m-d H:i'),
            ]);

        // Estados ya alcanzados
        $alcanzadosIds   = $timeline->pluck('estado_id')->filter()->all();
        $alcanzadosOrden = $timeline->pluck('orden')->filter()->all();
        $maxOrden        = empty($alcanzadosOrden) ? 0 : max($alcanzadosOrden);

        // Reglas de avance (6→7 u 8, 7→8, general→max+1)
        $allowedOrden = [];
        if ($maxOrden === 0) {
            $allowedOrden = [1];
        } elseif ($maxOrden === 6) {
            $allowedOrden = [7, 8];
        } elseif ($maxOrden === 7) {
            $allowedOrden = [8];
        } elseif ($maxOrden < 10) {
            $allowedOrden = [$maxOrden + 1];
        } else {
            $allowedOrden = [];
        }

        // Enviar TODO el catálogo NUEVO (sin repetir los ya usados),
        // y marcar allowed/disabled para el Dropdown del front.
        $estadosCatalog = $estadosAll
            ->reject(fn ($e) => in_array($e->id, $alcanzadosIds, true))
            ->values()
            ->map(function ($e) use ($allowedOrden) {
                $isAllowed = in_array($e->orden, $allowedOrden, true);
                return [
                    'id'       => $e->id,
                    'nombre'   => $e->nombre,
                    'orden'    => $e->orden,
                    'allowed'  => $isAllowed,
                    'disabled' => !$isAllowed, // útil para PrimeVue optionDisabled
                ];
            });

        $amortizaciones = Amortizacion::where('credito_id', $id)
            ->orderBy('fecha_pago')
            ->get()
            ->map(fn ($a) => [
                'id'         => $a->id,
                'fecha_pago' => $a->fecha_pago?->format('Y-m-d'),
                'status'     => $a->status,
            ]);

        $tipos     = DB::table('tipos_credito')->select('id', 'nombre')->orderBy('nombre')->get();
        $garantias = DB::table('garantias')->select('id', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Creditos/Edit', [
            'credito' => [
                'id'                => $credito->id,
                'cliente'           => $credito->cliente?->nombre_cliente,
                'cliente_id'        => $credito->cliente_id,
                'asesor'            => $credito->asesor?->name,
                'asesor_id'         => $credito->asesor_id,
                'tipo'              => $credito->tipoCredito?->nombre,
                'tipo_credito_id'   => $credito->tipo_credito_id,
                'garantia'          => $credito->garantia?->nombre,
                'garantia_id'       => $credito->garantia_id,
                'monto'             => (float) $credito->monto,
                'plazo'             => (int) $credito->plazo,
                'fecha_concesion'   => optional($credito->fecha_concesion)->format('Y-m-d'),
                'fecha_vencimiento' => optional($credito->fecha_vencimiento)->format('Y-m-d'),
            ],
            'tipos'            => $tipos,
            'garantias'        => $garantias,
            'estadosCatalog'   => $estadosCatalog, // TODAS nuevas (sin usadas) + allowed/disabled
            'timeline'         => $timeline,
            'isAdmin'          => (bool) $user->admin,
        ]);
    }

    /**
     * Actualiza datos básicos del crédito.
     */
    public function update(Request $request, int $id)
    {
        $user    = Auth::user();
        $credito = Credito::findOrFail($id);

        if (!$user->admin && $credito->asesor_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'tipo_credito_id'  => ['required', 'exists:tipos_credito,id'],
            'garantia_id'      => ['required', 'exists:garantias,id'],
            'monto'            => ['required', 'numeric', 'min:0.01'],
            'plazo'            => ['required', 'integer', 'min:1', 'max:360'],
            'fecha_concesion'  => ['required', 'date'],
        ]);

        $concesion                    = Carbon::parse($data['fecha_concesion']);
        $data['fecha_vencimiento']    = $concesion->copy()->addMonths((int) $data['plazo'])->format('Y-m-d');
        $data['fecha_concesion']      = $concesion->format('Y-m-d');

        $credito->update($data);

        return back()->with('success', 'Crédito actualizado.');
    }

    /**
     * Agrega una etapa con validación de orden y sin duplicados.
     */
    public function addEstado(Request $request, int $id)
    {
        $user    = Auth::user();
        $credito = Credito::findOrFail($id);

        if (!$user->admin && $credito->asesor_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'estado_id' => ['required', 'exists:estados_credito,id'],
        ]);

        // Solo aceptar estados VIGENTES (nuevos, con 'orden')
        $estado = EstadoCredito::vigentes()
            ->select('id','nombre','orden')
            ->find($validated['estado_id']);

        if (!$estado) {
            return response()->json(['message' => 'Etapa no válida.'], 422);
        }

        // No duplicar
        $yaExiste = CreditoEstado::where('credito_id', $id)
            ->where('estado_id', $estado->id)
            ->exists();

        if ($yaExiste) {
            return response()->json(['message' => 'La etapa ya fue registrada anteriormente.'], 422);
        }

        // Orden alcanzado
        $timeline = CreditoEstado::with('estado:id,orden')
            ->where('credito_id', $id)
            ->get();

        $alcanzadosOrden = $timeline->pluck('estado.orden')->filter()->all();
        $maxOrden        = empty($alcanzadosOrden) ? 0 : max($alcanzadosOrden);

        // Reglas de avance (6→7 u 8, 7→8, general→max+1)
        $allowedOrden = [];
        if ($maxOrden === 0) {
            $allowedOrden = [1];
        } elseif ($maxOrden === 6) {
            $allowedOrden = [7, 8];
        } elseif ($maxOrden === 7) {
            $allowedOrden = [8];
        } elseif ($maxOrden < 10) {
            $allowedOrden = [$maxOrden + 1];
        } else {
            $allowedOrden = [];
        }

        if (!in_array($estado->orden, $allowedOrden, true)) {
            return response()->json([
                'message' => 'La etapa seleccionada no respeta el orden permitido.',
            ], 422);
        }

        $entry = CreditoEstado::create([
            'credito_id' => $id,
            'estado_id'  => $estado->id,
        ])->load('estado');

        return response()->json([
            'id'         => $entry->id,
            'estado_id'  => $entry->estado_id,
            'estado'     => $entry->estado?->nombre,
            'created_at' => $entry->created_at?->format('Y-m-d H:i'),
            'message'    => 'Etapa agregada.',
        ], 201);
    }

    public function storeAmortizacion(Request $request, int $id)
    {
        $user    = Auth::user();
        $credito = Credito::findOrFail($id);

        if (!$user->admin && $credito->asesor_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'fecha_pago' => ['required', 'date'],
            'status'     => ['nullable', 'in:Pagado,Pendiente'],
        ]);

        $a = Amortizacion::create([
            'credito_id' => $id,
            'fecha_pago' => $validated['fecha_pago'],
            'status'     => $validated['status'] ?? 'Pendiente',
        ]);

        return response()->json([
            'id'         => $a->id,
            'fecha_pago' => $a->fecha_pago?->format('Y-m-d'),
            'status'     => $a->status,
            'message'    => 'Amortización añadida.',
        ], 201);
    }

    public function toggleAmortizacion(int $amortizacionId)
    {
        $a    = Amortizacion::with('credito:id,asesor_id')->findOrFail($amortizacionId);
        $user = Auth::user();

        if (!$user->admin && $a->credito?->asesor_id !== $user->id) {
            abort(403);
        }

        $a->status = $a->status === 'Pagado' ? 'Pendiente' : 'Pagado';
        $a->save();

        return response()->json([
            'id'      => $a->id,
            'status'  => $a->status,
            'message' => 'Estado actualizado.',
        ]);
    }

    public function destroyAmortizacion(int $amortizacionId)
    {
        $a    = Amortizacion::with('credito:id,asesor_id')->findOrFail($amortizacionId);
        $user = Auth::user();

        if (!$user->admin && $a->credito?->asesor_id !== $user->id) {
            abort(403);
        }

        $a->delete();

        return response()->json(['message' => 'Amortización eliminada.']);
    }

    public function show(int $id): Response
    {
        $user    = Auth::user();

        $credito = Credito::with([
            'cliente:id,nombre_cliente,asesor_id',
            'asesor:id,name',
            'tipoCredito:id,nombre',
            'garantia:id,nombre',
        ])->findOrFail($id);

        if (!$user->admin && $credito->asesor_id !== $user->id) {
            abort(403, 'No autorizado para ver este crédito.');
        }

        return Inertia::render('Creditos/Show', [
            'credito' => [
                'id'                => $credito->id,
                'cliente'           => $credito->cliente?->nombre_cliente,
                'tipo'              => $credito->tipoCredito?->nombre,
                'garantia'          => $credito->garantia?->nombre,
                'monto'             => (float) $credito->monto,
                'plazo'             => (int) $credito->plazo,
                'fecha_concesion'   => optional($credito->fecha_concesion)->format('Y-m-d'),
                'fecha_vencimiento' => optional($credito->fecha_vencimiento)->format('Y-m-d'),
                'asesor'            => $credito->asesor?->name,
            ],
        ]);
    }
}
