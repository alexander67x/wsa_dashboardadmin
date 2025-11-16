<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionProyecto;
use App\Models\Empleado;
use App\Models\Proyecto;
use App\Models\Tarea;
use Illuminate\Http\Request;
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

    public function stock(string $id): array
    {
        $project = Proyecto::findOrFail($id);

        // Buscar almacén asociado al proyecto
        $almacen = \App\Models\Almacen::where('cod_proy', $id)
            ->where('activo', true)
            ->first();

        if (!$almacen) {
            return [
                'warehouse' => null,
                'materials' => [],
                'message' => 'No se encontró un almacén activo para este proyecto',
            ];
        }

        // Obtener stock del almacén con información de materiales
        $stock = \App\Models\StockAlmacen::with([
            'material:id_material,codigo_producto,nombre_producto,unidad_medida,costo_unitario_promedio_bs,activo',
        ])
            ->where('id_almacen', $almacen->id_almacen)
            ->whereHas('material', function ($query) {
                $query->where('activo', true);
            })
            ->get()
            ->filter(fn ($stock) => $stock->material)
            ->map(function ($stock) {
                $material = $stock->material;
                return [
                    'id' => (string) $stock->id,
                    'materialId' => (string) $material->id_material,
                    'code' => $material->codigo_producto,
                    'name' => $material->nombre_producto,
                    'unit' => $material->unidad_medida,
                    'available' => (float) $stock->cantidad_disponible,
                    'reserved' => (float) $stock->cantidad_reservada,
                    'availableReal' => (float) ($stock->cantidad_disponible - $stock->cantidad_reservada),
                    'minAlert' => (float) $stock->cantidad_minima_alerta,
                    'location' => $stock->ubicacion_fisica,
                    'unitPrice' => (float) $material->costo_unitario_promedio_bs,
                    'needsRestock' => $stock->cantidad_disponible <= $stock->cantidad_minima_alerta,
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

    /**
     * Obtiene solo los proyectos asignados al usuario autenticado.
     * Incluye proyectos donde el usuario es:
     * - Responsable del proyecto
     * - Supervisor de obra
     * - Miembro del equipo (asignado)
     */
    public function myProjects(Request $request): Collection
    {
        $user = $request->user();
        $empleado = $user->empleado;

        if (!$empleado) {
            return collect([]);
        }

        $codEmpleado = $empleado->cod_empleado;

        // Obtener IDs de proyectos donde el empleado está asignado
        $proyectosAsignadosIds = AsignacionProyecto::where('cod_empleado', $codEmpleado)
            ->where('estado', 'activo')
            ->pluck('cod_proy')
            ->toArray();

        // Obtener todos los proyectos relacionados (asignado, responsable o supervisor)
        $proyectos = Proyecto::query()
            ->where(function ($query) use ($codEmpleado, $proyectosAsignadosIds) {
                $query->where('responsable_proyecto', $codEmpleado)
                    ->orWhere('supervisor_obra', $codEmpleado)
                    ->orWhereIn('cod_proy', $proyectosAsignadosIds);
            })
            ->with(['cliente:cod_cliente,nombre_cliente'])
            ->select(['cod_proy', 'nombre_ubicacion', 'cod_cliente', 'fecha_inicio', 'fecha_fin_estimada', 'created_at'])
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

        return $proyectos->values();
    }

    /**
     * Obtiene los responsables (empleados) disponibles para las tareas 
     * de los proyectos asignados al usuario autenticado.
     * 
     * Incluye empleados que están asignados a los proyectos del usuario
     * (ya sea como responsable del proyecto, supervisor, o miembro del equipo).
     */
    public function taskResponsibles(Request $request): Collection
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

        // Obtener empleados que están asignados a los proyectos del usuario
        $empleadosAsignadosIds = AsignacionProyecto::whereIn('cod_proy', $proyectosIds)
            ->where('estado', 'activo')
            ->pluck('cod_empleado')
            ->unique()
            ->toArray();

        // Obtener responsables de proyectos
        $responsablesProyectoIds = Proyecto::whereIn('cod_proy', $proyectosIds)
            ->whereNotNull('responsable_proyecto')
            ->pluck('responsable_proyecto')
            ->unique()
            ->toArray();

        // Obtener supervisores de proyectos
        $supervisoresProyectoIds = Proyecto::whereIn('cod_proy', $proyectosIds)
            ->whereNotNull('supervisor_obra')
            ->pluck('supervisor_obra')
            ->unique()
            ->toArray();

        // Obtener responsables actuales de tareas en esos proyectos
        $responsablesTareasIds = Tarea::whereIn('cod_proy', $proyectosIds)
            ->whereNotNull('responsable_id')
            ->pluck('responsable_id')
            ->unique()
            ->toArray();

        // Combinar todos los IDs de empleados únicos
        $empleadosIds = collect([
            ...$empleadosAsignadosIds,
            ...$responsablesProyectoIds,
            ...$supervisoresProyectoIds,
            ...$responsablesTareasIds,
        ])->unique()->toArray();

        // Obtener los empleados activos
        $empleados = Empleado::whereIn('cod_empleado', $empleadosIds)
            ->where('activo', true)
            ->orderBy('nombre_completo')
            ->get(['cod_empleado', 'nombre_completo', 'cargo', 'email'])
            ->map(function (Empleado $empleado) {
                return [
                    'id' => (string) $empleado->cod_empleado,
                    'name' => $empleado->nombre_completo,
                    'position' => $empleado->cargo,
                    'email' => $empleado->email,
                ];
            });

        return $empleados->values();
    }
}


