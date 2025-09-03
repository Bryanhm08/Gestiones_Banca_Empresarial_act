<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReporteCreditoController;
use App\Http\Controllers\ReporteriaCuentaController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CuentaApiController;
use App\Http\Controllers\MisAsignacionesController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'no-asesor-clientes'])->group(function () {
    Route::get('/obtener/clientes', [CuentaApiController::class, 'clientes']);
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/asesores', [CuentaApiController::class, 'asesores']);
    Route::get('/tipos-cuenta', [CuentaApiController::class, 'tiposCuenta']);
    Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::post('/cuentas', [CuentaApiController::class, 'store'])->name('api.cuentas.store');
    Route::get('/cuentas', [CuentaController::class, 'index'])->name('cuentas.index');
    Route::get('/cuentas/recent', [CuentaApiController::class, 'recent']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/creditos', [CreditoController::class, 'index'])->name('creditos.index');
    Route::get('/creditos/{id}/edit', [CreditoController::class, 'edit'])->name('creditos.edit');
    Route::get('/creditos/create', [CreditoController::class, 'create'])->name('creditos.create');
    Route::post('/creditos', [CreditoController::class, 'store'])->name('creditos.store');
    Route::patch('/creditos/{id}', [CreditoController::class, 'update'])->name('creditos.update');
    Route::get('/creditos/{id}', [CreditoController::class, 'show'])->name('creditos.show');
    Route::post('/creditos/{id}/estado', [CreditoController::class, 'addEstado'])->name('creditos.estado.add');
    Route::post('/creditos/{id}/amortizaciones', [CreditoController::class, 'storeAmortizacion'])->name('creditos.amortizaciones.store');
    Route::patch('/amortizaciones/{id}/toggle', [CreditoController::class, 'toggleAmortizacion'])->name('amortizaciones.toggle');
    Route::delete('/amortizaciones/{id}', [CreditoController::class, 'destroyAmortizacion'])->name('amortizaciones.destroy');
    Route::post('/reports/export', [\App\Http\Controllers\ReportExportController::class, 'export'])->name('reports.export');
});

Route::middleware(['auth'])->get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
Route::middleware(['auth'])->get('/MisAsignaciones', [MisAsignacionesController::class, 'index'])->name('mis.asignaciones');


Route::middleware(['auth', 'module:credit_reports'])
    ->get('/reportes/creditos', [ReporteCreditoController::class, 'index'])
    ->name('reportes.creditos');

Route::middleware(['auth', 'module:accounts_reporting'])
    ->get('/reporteria/cuentas', [ReporteriaCuentaController::class, 'index'])
    ->name('reporteria.cuentas');

require __DIR__ . '/auth.php';
