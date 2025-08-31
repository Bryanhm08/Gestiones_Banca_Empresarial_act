<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ReporteCreditoController extends Controller
{
    public function index()
    {
        return Inertia::render('Reportes/Creditos', [
        ]);
    }
}
