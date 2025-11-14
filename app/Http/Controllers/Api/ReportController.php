<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Archivo;
use App\Models\ReporteAvanceTarea;
use App\Models\ReporteMaterial;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request): Collection
    {
        $validated = $request->validate([
            'projectId' => ['nullable', 'string', 'exists:proyectos,cod_proy'],
            'taskId' => ['nullable', 'integer', 'exists:tareas,id_tarea'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $statusFilter = $this->mapStatusFilter($validated['status'] ?? null);
        $limit = $validated['limit'] ?? 50;

        $reports = ReporteAvanceTarea::query()
            ->with(['proyecto', 'tarea', 'registradoPor'])
            ->when($validated['projectId'] ?? null, fn ($query, $codProy) => $query->where('cod_proy', $codProy))
            ->when($validated['taskId'] ?? null, fn ($query, $taskId) => $query->where('id_tarea', $taskId))
            ->when($statusFilter, fn ($query, $statuses) => $query->whereIn('estado', $statuses))
            ->orderByDesc('fecha_reporte')
            ->limit($limit)
            ->get();

        return $reports->map(fn (ReporteAvanceTarea $report) => $this->transformReport($report));
    }

    public function show(int|string $id): array
    {
        $report = ReporteAvanceTarea::with([
            'proyecto',
            'tarea',
            'registradoPor',
            'aprobadoPor',
            'archivos',
        ])->findOrFail($id);

        return $this->transformReportDetail($report);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'projectId' => ['required', 'string', 'exists:proyectos,cod_proy'],
            'taskId' => ['required', 'integer', 'exists:tareas,id_tarea'],
            'authorId' => ['required', 'integer', 'exists:empleados,cod_empleado'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'reportDate' => ['nullable', 'date'],
            'difficulties' => ['nullable', 'string'],
            'materialsUsed' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*.url' => ['required', 'url'],
            'images.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'images.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'images.*.takenAt' => ['nullable', 'date'],
            'materials' => ['nullable', 'array'],
            'materials.*.materialId' => ['required', 'integer', 'exists:materiales,id_material'],
            'materials.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'materials.*.unit' => ['nullable', 'string', 'max:50'],
            'materials.*.observations' => ['nullable', 'string'],
        ]);

        $task = Tarea::where('cod_proy', $data['projectId'])->findOrFail($data['taskId']);

        $report = DB::transaction(function () use ($data, $task) {
            $report = ReporteAvanceTarea::create([
                'id_tarea' => $task->id_tarea,
                'cod_proy' => $data['projectId'],
                'titulo' => $data['title'],
                'descripcion' => $data['description'],
                'fecha_reporte' => $data['reportDate'] ?? now(),
                'dificultades_encontradas' => $data['difficulties'] ?? null,
                'materiales_utilizados' => $data['materialsUsed'] ?? null,
                'registrado_por' => $data['authorId'],
                'estado' => 'enviado',
                'observaciones_supervisor' => $data['observations'] ?? null,
            ]);

            if (! empty($data['images'])) {
                $archivoIds = [];
                foreach ($data['images'] as $image) {
                    $imageUrl = $image['url'];
                    $archivo = Archivo::create([
                        'entidad' => 'reporte',
                        'entidad_id' => $report->getKey(),
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
                    $archivoIds[] = $archivo->id_archivo;
                }
                $report->archivos()->attach($archivoIds);
            }

            // Guardar materiales usados
            if (! empty($data['materials'])) {
                foreach ($data['materials'] as $materialData) {
                    ReporteMaterial::create([
                        'id_reporte' => $report->getKey(),
                        'id_material' => $materialData['materialId'],
                        'cantidad_usada' => $materialData['quantity'],
                        'unidad_medida' => $materialData['unit'] ?? null,
                        'observaciones' => $materialData['observations'] ?? null,
                    ]);
                }
            }

            return $report;
        });

        $report->load(['proyecto', 'registradoPor']);

        return response()->json([
            'id' => (string) $report->getKey(),
            'report' => $this->transformReport($report),
        ], 201);
    }

    protected function transformReport(ReporteAvanceTarea $report): array
    {
        return [
            'id' => (string) $report->getKey(),
            'projectId' => $report->cod_proy,
            'taskId' => $report->id_tarea ? (string) $report->id_tarea : null,
            'taskTitle' => $report->tarea?->titulo,
            'title' => $report->titulo,
            'project' => $report->proyecto?->nombre_ubicacion,
            'date' => optional($report->fecha_reporte)->toDateString(),
            'type' => 'progress',
            'status' => $this->mapEstadoToStatus($report->estado),
            'progress' => null,
            'authorId' => optional($report->registradoPor)?->cod_empleado ? (string) $report->registradoPor->cod_empleado : null,
            'authorName' => $report->registradoPor?->nombre_completo,
        ];
    }

    protected function transformReportDetail(ReporteAvanceTarea $report): array
    {
        $images = $report->archivos
            ? $report->archivos->map(fn (Archivo $archivo) => $archivo->url)->filter()->values()->all()
            : [];

        $projectLocation = $report->proyecto
            ? implode(' • ', array_filter([
                $report->proyecto->direccion,
                $report->proyecto->ciudad,
                $report->proyecto->pais,
            ]))
            : null;

        return [
            'id' => (string) $report->getKey(),
            'projectId' => $report->cod_proy,
            'taskId' => $report->id_tarea ? (string) $report->id_tarea : null,
            'taskTitle' => $report->tarea?->titulo,
            'taskDescription' => $report->tarea?->descripcion,
            'taskStatus' => $report->tarea?->estado,
            'title' => $report->titulo,
            'project' => $report->proyecto?->nombre_ubicacion,
            'type' => 'progress',
            'status' => $this->mapEstadoToStatus($report->estado),
            'authorId' => optional($report->registradoPor)?->cod_empleado ? (string) $report->registradoPor->cod_empleado : null,
            'author' => $report->registradoPor?->nombre_completo,
            'date' => optional($report->fecha_reporte)->toDateString(),
            'location' => $projectLocation,
            'description' => $report->descripcion,
            'observations' => $report->observaciones_supervisor,
            'images' => $images,
            'approvedBy' => $report->aprobadoPor?->nombre_completo,
            'approvedDate' => optional($report->fecha_aprobacion)->toDateTimeString(),
            'feedback' => $report->observaciones_supervisor,
            'difficulties' => $report->dificultades_encontradas,
            'materialsUsed' => $report->materiales_utilizados,
        ];
    }

    protected function mapEstadoToStatus(?string $estado): string
    {
        return match ($estado) {
            'aprobado' => 'approved',
            'rechazado' => 'rejected',
            default => 'pending',
        };
    }

    protected function mapStatusFilter(?string $status): ?array
    {
        return match ($status) {
            'approved' => ['aprobado'],
            'rejected' => ['rechazado'],
            'pending' => ['borrador', 'enviado'],
            default => null,
        };
    }
}

