<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotSupervisor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->empleado?->role?->slug === 'supervisor') {
            abort(403, 'No autorizado.');
        }

        return $next($request);
    }
}
