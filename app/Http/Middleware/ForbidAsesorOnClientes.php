<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForbidAsesorOnClientes
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }
        if ($user->admin) {
            return $next($request);
        }
        if ($user->asesor === true) {
            abort(403, 'Acceso restringido para asesores.');
        }

        return $next($request);
    }
}
