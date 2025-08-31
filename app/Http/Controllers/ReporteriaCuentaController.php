<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ReporteriaCuentaController extends Controller
{
    public function index()
    {
        return Inertia::render('Reporteria/Cuentas', [
        ]);
    }
}
