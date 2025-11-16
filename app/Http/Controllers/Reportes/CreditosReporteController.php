<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Credito;
use App\Models\EstadoCredito;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\CreditosExport;
use Maatwebsite\Excel\Facades\Excel;

class CreditosReporteController extends Controller
{
    public function index(Request $request)
    {
        // (Opcional) políticas: $this->authorize('viewAny', Credito::class);

        $estadoId = $request->integer('estado_id');
        $search   = trim((string) $request->get('q'));

        $query = Credito::query()
            ->with([
                'cliente:id,nombre_cliente',
                'tipoCredito:id,nombre',
                'garantia:id,nombre',
                'asesor:id,name',
                'ultimoEstado.estado:id,nombre',
            ]);

        if ($estadoId) {
            $query->whereHas('ultimoEstado', function ($q) use ($estadoId) {
                $q->where('estado_id', $estadoId);
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('cliente', fn ($c) => $c->where('nombre_cliente', 'like', "%{$search}%"))
                  ->orWhereHas('asesor',  fn ($a) => $a->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('tipoCredito', fn ($t) => $t->where('nombre', 'like', "%{$search}%"));
            });
        }

        $creditos = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($c) {
                return [
                    'id'       => $c->id,
                    'cliente'  => $c->cliente?->nombre_cliente,
                    'tipo'     => $c->tipoCredito?->nombre,
                    'garantia' => $c->garantia?->nombre,
                    'monto'    => (float) $c->monto,
                    'plazo'    => (int) $c->plazo,
                    'asesor'   => $c->asesor?->name,
                    // 👇 Ya NO devolvemos fecha_concesion / fecha_vencimiento
                    'etapa'    => $c->ultimoEstado?->estado?->nombre ?? '—',
                    'etapa_id' => $c->ultimoEstado?->estado_id,
                ];
            });

        $estadosCatalog = EstadoCredito::select('id', 'nombre')->orderBy('id')->get();

        return Inertia::render('Reportes/Creditos/Index', [
            'creditos'       => $creditos,
            'estadosCatalog' => $estadosCatalog,
            'filters'        => [
                'estado_id' => $estadoId,
                'q'         => $search,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $estadoId = $request->integer('estado_id');
        $search   = trim((string) $request->get('q'));

        $builder = Credito::query()
            ->with([
                'cliente:id,nombre_cliente',
                'tipoCredito:id,nombre',
                'garantia:id,nombre',
                'asesor:id,name',
                'ultimoEstado.estado:id,nombre',
            ]);

        if ($estadoId) {
            $builder->whereHas('ultimoEstado', fn ($q) => $q->where('estado_id', $estadoId));
        }

        if ($search !== '') {
            $builder->where(function ($q) use ($search) {
                $q->whereHas('cliente', fn ($c) => $c->where('nombre_cliente', 'like', "%{$search}%"))
                  ->orWhereHas('asesor',  fn ($a) => $a->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('tipoCredito', fn ($t) => $t->where('nombre', 'like', "%{$search}%"));
            });
        }

        // Usa la clase CreditosExport SIN fechas de concesión/vencimiento
        return Excel::download(new CreditosExport($builder), 'reporte_creditos.xlsx');
    }
}
