<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionProyecto;
use App\Models\Proyecto;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Obtiene las tareas de los proyectos asignados al usuario autenticado.
     */
    public function index(Request $request): Collection
    {
        $user = $request->user();
        $empleado = $user->empleado;

        if (!$empleado) {
            return collect([]);
        }

        $codEmpleado = $empleado->cod_empleado;

        // Obtener IDs de proyectos donde el empleado está relacionado
        $proyectosAsignadosIds = AsignacionProyecto::where('cod_empleado', $codEmpleado)
            ->where('estado', 'activo')
            ->pluck('cod_proy')
            ->toArray();

        $proyectosIds = Proyecto::query()
            ->where(function ($query) use ($codEmpleado, $proyectosAsignadosIds) {
                $query->where('responsable_proyecto', $codEmpleado)
                    ->orWhere('supervisor_obra', $codEmpleado)
                    ->orWhereIn('cod_proy', $proyectosAsignadosIds);
            })
            ->pluck('cod_proy')
            ->toArray();

        if (empty($proyectosIds)) {
            return collect([]);
        }

        // Obtener tareas de esos proyectos
        $tareas = Tarea::with(['proyecto:cod_proy,nombre_ubicacion', 'responsables:cod_empleado,nombre_completo,cargo'])
            ->whereIn('cod_proy', $proyectosIds)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Tarea $tarea) {
                $responsibles = $this->serializeResponsables($tarea);
                $primaryResponsible = $responsibles[0] ?? null;

                return [
                    'id' => (string) $tarea->id_tarea,
                    'title' => $tarea->titulo,
                    'description' => $tarea->descripcion,
                    'projectId' => $tarea->cod_proy,
                    'projectName' => $tarea->proyecto?->nombre_ubicacion,
                    'status' => $tarea->estado ?? 'pendiente',
                    'priority' => $tarea->prioridad ?? 'media',
                    'startDate' => optional($tarea->fecha_inicio)->toDateString(),
                    'endDate' => optional($tarea->fecha_fin)->toDateString(),
                    'responsibleId' => $primaryResponsible['id'] ?? null,
                    'responsibleName' => $primaryResponsible['name'] ?? null,
                    'responsibleIds' => collect($responsibles)->pluck('id')->all(),
                    'responsibles' => $responsibles,
                    'responsible' => $primaryResponsible,
                    'assignee' => $primaryResponsible['name'] ?? null,
                    'assigneeIds' => collect($responsibles)->pluck('id')->all(),
                    'assignees' => collect($responsibles)->pluck('name')->all(),
                    'createdAt' => optional($tarea->created_at)->toDateTimeString(),
                ];
            });

        return $tareas->values();
    }

    /**
     * Obtiene los detalles de una tarea específica.
     */
    public function show(Request $request, int|string $id): array
    {
        $user = $request->user();
        $empleado = $user->empleado;

        if (!$empleado) {
            abort(404, 'No se encontró un empleado asociado al usuario');
        }

        $tarea = Tarea::with([
            'proyecto:cod_proy,nombre_ubicacion',
            'responsables:cod_empleado,nombre_completo,cargo',
            'supervisor:cod_empleado,nombre_completo,cargo',
        ])->findOrFail($id);

        // Verificar que el usuario tenga acceso a este proyecto
        $codEmpleado = $empleado->cod_empleado;
        $proyectosAsignadosIds = AsignacionProyecto::where('cod_empleado', $codEmpleado)
            ->where('estado', 'activo')
            ->pluck('cod_proy')
            ->toArray();

        $tieneAcceso = Proyecto::query()
            ->where('cod_proy', $tarea->cod_proy)
            ->where(function ($query) use ($codEmpleado, $proyectosAsignadosIds) {
                $query->where('responsable_proyecto', $codEmpleado)
                    ->orWhere('supervisor_obra', $codEmpleado)
                    ->orWhereIn('cod_proy', $proyectosAsignadosIds);
            })
            ->exists();

        if (!$tieneAcceso) {
            abort(403, 'No tienes acceso a esta tarea');
        }

        $responsibles = $this->serializeResponsables($tarea);
        $primaryResponsible = $responsibles[0] ?? null;

        return [
            'id' => (string) $tarea->id_tarea,
            'title' => $tarea->titulo,
            'description' => $tarea->descripcion,
            'projectId' => $tarea->cod_proy,
            'projectName' => $tarea->proyecto?->nombre_ubicacion,
            'status' => $tarea->estado ?? 'pendiente',
            'priority' => $tarea->prioridad ?? 'media',
            'startDate' => optional($tarea->fecha_inicio)->toDateString(),
            'endDate' => optional($tarea->fecha_fin)->toDateString(),
            'duration' => $tarea->duracion_dias,
            'responsibleId' => $primaryResponsible['id'] ?? null,
            'responsibleName' => $primaryResponsible['name'] ?? null,
            'responsibleIds' => collect($responsibles)->pluck('id')->all(),
            'responsibles' => $responsibles,
            'responsible' => $primaryResponsible,
            'assignee' => $primaryResponsible['name'] ?? null,
            'assigneeIds' => collect($responsibles)->pluck('id')->all(),
            'assignees' => collect($responsibles)->pluck('name')->all(),
            'supervisorId' => $tarea->supervisor_asignado ? (string) $tarea->supervisor_asignado : null,
            'supervisor' => $tarea->supervisor ? [
                'id' => (string) $tarea->supervisor->cod_empleado,
                'name' => $tarea->supervisor->nombre_completo,
                'position' => $tarea->supervisor->cargo,
            ] : null,
            'createdAt' => optional($tarea->created_at)->toDateTimeString(),
            'updatedAt' => optional($tarea->updated_at)->toDateTimeString(),
        ];
    }

    /**
     * Permite al usuario autenticado autoasignarse una tarea.
     * Solo puede asignarse tareas de proyectos donde está asignado.
     */
    public function assignToMe(Request $request, int|string $id)
    {
        $user = $request->user();
        $empleado = $user->empleado;

        if (!$empleado) {
            return response()->json([
                'message' => 'No se encontró un empleado asociado al usuario'
            ], 404);
        }

        $tarea = Tarea::with('proyecto')->findOrFail($id);

        // Verificar que el usuario tenga acceso a este proyecto
        $codEmpleado = $empleado->cod_empleado;
        $proyectosAsignadosIds = AsignacionProyecto::where('cod_empleado', $codEmpleado)
            ->where('estado', 'activo')
            ->pluck('cod_proy')
            ->toArray();

        $tieneAcceso = Proyecto::query()
            ->where('cod_proy', $tarea->cod_proy)
            ->where(function ($query) use ($codEmpleado, $proyectosAsignadosIds) {
                $query->where('responsable_proyecto', $codEmpleado)
                    ->orWhere('supervisor_obra', $codEmpleado)
                    ->orWhereIn('cod_proy', $proyectosAsignadosIds);
            })
            ->exists();

        if (!$tieneAcceso) {
            return response()->json([
                'message' => 'No tienes acceso a esta tarea. Debes estar asignado al proyecto para poder asignarte la tarea.'
            ], 403);
        }

        // Verificar que el empleado esté en la lista de responsables disponibles
        $proyectosIds = Proyecto::query()
            ->where(function ($query) use ($codEmpleado, $proyectosAsignadosIds) {
                $query->where('responsable_proyecto', $codEmpleado)
                    ->orWhere('supervisor_obra', $codEmpleado)
                    ->orWhereIn('cod_proy', $proyectosAsignadosIds);
            })
            ->pluck('cod_proy')
            ->toArray();

        $empleadosAsignadosIds = AsignacionProyecto::whereIn('cod_proy', $proyectosIds)
            ->where('estado', 'activo')
            ->pluck('cod_empleado')
            ->unique()
            ->toArray();

        $responsablesProyectoIds = Proyecto::whereIn('cod_proy', $proyectosIds)
            ->whereNotNull('responsable_proyecto')
            ->pluck('responsable_proyecto')
            ->unique()
            ->toArray();

        $supervisoresProyectoIds = Proyecto::whereIn('cod_proy', $proyectosIds)
            ->whereNotNull('supervisor_obra')
            ->pluck('supervisor_obra')
            ->unique()
            ->toArray();

        $responsablesDisponibles = collect([
            ...$empleadosAsignadosIds,
            ...$responsablesProyectoIds,
            ...$supervisoresProyectoIds,
        ])->unique()->toArray();

        if (!in_array($codEmpleado, $responsablesDisponibles)) {
            return response()->json([
                'message' => 'No puedes asignarte esta tarea. Debes ser miembro del equipo del proyecto.'
            ], 403);
        }

        // Actualizar la tarea asignándola al usuario
        $tarea->responsables()->syncWithoutDetaching([$codEmpleado]);
        
        // Si la tarea estaba pendiente, cambiar a en_proceso al autoasignarse
        if ($tarea->estado === 'pendiente' || !$tarea->estado) {
            $tarea->estado = 'en_proceso';
        }
        
        $tarea->save();
        $tarea->load(['responsables:cod_empleado,nombre_completo,cargo']);

        $responsibles = $this->serializeResponsables($tarea);

        return response()->json([
            'message' => 'Tarea asignada exitosamente',
            'task' => [
                'id' => (string) $tarea->id_tarea,
                'title' => $tarea->titulo,
                'status' => $tarea->estado,
                'responsibleId' => $responsibles[0]['id'] ?? null,
                'responsibleName' => $responsibles[0]['name'] ?? null,
                'responsibleIds' => collect($responsibles)->pluck('id')->all(),
                'responsibles' => $responsibles,
                'responsible' => $responsibles[0] ?? null,
                'assignee' => $responsibles[0]['name'] ?? null,
                'assigneeIds' => collect($responsibles)->pluck('id')->all(),
                'assignees' => collect($responsibles)->pluck('name')->all(),
            ]
        ], 200);
    }

    protected function serializeResponsables(Tarea $tarea): array
    {
        return $tarea->responsables
            ->map(fn ($empleado) => [
                'id' => (string) $empleado->cod_empleado,
                'name' => $empleado->nombre_completo,
                'position' => $empleado->cargo,
            ])
            ->values()
            ->all();
    }
}
