<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }
        if (!$user->admin) {
            abort(403, 'Solo administradores.');
        }
        return $next($request);
    }
}
