<?php
namespace App\Http\Controllers;
use Inertia\Inertia;

class CuentaController extends Controller
{
    public function index() { return Inertia::render('Cuentas/Index'); }
}
