<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReporteCreditoController;
use App\Http\Controllers\ReporteriaCuentaController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard: todos los autenticados
Route::get('/dashboard', fn() => Inertia::render('Dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Perfil (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ========= PRINCIPALES =========

// Clientes (bloqueado para asesores; admin pasa)
Route::middleware(['auth', 'no-asesor-clientes'])->group(function () {
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create'); // 👈 formulario
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');        // 👈 guardar
});

// Cuentas (acceso general autenticado)
Route::middleware(['auth'])->get('/cuentas', [CuentaController::class, 'index'])->name('cuentas.index');

// Créditos (acceso general autenticado)
Route::middleware(['auth'])->get('/creditos', [CreditoController::class, 'index'])->name('creditos.index');

// Reportes (hub) — autenticado; los sub-reportes ya tienen module:*
Route::middleware(['auth'])->get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

// ========= SUB-REPORTES PROTEGIDOS POR ÁREA =========

// Reportes de créditos → requiere módulo "Reportes de créditos"
Route::middleware(['auth', 'module:credit_reports'])
    ->get('/reportes/creditos', [ReporteCreditoController::class, 'index'])
    ->name('reportes.creditos');

// Reportería de cuentas → requiere módulo "Reportería de cuentas"
Route::middleware(['auth', 'module:accounts_reporting'])
    ->get('/reporteria/cuentas', [ReporteriaCuentaController::class, 'index'])
    ->name('reporteria.cuentas');

require __DIR__ . '/auth.php';
