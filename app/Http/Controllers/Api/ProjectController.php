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
            ->select(['cod_proy', 'nombre_ubicacion', 'cod_cliente', 'fecha_inicio', 'fecha_fin_estimada', 'created_at'])
            ->with(['cliente:cod_cliente,nombre_cliente'])
            ->orderByDesc('created_at');

        if (is_array($allowed)) {
            $query->whereIn('cod_proy', $allowed);
        }

        return $query->get()
            ->map(fn (Proyecto $project) => [
                'id' => (string) $project->getKey(),
                'name' => $project->nombre_ubicacion,
                'client' => $project->cliente?->nombre_cliente,
                'startDate' => optional($project->fecha_inicio)->toDateString(),
                'endDate' => optional($project->fecha_fin_estimada)->toDateString(),
            ])
            ->values();
    }

    public function show(Request $request, string $id): array
    {
        ProjectAccessService::ensureCanAccess($request->user(), $id);

        $project = Proyecto::with([
            'cliente:cod_cliente,nombre_cliente',
            'tareas:id_tarea,cod_proy,titulo,estado',
        ])->findOrFail($id);

        return [
            'id' => (string) $project->getKey(),
            'name' => $project->nombre_ubicacion,
            'client' => $project->cliente?->nombre_cliente,
            'tasks' => $project->tareas
                ->map(fn ($task) => [
                    'id' => (string) $task->id_tarea,
                    'title' => $task->titulo,
                    'status' => $task->estado ?? 'todo',
                ])
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
            ->with(['cliente:cod_cliente,nombre_cliente'])
            ->select(['cod_proy', 'nombre_ubicacion', 'cod_cliente', 'fecha_inicio', 'fecha_fin_estimada', 'created_at'])
            ->orderByDesc('created_at');

        if (is_array($allowed)) {
            $query->whereIn('cod_proy', $allowed);
        }

        return $query->get()
            ->map(fn (Proyecto $project) => [
                'id' => (string) $project->getKey(),
                'name' => $project->nombre_ubicacion,
                'client' => $project->cliente?->nombre_cliente,
                'startDate' => optional($project->fecha_inicio)->toDateString(),
                'endDate' => optional($project->fecha_fin_estimada)->toDateString(),
            ])
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
