<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $user = $request->user();

        if (! $user || ! $user->empleado) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (empty($permissions)) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        logger()->warning('Permission denied', [
            'user_id' => $user?->id,
            'route' => $request->path(),
            'required_permissions' => $permissions,
        ]);

        return response()->json([
            'message' => 'No cuentas con los privilegios necesarios para esta acción.',
        ], 403);
    }
}
