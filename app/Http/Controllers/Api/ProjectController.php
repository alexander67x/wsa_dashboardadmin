<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionProyecto;
use App\Models\Proyecto;
use Illuminate\Support\Collection;

class ProjectController extends Controller
{
    public function index(): Collection
    {
        $projects = Proyecto::query()
            ->select(['cod_proy', 'nombre_ubicacion', 'cod_cliente', 'fecha_inicio', 'fecha_fin_estimada', 'created_at'])
            ->with(['cliente:cod_cliente,nombre_cliente'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Proyecto $project) {
                return [
                    'id' => (string) $project->getKey(),
                    'name' => $project->nombre_ubicacion,
                    'client' => $project->cliente?->nombre_cliente,
                    'startDate' => optional($project->fecha_inicio)->toDateString(),
                    'endDate' => optional($project->fecha_fin_estimada)->toDateString(),
                ];
            });

        return $projects->values();
    }

    public function show(string $id): array
    {
        $project = Proyecto::with([
                'cliente:cod_cliente,nombre_cliente',
                'tareas:id_tarea,cod_proy,titulo,estado',
            ])->findOrFail($id);

        return [
            'id' => (string) $project->getKey(),
            'name' => $project->nombre_ubicacion,
            'client' => $project->cliente?->nombre_cliente,
            'tasks' => $project->tareas
                ->map(function ($task) {
                    return [
                        'id' => (string) $task->id_tarea,
                        'title' => $task->titulo,
                        'status' => $task->estado ?? 'todo',
                    ];
                })
                ->values(),
        ];
    }

    public function team(string $id): Collection
    {
        return AsignacionProyecto::query()
            ->with(['empleado:cod_empleado,nombre_completo,cargo'])
            ->where('cod_proy', $id)
            ->get()
            ->filter(fn ($assignment) => $assignment->empleado)
            ->map(function ($assignment) {
                return [
                    'id' => (string) $assignment->empleado->cod_empleado,
                    'name' => $assignment->empleado->nombre_completo,
                    'role' => $assignment->rol_en_proyecto ?? 'worker',
                ];
            })
            ->values();
    }
}


