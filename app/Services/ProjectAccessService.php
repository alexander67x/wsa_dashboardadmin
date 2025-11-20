<?php

namespace App\Services;

use App\Models\AsignacionProyecto;
use App\Models\Proyecto;
use App\Models\User;

class ProjectAccessService
{
    /**
     * @return array<string>|null Returns null when user can access all projects, array when limited, empty array for none.
     */
    public static function allowedProjectIds(?User $user): ?array
    {
        if (! $user || ! $user->empleado) {
            return [];
        }

        if ($user->hasPermission('dashboard.projects.overview')) {
            return null;
        }

        $empleado = $user->empleado;
        $codEmpleado = $empleado->cod_empleado;

        $ids = Proyecto::query()
            ->where('responsable_proyecto', $codEmpleado)
            ->orWhere('supervisor_obra', $codEmpleado)
            ->pluck('cod_proy');

        $assignments = AsignacionProyecto::where('cod_empleado', $codEmpleado)
            ->where('estado', 'activo')
            ->pluck('cod_proy');

        $merged = $ids->merge($assignments)->unique()->values()->all();

        return $merged;
    }

    public static function ensureCanAccess(?User $user, string $projectId): void
    {
        $allowed = self::allowedProjectIds($user);

        if ($allowed === null) {
            return;
        }

        if (in_array($projectId, $allowed, true)) {
            return;
        }

        abort(403, 'No tienes acceso a este proyecto.');
    }
}
