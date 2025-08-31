<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
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

// Dashboard: todos los autenticados (y verificados si usas verificación)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Perfil (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ================== MÓDULOS ================== //

// Clientes -> bloqueado para Asesores (a menos que sea admin)
Route::middleware(['auth', 'no-asesor-clientes'])->group(function () {
    // Si aún no querés todo el CRUD, dejá al menos index
    Route::resource('clientes', ClienteController::class)->only(['index']);
});

// Reportes de créditos (área debe tener "Reportes de créditos")
Route::middleware(['auth', 'module:credit_reports'])
    ->get('/reportes/creditos', [ReporteCreditoController::class, 'index'])
    ->name('reportes.creditos');

// Reportería de cuentas (área debe tener "Reportería de cuentas")
Route::middleware(['auth', 'module:accounts_reporting'])
    ->get('/reporteria/cuentas', [ReporteriaCuentaController::class, 'index'])
    ->name('reporteria.cuentas');

require __DIR__.'/auth.php';
