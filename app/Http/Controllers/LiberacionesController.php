<?php

namespace App\Http\Controllers;

use App\Models\Liberacion;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LiberacionExport;
use Illuminate\Support\Facades\Schema as DBSchema;

class LiberacionesController extends Controller
{
    public function index(Request $r)
    {
        $q = $r->get('q');

        $libs = Liberacion::with('cliente')
            ->when($q, fn($qq) => $qq->where('nombre', 'like', "%$q%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $libs->getCollection()->transform(function ($lib) {
            $lib->cliente_nombre = $lib->cliente
                ? $this->resolveClienteNombre($lib->cliente)
                : '—';
            return $lib;
        });

        $ultimosClientes = Cliente::latest()
            ->limit(8)
            ->get()
            ->map(fn($c) => [
                'id'     => $c->id,
                'nombre' => $this->resolveClienteNombre($c),
            ])
            ->values();

        return Inertia::render('Liberaciones/Index', [
            'libs'            => $libs,
            'ultimosClientes' => $ultimosClientes,
        ]);
    }

    public function create()
    {
        $defaultCols = collect([
            ['id' => 'correlativo', 'label' => 'Correlativo'],
            ['id' => 'cliente',     'label' => 'Cliente'],
            ['id' => 'finca',       'label' => 'Finca'],
            ['id' => 'folio',       'label' => 'Folio'],
            ['id' => 'libro',       'label' => 'Libro'],
            ['id' => 'monto',       'label' => 'Monto'],
        ]);

        $recent = Cliente::latest()
            ->limit(10)
            ->get()
            ->map(fn($c) => [
                'id'     => $c->id,
                'nombre' => $this->resolveClienteNombre($c),
            ])
            ->values();

        return Inertia::render('Liberaciones/Create', [
            'defaultCols'   => $defaultCols,
            'recentClients' => $recent,
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'nombre'     => ['nullable', 'string', 'max:190'],
            'columns'    => ['required', 'array', 'min:1'],
            'rows'       => ['nullable', 'array'],
        ]);

        Liberacion::create([
            'cliente_id' => $data['cliente_id'] ?? null,
            'nombre'     => $data['nombre'] ?? null,
            'columns'    => $data['columns'],
            'rows'       => $data['rows'] ?? [],
            'created_by' => $r->user()->id,
            'updated_by' => $r->user()->id,
        ]);

        return redirect()->route('liberaciones.index')->with('ok', 'Cuadro de liberación creado.');
    }

    public function show(Liberacion $lib, Request $r)
    {
        $filters = $r->only(['correlativo', 'cliente', 'finca', 'folio', 'libro', 'monto', 'status']);

        return Inertia::render('Liberaciones/Show', [
            'lib'     => $lib->only(['id', 'nombre', 'columns', 'rows', 'cliente_id']),
            'filters' => $filters,
        ]);
    }

    public function edit(Liberacion $lib)
    {
        return Inertia::render('Liberaciones/Edit', [
            'lib' => $lib->only(['id', 'nombre', 'columns', 'rows', 'cliente_id']),
        ]);
    }

    public function updateStructure(Liberacion $lib, Request $r)
    {
        $data = $r->validate([
            'columns' => 'required|array|min:1',
            'rows'    => 'nullable|array',
        ]);

        $lib->columns    = $data['columns'];
        $lib->rows       = $data['rows'] ?? [];
        $lib->updated_by = $r->user()->id;
        $lib->save();

        return redirect()->route('liberaciones.show', $lib->id)->with('ok', 'Estructura actualizada.');
    }

    public function updateRowStatus(Liberacion $lib, string $rowId, Request $r)
    {
        $status = $r->validate(['status' => 'required|in:pendiente,liberado'])['status'];

        $rows = collect($lib->rows ?? [])->map(function ($row) use ($rowId, $status) {
            if (($row['id'] ?? null) === $rowId) {
                $row['status'] = $status;
            }
            return $row;
        });

        $lib->rows       = $rows;
        $lib->updated_by = $r->user()->id;
        $lib->save();

        return back();
    }

  public function exportExcel(Liberacion $lib)
{
    $filename = 'Liberaciones_' . $lib->id . '_' . now()->format('Ymd_His') . '.xlsx';

    // 1) Genera el archivo en storage/app/public
    \Maatwebsite\Excel\Facades\Excel::store(
        new \App\Exports\LiberacionExport($lib),
        $filename,
        'public' // usa el disco "public"
    );

    // 2) Descarga y borra después de enviar
    $fullPath = storage_path('app/public/' . $filename);
    return response()->download($fullPath)->deleteFileAfterSend(true);
}


    private function resolveClienteNombre($c): string
    {
        $table = $c->getTable();
        $cols  = DBSchema::getColumnListing($table);

        $directCandidates = [
            'nombre', 'razon_social', 'nombre_comercial', 'cliente',
            'nombre_cliente', 'nombre_legal', 'denominacion', 'empresa',
            'name', 'full_name', 'razon',
        ];
        foreach ($directCandidates as $f) {
            if (in_array($f, $cols, true)) {
                $val = $c->{$f} ?? null;
                if (is_string($val) && trim($val) !== '') return trim($val);
            }
        }

        $sets = [
            ['nombres', 'apellidos'],
            ['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido'],
        ];
        foreach ($sets as $set) {
            $parts = [];
            foreach ($set as $f) {
                if (in_array($f, $cols, true)) {
                    $v = $c->{$f} ?? null;
                    if (is_string($v) && trim($v) !== '') $parts[] = trim($v);
                }
            }
            if (!empty($parts)) return implode(' ', $parts);
        }

        foreach ($cols as $f) {
            if (preg_match('/(nombre|razon|cliente)/i', $f)) {
                $v = $c->{$f} ?? null;
                if (is_string($v) && trim($v) !== '') return trim($v);
            }
        }

        return 'Cliente #' . $c->id;
    }
}
