<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Categoria; // ajusta si tu modelo está en otro namespace
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    /**
     * Listado de clientes (asesor ve sólo los suyos; incluye búsqueda ?q=)
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        $query = Cliente::query()
            ->with(['asesor', 'categoria'])
            ->latest();

        // Búsqueda rápida
        $term = trim((string) $request->query('q', ''));
        if ($term !== '') {
            $query->search($term);
        }

        // Si es asesor: sólo sus clientes
        if ($user?->asesor) {
            $query->byAsesor($user->id);
        }

        $clientes = $query->paginate(12)->withQueryString();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filters'  => ['q' => $term],
        ]);
    }

    /**
     * Formulario de creación (sólo categorías; SIN asesores)
     */
    public function create(): Response
    {
        $categorias = Categoria::select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Clientes/Create', [
            'categorias' => $categorias,
        ]);
    }

    /**
     * Guarda un cliente nuevo.
     * 🔒 Siempre se asigna asesor_id = auth()->id() (no se envía desde el front).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nombre_cliente'   => ['required', 'string', 'max:255'],
            'categoria_id'     => ['required', 'exists:categorias,id'], // ajusta si tu tabla se llama distinto
            'nit'              => ['required', 'string', 'max:32'],
            'telefono'         => ['required', 'string', 'max:50'],
            'fecha_nacimiento' => ['required', 'date'],
            'email'            => ['nullable', 'email', 'max:255'],
            // 👇 ya no validamos asesor_id
        ]);

        Cliente::create([
            'nombre_cliente'   => $validated['nombre_cliente'],
            'categoria_id'     => $validated['categoria_id'],
            'nit'              => $validated['nit'],
            'telefono'         => $validated['telefono'],
            'email'            => $validated['email'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'],
            'asesor_id'        => $user->id, // ← autoasignado
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente creado con éxito.');
    }
}
