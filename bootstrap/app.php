<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 💡 Dejamos el stack "web" como viene por defecto
        // (incluye CSRF, sesiones, cookies, etc.)

        // Middleware extra para Inertia + preload de assets
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Alias de middlewares que usas en rutas
        $middleware->alias([
            'module'             => \App\Http\Middleware\CheckModuleAccess::class,
            'no-asesor-clientes' => \App\Http\Middleware\ForbidAsesorOnClientes::class,
            'admin'              => \App\Http\Middleware\AdminOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
