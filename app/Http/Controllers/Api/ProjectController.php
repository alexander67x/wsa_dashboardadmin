<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionProyecto;
use App\Models\Proyecto;
use App\Models\Tarea;
use App\Services\ProjectAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProjectController extends Controller
{
    public function index(Request $request): Collection
    {
        $allowed = ProjectAccessService::allowedProjectIds($request->user());
        if ($allowed === []) {
            return collect();
        }

        $query = Proyecto::query()
            ->select(['cod_proy', 'nombre_ubicacion', 'cod_cliente', 'fecha_inicio', 'fecha_fin_estimada', 'avance_financiero', 'created_at'])
            ->with(['cliente:cod_cliente,nombre_cliente', 'empleados:cod_empleado,nombre_completo'])
            ->orderByDesc('created_at');

        if (is_array($allowed)) {
            $query->whereIn('cod_proy', $allowed);
        }

        return $query->get()
            ->map(function (Proyecto $project) {
                return [
                    'id' => (string) $project->getKey(),
                    'name' => $project->nombre_ubicacion,
                    'client' => $project->cliente?->nombre_cliente,
                    'startDate' => optional($project->fecha_inicio)->toDateString(),
                    // Fecha límite del proyecto
                    'endDate' => optional($project->fecha_fin_estimada)->toDateString(),
                    'deadline' => optional($project->fecha_fin_estimada)->toDateString(),
                    // Presupuesto / avance financiero
                    'budget' => $project->avance_financiero !== null
                        ? (float) $project->avance_financiero
                        : null,
                    // Miembros del proyecto (equipo asignado)
                    'members' => $project->empleados
                        ->map(fn ($empleado) => [
                            'id' => (string) $empleado->cod_empleado,
                            'name' => $empleado->nombre_completo,
                        ])
                        ->values(),
                ];
            })
            ->values();
    }

    public function show(Request $request, string $id): array
    {
        ProjectAccessService::ensureCanAccess($request->user(), $id);

        $project = Proyecto::with([
            'cliente:cod_cliente,nombre_cliente',
            'tareas' => function ($query) {
                $query->select([
                    'id_tarea',
                    'cod_proy',
                    'titulo',
                    'descripcion',
                    'fecha_inicio',
                    'fecha_fin',
                    'estado',
                    'created_at',
                    'updated_at',
                    'supervisor_asignado',
                ])->with([
                    'responsables:cod_empleado,nombre_completo,cargo',
                    'supervisor:cod_empleado,nombre_completo',
                ]);
            },
            'empleados:cod_empleado,nombre_completo,cargo',
        ])->findOrFail($id);

        return [
            'id' => (string) $project->getKey(),
            'name' => $project->nombre_ubicacion,
            'client' => $project->cliente?->nombre_cliente,
            'startDate' => optional($project->fecha_inicio)->toDateString(),
            'endDate' => optional($project->fecha_fin_estimada)->toDateString(),
            'deadline' => optional($project->fecha_fin_estimada)->toDateString(),
            'budget' => $project->avance_financiero !== null
                ? (float) $project->avance_financiero
                : null,
            'members' => $project->empleados
                ->map(fn ($empleado) => [
                    'id' => (string) $empleado->cod_empleado,
                    'name' => $empleado->nombre_completo,
                    'role' => $empleado->pivot->rol_en_proyecto ?? null,
                ])
                ->values(),
            'tasks' => $project->tareas
                ->map(function ($task) {
                    $responsibles = $task->responsables
                        ->map(fn ($empleado) => [
                            'id' => (string) $empleado->cod_empleado,
                            'name' => $empleado->nombre_completo,
                            'position' => $empleado->cargo,
                        ])
                        ->values()
                        ->all();
                    $primaryResponsible = $responsibles[0] ?? null;
                    $responsableNombre = $primaryResponsible['name'] ?? $task->supervisor?->nombre_completo;

                    return [
                        // IDs
                        'id' => (string) $task->id_tarea,
                        'tareaId' => (string) $task->id_tarea,
                        // Título
                        'title' => $task->titulo,
                        'titulo' => $task->titulo,
                        // Descripción
                        'description' => $task->descripcion,
                        'descripcion' => $task->descripcion,
                        // Responsable / asignado
                        'responsable' => $responsableNombre,
                        'responsable_nombre' => $responsableNombre,
                        'responsableName' => $responsableNombre,
                        'encargado' => $responsableNombre,
                        'owner' => $responsableNombre,
                        'responsibleId' => $primaryResponsible['id'] ?? null,
                        'responsibleName' => $primaryResponsible['name'] ?? null,
                        'responsibleIds' => collect($responsibles)->pluck('id')->all(),
                        'responsibles' => $responsibles,
                        'responsible' => $primaryResponsible,
                        'assignee' => $primaryResponsible['name'] ?? null,
                        'assigneeIds' => collect($responsibles)->pluck('id')->all(),
                        'assignees' => collect($responsibles)->pluck('name')->all(),
                        // Fechas
                        'startDate' => optional($task->fecha_inicio)->toDateString(),
                        'fechaInicio' => optional($task->fecha_inicio)->toDateString(),
                        'fecha_inicio' => optional($task->fecha_inicio)->toDateString(),
                        'dueDate' => optional($task->fecha_fin)->toDateString(),
                        'fechaLimite' => optional($task->fecha_fin)->toDateString(),
                        'fecha_limite' => optional($task->fecha_fin)->toDateString(),
                        'deadline' => optional($task->fecha_fin)->toDateString(),
                        'fechaVencimiento' => optional($task->fecha_fin)->toDateString(),
                        'endDate' => optional($task->fecha_fin)->toDateString(),
                        'fechaFin' => optional($task->fecha_fin)->toDateString(),
                        'fecha_fin' => optional($task->fecha_fin)->toDateString(),
                        'createdAt' => optional($task->created_at)->toDateTimeString(),
                        'created_at' => optional($task->created_at)->toDateTimeString(),
                        'fechaCreacion' => optional($task->created_at)->toDateTimeString(),
                        'updatedAt' => optional($task->updated_at)->toDateTimeString(),
                        'updated_at' => optional($task->updated_at)->toDateTimeString(),
                        'fechaActualizacion' => optional($task->updated_at)->toDateTimeString(),
                        // Estado
                        'status' => $task->estado ?? 'todo',
                    ];
                })
                ->values(),
        ];
    }

    public function team(Request $request, string $id): Collection
    {
        ProjectAccessService::ensureCanAccess($request->user(), $id);

        return AsignacionProyecto::query()
            ->with(['empleado:cod_empleado,nombre_completo,cargo'])
            ->where('cod_proy', $id)
            ->get()
            ->filter(fn ($assignment) => $assignment->empleado)
            ->map(fn ($assignment) => [
                'id' => (string) $assignment->empleado->cod_empleado,
                'name' => $assignment->empleado->nombre_completo,
                'role' => $assignment->rol_en_proyecto ?? 'worker',
            ])
            ->values();
    }

    public function stock(Request $request, string $id): array
    {
        ProjectAccessService::ensureCanAccess($request->user(), $id);

        $project = Proyecto::findOrFail($id);

        $almacen = \App\Models\Almacen::where('cod_proy', $id)
            ->where('activo', true)
            ->first();

        if (! $almacen) {
            return [
                'warehouse' => null,
                'materials' => [],
                'message' => 'No se encontró un almacén activo para este proyecto',
            ];
        }

        $stock = \App\Models\StockAlmacen::with([
            'material:id_material,codigo_producto,nombre_producto,unidad_medida,costo_unitario_promedio_bs,activo',
        ])
            ->where('id_almacen', $almacen->id_almacen)
            ->whereHas('material', fn ($query) => $query->where('activo', true))
            ->get()
            ->filter(fn ($item) => $item->material)
            ->map(function ($item) {
                $material = $item->material;

                return [
                    'id' => (string) $item->id,
                    'materialId' => (string) $material->id_material,
                    'code' => $material->codigo_producto,
                    'name' => $material->nombre_producto,
                    'unit' => $material->unidad_medida,
                    'available' => (float) $item->cantidad_disponible,
                    'reserved' => (float) $item->cantidad_reservada,
                    'availableReal' => (float) ($item->cantidad_disponible - $item->cantidad_reservada),
                    'minAlert' => (float) $item->cantidad_minima_alerta,
                    'location' => $item->ubicacion_fisica,
                    'unitPrice' => (float) $material->costo_unitario_promedio_bs,
                    'needsRestock' => $item->cantidad_disponible <= $item->cantidad_minima_alerta,
                ];
            })
            ->values();

        return [
            'warehouse' => [
                'id' => (string) $almacen->id_almacen,
                'code' => $almacen->codigo_almacen,
                'name' => $almacen->nombre,
                'address' => $almacen->direccion,
                'city' => $almacen->ciudad,
            ],
            'materials' => $stock,
            'totalMaterials' => $stock->count(),
        ];
    }

    public function myProjects(Request $request): Collection
    {
        $allowed = ProjectAccessService::allowedProjectIds($request->user());
        if ($allowed === []) {
            return collect();
        }

        $query = Proyecto::query()
            ->with(['cliente:cod_cliente,nombre_cliente', 'empleados:cod_empleado,nombre_completo'])
            ->select(['cod_proy', 'nombre_ubicacion', 'cod_cliente', 'fecha_inicio', 'fecha_fin_estimada', 'avance_financiero', 'created_at'])
            ->orderByDesc('created_at');

        if (is_array($allowed)) {
            $query->whereIn('cod_proy', $allowed);
        }

        return $query->get()
            ->map(function (Proyecto $project) {
                return [
                    'id' => (string) $project->getKey(),
                    'name' => $project->nombre_ubicacion,
                    'client' => $project->cliente?->nombre_cliente,
                    'startDate' => optional($project->fecha_inicio)->toDateString(),
                    'endDate' => optional($project->fecha_fin_estimada)->toDateString(),
                    'deadline' => optional($project->fecha_fin_estimada)->toDateString(),
                    'budget' => $project->avance_financiero !== null
                        ? (float) $project->avance_financiero
                        : null,
                    'members' => $project->empleados
                        ->map(fn ($empleado) => [
                            'id' => (string) $empleado->cod_empleado,
                            'name' => $empleado->nombre_completo,
                        ])
                        ->values(),
                ];
            })
            ->values();
    }

    public function taskResponsibles(Request $request): Collection
    {
        $allowed = ProjectAccessService::allowedProjectIds($request->user());
        if ($allowed === []) {
            return collect();
        }

        $projectIds = $allowed;

        if ($projectIds === null) {
            $projectIds = Proyecto::pluck('cod_proy')->all();
        }

        if (empty($projectIds)) {
            return collect([]);
        }

        $empleados = AsignacionProyecto::query()
            ->with(['empleado:cod_empleado,nombre_completo,cargo'])
            ->whereIn('cod_proy', $projectIds)
            ->where('estado', 'activo')
            ->get()
            ->filter(fn ($assignment) => $assignment->empleado)
            ->map(function ($assignment) {
                return [
                    'id' => (string) $assignment->empleado->cod_empleado,
                    'name' => $assignment->empleado->nombre_completo,
                    'role' => $assignment->rol_en_proyecto ?? 'worker',
                    'projectId' => $assignment->cod_proy,
                ];
            })
            ->values();

        return $empleados;
    }
}
