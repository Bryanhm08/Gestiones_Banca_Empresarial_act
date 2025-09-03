<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class ClienteController extends Controller
{
    public function index()
    {
        return Inertia::render('Clientes/Index');
    }

    public function create()
    {
        $categorias = DB::table('categorias')->select('id', 'nombre')->orderBy('nombre')->get();
        $asesores = DB::table('users')
            ->select('id', 'name')
            ->where('estado', 1)
            ->where('asesor', 1)
            ->orderBy('name')
            ->get();

        return Inertia::render('Clientes/Create', [
            'categorias' => $categorias,
            'asesores' => $asesores,
        ]);
    }

    public function store(StoreClienteRequest $request): RedirectResponse
    {
        DB::table('clientes')->insert([
            'nombre_cliente' => $request->nombre_cliente,
            'categoria_id' => $request->categoria_id,
            'nit' => $request->nit,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'asesor_id' => $request->asesor_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente creado con éxito.');
    }
}
