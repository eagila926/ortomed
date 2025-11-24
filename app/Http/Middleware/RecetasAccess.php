<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RecetasAccess
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->rol, ['Admin','Laboratorio'], true)) {
            abort(403, 'No tienes permisos para acceder a Recetas.');
        }

        return $next($request);
    }
}
