<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs excluidas de verificación CSRF (por defecto ninguna).
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     *
     * En entorno LOCAL se omite la verificación CSRF para evitar 419
     * mientras desarrollás y probás el sistema.
     */
    public function handle($request, Closure $next)
    {
        if (app()->environment('local')) {
            // 🔓 En local: NO validar CSRF (solo para desarrollo)
            return $next($request);
        }

        // En otros entornos (staging / producción): comportamiento normal
        return parent::handle($request, $next);
    }
}
