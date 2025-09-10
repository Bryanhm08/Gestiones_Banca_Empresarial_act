<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Credito;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Index', [
            'kpis' => [
                'usuarios' => User::count(),
                'asesores' => User::where('asesor', true)->count(),
                'clientes' => Cliente::count(),
                'cuentas' => Cuenta::count(),
                'creditos' => Credito::count(),
            ]
        ]);
    }
}
