<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Controladores base
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReporteCreditoController;
use App\Http\Controllers\ReporteriaCuentaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\CuentaApiController;
use App\Http\Controllers\MisAsignacionesController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserAdminController;


// Reportes de créditos (admin, export a Excel)
use App\Http\Controllers\Reportes\CreditosReporteController;

// Calendario
use App\Http\Controllers\CalendarioController;

// ✅ NUEVO: Liberaciones
use App\Http\Controllers\LiberacionesController;

// routes/web.php


Route::get('/liberaciones/{lib}/export', [LiberacionesController::class, 'exportExcel'])
    ->name('liberaciones.export');


// Home -> Login (Inertia)
Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Módulos clientes/cuentas
Route::middleware(['auth'])->group(function () {
    Route::get('/obtener/clientes', [CuentaApiController::class, 'clientes']);
    Route::get('/clientes',          [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/asesores',          [CuentaApiController::class, 'asesores']);
    Route::get('/tipos-cuenta',      [CuentaApiController::class, 'tiposCuenta']);
    Route::get('/clientes/create',   [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes',         [ClienteController::class, 'store'])->name('clientes.store');

    Route::post('/cuentas',          [CuentaApiController::class, 'store'])->name('api.cuentas.store');
    Route::get('/cuentas',           [CuentaController::class, 'index'])->name('cuentas.index');
    Route::get('/cuentas/recent',    [CuentaApiController::class, 'recent']);
});

// 🗓️ Calendario (PROTEGIDO)
Route::middleware(['auth'])->group(function () {
    Route::get('/calendario',                 [CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('/calendario/events',          [CalendarioController::class, 'events'])->name('calendario.events');
    Route::post('/calendario/events',         [CalendarioController::class, 'store'])->name('calendario.events.store');
    Route::delete('/calendario/events/{id}',  [CalendarioController::class, 'destroy'])->name('calendario.events.destroy');

    // Reporte CSV (admin ve todo; asesor solo lo propio)
    Route::get('/calendario/report',          [CalendarioController::class, 'report'])->name('calendario.report');
});

// Créditos
Route::middleware(['auth'])->group(function () {
    Route::get('/creditos',             [CreditoController::class, 'index'])->name('creditos.index');
    Route::get('/creditos/{id}/edit',   [CreditoController::class, 'edit'])->name('creditos.edit');
    Route::get('/creditos/create',      [CreditoController::class, 'create'])->name('creditos.create');
    Route::post('/creditos',            [CreditoController::class, 'store'])->name('creditos.store');
    Route::patch('/creditos/{id}',      [CreditoController::class, 'update'])->name('creditos.update');
    Route::get('/creditos/{id}',        [CreditoController::class, 'show'])->name('creditos.show');

    // Etapas (estados)
    Route::post('/creditos/{id}/estado', [CreditoController::class, 'addEstado'])->name('creditos.estado.add');

    // Amortizaciones
    Route::post('/creditos/{id}/amortizaciones', [CreditoController::class, 'storeAmortizacion'])->name('creditos.amortizaciones.store');
    Route::patch('/amortizaciones/{id}/toggle',  [CreditoController::class, 'toggleAmortizacion'])->name('amortizaciones.toggle');
    Route::delete('/amortizaciones/{id}',        [CreditoController::class, 'destroyAmortizacion'])->name('amortizaciones.destroy');

    // Exportes de reportes existentes
    Route::post('/reports/export', [\App\Http\Controllers\ReportExportController::class, 'export'])->name('reports.export');
});

// Reportes existentes
Route::middleware(['auth'])->get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
Route::middleware(['auth'])->get('/MisAsignaciones', [MisAsignacionesController::class, 'index'])->name('mis.asignaciones');

// Ruta legada de reportes de créditos (NO se toca)
Route::middleware(['auth', 'module:credit_reports'])
    ->get('/reportes/creditos', [ReporteCreditoController::class, 'index'])
    ->name('reportes.creditos');

// Reportería de cuentas (NO se toca)
Route::middleware(['auth', 'module:accounts_reporting'])
    ->get('/reporteria/cuentas', [ReporteriaCuentaController::class, 'index'])
    ->name('reporteria.cuentas');

// Admin (NO se toca)
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('index');

        Route::get('/users',         [UserAdminController::class, 'index'])->name('users.index');
        Route::post('/users',        [UserAdminController::class, 'store'])->name('users.store');
        Route::post('/users/{user}', [UserAdminController::class, 'update'])->name('users.update');

        Route::patch('/users/{user}/toggle', [UserAdminController::class, 'toggleEstado'])->name('users.toggle');
        Route::patch('/users/{user}/roles',  [UserAdminController::class, 'updateRoles'])->name('users.roles');
    });

// NUEVO: Reportes de créditos (ADMIN) con filtro por etapa + export a Excel
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/reportes/creditos/admin',        [CreditosReporteController::class, 'index'])->name('reportes.creditos.index');
    Route::get('/reportes/creditos/admin/export', [CreditosReporteController::class, 'export'])->name('reportes.creditos.export');
});


// ✅✅ NUEVO MÓDULO: LIBERACIONES
Route::middleware(['auth'])->group(function () {

    // Listado principal (con botón "Agregar listado de liberaciones")
    Route::get('/liberaciones',                 [LiberacionesController::class, 'index'])->name('liberaciones.index');

    // Crear un nuevo "cuadro de liberación"
    Route::get('/liberaciones/create',          [LiberacionesController::class, 'create'])->name('liberaciones.create');
    Route::post('/liberaciones',                [LiberacionesController::class, 'store'])->name('liberaciones.store');

    // Administrar (ver la tabla, filtrar, cambiar estatus, exportar)
    Route::get('/liberaciones/{lib}',           [LiberacionesController::class, 'show'])->name('liberaciones.show');
    Route::patch('/liberaciones/{lib}/rows/{rowId}/status',      [LiberacionesController::class, 'updateRowStatus'])->name('liberaciones.rows.status');
    Route::get('/liberaciones/{lib}/export',    [LiberacionesController::class, 'exportExcel'])->name('liberaciones.export');

    // Editar estructura (agregar/eliminar filas/columnas con confirmación)
    Route::get('/liberaciones/{lib}/edit',      [LiberacionesController::class, 'edit'])->name('liberaciones.edit');
    Route::patch('/liberaciones/{lib}',         [LiberacionesController::class, 'updateStructure'])->name('liberaciones.update');

    // Acciones puntuales para filas/columnas
    Route::post('/liberaciones/{lib}/rows',     [LiberacionesController::class, 'addRow'])->name('liberaciones.rows.add');
    Route::delete('/liberaciones/{lib}/rows/{rowId}', [LiberacionesController::class, 'removeRow'])->name('liberaciones.rows.remove');
    Route::post('/liberaciones/{lib}/columns',  [LiberacionesController::class, 'addColumn'])->name('liberaciones.columns.add');
    Route::delete('/liberaciones/{lib}/columns/{colId}', [LiberacionesController::class, 'removeColumn'])->name('liberaciones.columns.remove');
});

require __DIR__ . '/auth.php';


Route::middleware(['auth','verified'])->group(function () {
    Route::get('/liberaciones',                 [LiberacionesController::class,'index'])->name('liberaciones.index');
    Route::get('/liberaciones/create',          [LiberacionesController::class,'create'])->name('liberaciones.create');
    Route::post('/liberaciones',                [LiberacionesController::class,'store'])->name('liberaciones.store');

    Route::get('/liberaciones/{lib}',           [LiberacionesController::class,'show'])->name('liberaciones.show');
    Route::patch('/liberaciones/{lib}/rows/{rowId}/status', [LiberacionesController::class,'updateRowStatus'])->name('liberaciones.rows.status');

    Route::get('/liberaciones/{lib}/edit',      [LiberacionesController::class,'edit'])->name('liberaciones.edit');
    Route::patch('/liberaciones/{lib}',         [LiberacionesController::class,'updateStructure'])->name('liberaciones.update');

    Route::post('/liberaciones/{lib}/rows',     [LiberacionesController::class,'addRow'])->name('liberaciones.rows.add');
    Route::delete('/liberaciones/{lib}/rows/{rowId}', [LiberacionesController::class,'removeRow'])->name('liberaciones.rows.remove');
    Route::post('/liberaciones/{lib}/columns',  [LiberacionesController::class,'addColumn'])->name('liberaciones.columns.add');
    Route::delete('/liberaciones/{lib}/columns/{colId}', [LiberacionesController::class,'removeColumn'])->name('liberaciones.columns.remove');

    Route::get('/liberaciones/{lib}/export',    [LiberacionesController::class,'exportExcel'])->name('liberaciones.export');
});
