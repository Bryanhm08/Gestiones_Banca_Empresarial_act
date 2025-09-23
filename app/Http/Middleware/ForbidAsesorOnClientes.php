<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForbidAsesorOnClientes
{
    public function handle(Request $request, Closure $next)
    {
        // Antes bloqueaba a asesores. Ahora deja pasar a todos los autenticados.
        // (Si aún está registrado en Kernel, no romperá nada)
        return $next($request);
    }
}
