<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $moduleSlug)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }
        if ($user->admin) {
            return $next($request);
        }
        if (!$user->canAccessModule($moduleSlug)) {
            abort(403, 'No tenés acceso a este módulo.');
        }

        return $next($request);
    }
}
