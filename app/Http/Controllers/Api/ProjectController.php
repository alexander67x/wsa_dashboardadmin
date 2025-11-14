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
}


