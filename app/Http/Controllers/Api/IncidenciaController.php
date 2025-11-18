<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Archivo;
use App\Models\Incidencia;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IncidenciaController extends Controller
{
    public function index(Request $request): Collection
    {
        $validated = $request->validate([
            'projectId' => ['nullable', 'string', 'exists:proyectos,cod_proy'],
            'taskId' => ['nullable', 'integer', 'exists:tareas,id_tarea'],
            'status' => ['nullable', Rule::in(['abierta', 'en_proceso', 'resuelta', 'verificacion', 'cerrada', 'reabierta'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $limit = $validated['limit'] ?? 50;

        $incidencias = Incidencia::query()
            ->with(['proyecto', 'tarea', 'reportadoPor', 'asignadoA'])
            ->when($validated['projectId'] ?? null, fn ($query, $codProy) => $query->where('cod_proy', $codProy))
            ->when($validated['taskId'] ?? null, fn ($query, $taskId) => $query->where('id_tarea', $taskId))
            ->when($validated['status'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->orderByDesc('fecha_reportado')
            ->limit($limit)
            ->get();

        return $incidencias->map(fn (Incidencia $incidencia) => $this->transformIncidencia($incidencia));
    }

    public function show(int|string $id): array
    {
        $incidencia = Incidencia::with([
            'proyecto',
            'tarea',
            'reportadoPor',
            'asignadoA',
            'archivos',
        ])->findOrFail($id);

        return $this->transformIncidenciaDetail($incidencia);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'projectId' => ['required', 'string', 'exists:proyectos,cod_proy'],
            'taskId' => ['nullable', 'integer', 'exists:tareas,id_tarea'],
            'authorId' => ['required', 'integer', 'exists:empleados,cod_empleado'],
            'assignedToId' => ['nullable', 'integer', 'exists:empleados,cod_empleado'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'tipo' => ['required', Rule::in(['falla_equipos', 'accidente', 'retraso_material', 'problema_calidad', 'otro'])],
            'severidad' => ['nullable', Rule::in(['critica', 'alta', 'media', 'baja'])],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'images' => ['nullable', 'array'],
            'images.*.url' => ['required', 'url'],
            'images.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'images.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'images.*.takenAt' => ['nullable', 'date'],
            'images.*.description' => ['nullable', 'string'],
        ]);

        $severity = $data['severidad'] ?? 'media';

        $incidencia = DB::transaction(function () use ($data, $severity) {
            $incidencia = Incidencia::create([
                'cod_proy' => $data['projectId'],
                'id_tarea' => $data['taskId'] ?? null,
                'titulo' => $data['title'],
                'descripcion' => $data['description'],
                'tipo_incidencia' => $data['tipo'],
                'severidad' => $severity,
                'estado' => 'abierta',
                'latitud' => $data['latitude'] ?? null,
                'longitud' => $data['longitude'] ?? null,
                'reportado_por' => $data['authorId'],
                'asignado_a' => $data['assignedToId'] ?? null,
                'fecha_reportado' => now(),
            ]);

            // Guardar imágenes/evidencias
            if (!empty($data['images'])) {
                $archivoIds = [];
                foreach ($data['images'] as $image) {
                    $imageUrl = $image['url'];
                    $archivo = Archivo::create([
                        'entidad' => 'incidencia',
                        'entidad_id' => $incidencia->getKey(),
                        'nombre_original' => basename(parse_url($imageUrl, PHP_URL_PATH)) ?: 'image.jpg',
                        'ruta_storage' => $imageUrl,
                        'tipo_mime' => 'image/jpeg',
                        'tamano_bytes' => null,
                        'es_foto' => true,
                        'latitud' => $image['latitude'] ?? null,
                        'longitud' => $image['longitude'] ?? null,
                        'tomado_en' => isset($image['takenAt']) ? $image['takenAt'] : null,
                        'creado_por' => $data['authorId'],
                    ]);
                    
                    // Crear evidencia vinculada
                    \App\Models\IncidenciaEvidencia::create([
                        'id_incidencia' => $incidencia->getKey(),
                        'archivo_id' => $archivo->id_archivo,
                        'descripcion' => $image['description'] ?? null,
                    ]);
                    
                    $archivoIds[] = $archivo->id_archivo;
                }
            }

            // Crear registro en historial
            \App\Models\IncidenciaHistorial::create([
                'id_incidencia' => $incidencia->getKey(),
                'estado_anterior' => null,
                'estado_nuevo' => 'abierta',
                'comentario' => 'Incidencia creada',
                'accion_tomada' => 'Creación de incidencia',
                'usuario_cambio' => $data['authorId'],
                'fecha_cambio' => now(),
            ]);

            return $incidencia;
        });

        $incidencia->load(['proyecto', 'reportadoPor', 'asignadoA', 'archivos']);

        return response()->json([
            'id' => (string) $incidencia->getKey(),
            'incidencia' => $this->transformIncidencia($incidencia),
        ], 201);
    }

    protected function transformIncidencia(Incidencia $incidencia): array
    {
        return [
            'id' => (string) $incidencia->getKey(),
            'projectId' => $incidencia->cod_proy,
            'taskId' => $incidencia->id_tarea ? (string) $incidencia->id_tarea : null,
            'taskTitle' => $incidencia->tarea?->titulo,
            'title' => $incidencia->titulo,
            'project' => $incidencia->proyecto?->nombre_ubicacion,
            'date' => optional($incidencia->fecha_reportado)->toDateString(),
            'type' => 'incidence',
            'status' => $incidencia->estado,
            'severity' => $incidencia->severidad,
            'tipo' => $incidencia->tipo_incidencia,
            'authorId' => optional($incidencia->reportadoPor)?->cod_empleado ? (string) $incidencia->reportadoPor->cod_empleado : null,
            'authorName' => $incidencia->reportadoPor?->nombre_completo,
            'assignedToId' => optional($incidencia->asignadoA)?->cod_empleado ? (string) $incidencia->asignadoA->cod_empleado : null,
            'assignedToName' => $incidencia->asignadoA?->nombre_completo,
        ];
    }

    protected function transformIncidenciaDetail(Incidencia $incidencia): array
    {
        $images = $incidencia->archivos
            ? $incidencia->archivos->map(fn (Archivo $archivo) => [
                'url' => $archivo->url,
                'description' => $archivo->pivot->descripcion ?? null,
            ])->filter(fn ($img) => $img['url'])->values()->all()
            : [];

        $projectLocation = $incidencia->proyecto
            ? implode(' • ', array_filter([
                $incidencia->proyecto->direccion,
                $incidencia->proyecto->ciudad,
                $incidencia->proyecto->pais,
            ]))
            : null;

        return [
            'id' => (string) $incidencia->getKey(),
            'projectId' => $incidencia->cod_proy,
            'taskId' => $incidencia->id_tarea ? (string) $incidencia->id_tarea : null,
            'taskTitle' => $incidencia->tarea?->titulo,
            'taskDescription' => $incidencia->tarea?->descripcion,
            'taskStatus' => $incidencia->tarea?->estado,
            'title' => $incidencia->titulo,
            'project' => $incidencia->proyecto?->nombre_ubicacion,
            'type' => 'incidence',
            'status' => $incidencia->estado,
            'severity' => $incidencia->severidad,
            'tipo' => $incidencia->tipo_incidencia,
            'authorId' => optional($incidencia->reportadoPor)?->cod_empleado ? (string) $incidencia->reportadoPor->cod_empleado : null,
            'author' => $incidencia->reportadoPor?->nombre_completo,
            'assignedToId' => optional($incidencia->asignadoA)?->cod_empleado ? (string) $incidencia->asignadoA->cod_empleado : null,
            'assignedTo' => $incidencia->asignadoA?->nombre_completo,
            'date' => optional($incidencia->fecha_reportado)->toDateString(),
            'location' => $projectLocation,
            'description' => $incidencia->descripcion,
            'images' => $images,
            'latitude' => $incidencia->latitud,
            'longitude' => $incidencia->longitud,
            'solution' => $incidencia->solucion_implementada,
            'resolvedAt' => optional($incidencia->fecha_resolucion)->toDateTimeString(),
        ];
    }
}

