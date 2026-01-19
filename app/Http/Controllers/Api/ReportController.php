<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Archivo;
use App\Models\Empleado;
use App\Models\ReporteAvanceTarea;
use App\Models\ReporteHistorial;
use App\Models\ReporteMaterial;
use App\Models\Material;
use App\Models\StockAlmacen;
use App\Models\Tarea;
use App\Services\ResendMailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'historiales.creadoPor',
            'materiales.material',
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
            'materialsUsed' => ['required', 'string'],
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

        $report->load(['proyecto.responsable', 'registradoPor', 'tarea.responsables', 'tarea.supervisor']);

        $this->notifyReportCreated($report);

        return response()->json([
            'id' => (string) $report->getKey(),
            'report' => $this->transformReport($report),
        ], 201);
    }

    public function resubmit(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'reportDate' => ['nullable', 'date'],
            'difficulties' => ['nullable', 'string'],
            'materialsUsed' => ['required', 'string'],
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

        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $empleado = Empleado::where('email', $user->email)->first();

        if (! $empleado) {
            throw ValidationException::withMessages([
                'user' => ['No se encontró un empleado asociado al usuario autenticado.'],
            ]);
        }

        $report = ReporteAvanceTarea::with('historiales.creadoPor')->findOrFail($id);

        if ($report->estado !== 'rechazado') {
            throw ValidationException::withMessages([
                'status' => ['Solo los reportes rechazados pueden reenviarse.'],
            ]);
        }

        if ((int) $report->registrado_por !== (int) $empleado->cod_empleado) {
            abort(403, 'Solo el autor puede reenviar este reporte.');
        }

        $report = DB::transaction(function () use ($report, $data) {
            $report = ReporteAvanceTarea::whereKey($report->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $updates = [];

            if (array_key_exists('title', $data)) {
                $updates['titulo'] = $data['title'];
            }

            if (array_key_exists('description', $data)) {
                $updates['descripcion'] = $data['description'];
            }

            if (array_key_exists('reportDate', $data)) {
                $updates['fecha_reporte'] = $data['reportDate'];
            }

            if (array_key_exists('difficulties', $data)) {
                $updates['dificultades_encontradas'] = $data['difficulties'];
            }

            if (array_key_exists('materialsUsed', $data)) {
                $updates['materiales_utilizados'] = $data['materialsUsed'];
            }

            if (array_key_exists('observations', $data)) {
                $updates['observaciones_supervisor'] = $data['observations'];
            }

            $updates['estado'] = 'enviado';
            $updates['fecha_aprobacion'] = null;
            $updates['aprobado_por'] = null;

            $report->update($updates);

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
                        'creado_por' => $report->registrado_por,
                    ]);
                    $archivoIds[] = $archivo->id_archivo;
                }
                $report->archivos()->attach($archivoIds);
            }

            if (array_key_exists('materials', $data)) {
                ReporteMaterial::where('id_reporte', $report->getKey())->delete();

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
            }

            ReporteHistorial::create([
                'id_reporte' => $report->getKey(),
                'tipo' => 'reenvio',
                'comentario' => 'Reporte reenviado tras correcciones.',
                'creado_por' => $report->registrado_por,
            ]);

            return $report;
        });

        $report->load(['proyecto', 'tarea', 'registradoPor', 'aprobadoPor', 'archivos', 'historiales.creadoPor']);

        $this->notifyReportCreated($report);

        return response()->json([
            'message' => 'Reporte reenviado correctamente.',
            'report' => $this->transformReportDetail($report),
        ]);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'observations' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $empleado = Empleado::where('email', $user->email)->first();

        if (! $empleado) {
            throw ValidationException::withMessages([
                'user' => ['No se encontró un empleado asociado al usuario autenticado.'],
            ]);
        }

        $report = DB::transaction(function () use ($id, $data, $empleado) {
            $report = ReporteAvanceTarea::with('materiales')
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($report->estado, ['enviado', 'borrador'])) {
                throw ValidationException::withMessages([
                    'status' => ['El reporte no está pendiente y no puede aprobarse.'],
                ]);
            }

            $materialesUsados = $report->materiales;

            if ($materialesUsados->isNotEmpty()) {
                $almacen = Almacen::where('cod_proy', $report->cod_proy)
                    ->where('activo', true)
                    ->first();

                if (! $almacen) {
                    throw ValidationException::withMessages([
                        'warehouse' => ['No se encontró un almacén activo asociado al proyecto. No es posible aprobar el reporte.'],
                    ]);
                }

                $errores = [];

                foreach ($materialesUsados as $reporteMaterial) {
                    $material = Material::find($reporteMaterial->id_material);
                    $nombreMaterial = $material ? $material->nombre_producto : "ID {$reporteMaterial->id_material}";

                    $stock = StockAlmacen::where('id_almacen', $almacen->id_almacen)
                        ->where('id_material', $reporteMaterial->id_material)
                        ->lockForUpdate()
                        ->first();

                    if (! $stock) {
                        $errores[] = "Material '{$nombreMaterial}' no encontrado en el almacén del proyecto.";
                        continue;
                    }

                    $cantidadDisponible = (float) $stock->cantidad_disponible;
                    $cantidadUsada = (float) $reporteMaterial->cantidad_usada;

                    if ($cantidadDisponible < $cantidadUsada) {
                        $errores[] = "Stock insuficiente para '{$nombreMaterial}'. Disponible: {$cantidadDisponible}, Requerido: {$cantidadUsada}";
                        continue;
                    }

                    $stock->decrement('cantidad_disponible', $cantidadUsada);
                }

                if (! empty($errores)) {
                    throw ValidationException::withMessages([
                        'materials' => $errores,
                    ]);
                }
            }

            $report->update([
                'estado' => 'aprobado',
                'observaciones_supervisor' => $data['observations'] ?? null,
                'fecha_aprobacion' => now(),
                'aprobado_por' => $empleado->cod_empleado,
            ]);

            if ($report->id_tarea) {
                $tarea = Tarea::whereKey($report->id_tarea)->lockForUpdate()->first();
                if ($tarea && $tarea->estado !== 'finalizada') {
                    $tarea->update([
                        'estado' => 'finalizada',
                        'fecha_fin' => $report->fecha_reporte ?? now(),
                    ]);
                }
            }

            return $report;
        });

        $report->load(['proyecto', 'tarea', 'registradoPor', 'aprobadoPor', 'archivos', 'historiales.creadoPor']);

        $this->notifyReportStatusChange($report, 'aprobado', $data['observations'] ?? null);

        return response()->json([
            'message' => 'Reporte aprobado correctamente.',
            'report' => $this->transformReportDetail($report),
        ]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'observations' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $empleado = Empleado::where('email', $user->email)->first();

        if (! $empleado) {
            throw ValidationException::withMessages([
                'user' => ['No se encontró un empleado asociado al usuario autenticado.'],
            ]);
        }

        $report = DB::transaction(function () use ($id, $data, $empleado) {
            $report = ReporteAvanceTarea::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($report->estado, ['enviado', 'borrador'])) {
                throw ValidationException::withMessages([
                    'status' => ['El reporte no está pendiente y no puede rechazarse.'],
                ]);
            }

            $report->update([
                'estado' => 'rechazado',
                'observaciones_supervisor' => $data['observations'] ?? null,
                'fecha_aprobacion' => now(),
                'aprobado_por' => $empleado->cod_empleado,
            ]);

            return $report;
        });

        $report->load(['proyecto', 'tarea', 'registradoPor', 'aprobadoPor', 'archivos', 'historiales.creadoPor']);

        $this->notifyReportStatusChange($report, 'rechazado', $data['observations'] ?? null);

        return response()->json([
            'message' => 'Reporte rechazado correctamente.',
            'report' => $this->transformReportDetail($report),
        ]);
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
            'materialsUsed' => $report->materiales_utilizados,
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

        $projectName = $report->proyecto?->nombre_ubicacion;
        $projectCode = $report->cod_proy;
        $authorName = $report->registradoPor?->nombre_completo;
        $date = optional($report->fecha_reporte)->toDateString();
        $statusEn = $this->mapEstadoToStatus($report->estado);
        $statusEs = match ($report->estado) {
            'aprobado' => 'aprobado',
            'rechazado' => 'rechazado',
            'borrador', 'enviado' => 'pendiente',
            default => $report->estado ?? 'pendiente',
        };
        $description = $report->descripcion;
        $observaciones = $report->observaciones_supervisor;
  
          return [
              'id' => (string) $report->getKey(),
              'projectId' => $report->cod_proy,
              'taskId' => $report->id_tarea ? (string) $report->id_tarea : null,
            'taskTitle' => $report->tarea?->titulo,
            'taskDescription' => $report->tarea?->descripcion,
            'taskStatus' => $report->tarea?->estado,
              'title' => $report->titulo,

              // Proyecto: código e identificadores alternativos
              'project' => $projectName,
              'projectCode' => $projectCode,
              'projectName' => $projectName,
              'project_name' => $projectName,
              'proyecto' => $projectName,
              'obra' => $projectName,

              'type' => 'progress',

              // Estado en inglés y español
              'status' => $statusEn,
              'status_es' => $statusEs,

              'authorId' => optional($report->registradoPor)?->cod_empleado ? (string) $report->registradoPor->cod_empleado : null,
              'author' => $authorName,
              'authorName' => $authorName,
              'author_name' => $authorName,
              'autor' => $authorName,
              'autorNombre' => $authorName,

              // Fecha del reporte con alias
              'date' => $date,
              'fecha' => $date,
              'fecha_reporte' => $date,
              'reportDate' => $date,

              'location' => $projectLocation,
              // Descripción y observaciones con varias claves soportadas
              'description' => $description,
              'descripcion' => $description,
              'detalle' => $description,
              'detalles' => $description,
              'observaciones' => $observaciones,

              'images' => $images,
            'approvedBy' => $report->aprobadoPor?->nombre_completo,
            'approvedDate' => optional($report->fecha_aprobacion)->toDateTimeString(),
            'feedback' => $report->observaciones_supervisor,
            'difficulties' => $report->dificultades_encontradas,
            'materialsUsed' => $report->materiales_utilizados,
            'materials' => $report->materiales
                ? $report->materiales->map(function (ReporteMaterial $material) {
                    return [
                        'id' => (string) $material->getKey(),
                        'materialId' => (int) $material->id_material,
                        'materialName' => $material->material?->nombre_producto,
                        'quantity' => (float) $material->cantidad_usada,
                        'unit' => $material->unidad_medida,
                        'observations' => $material->observaciones,
                    ];
                })->values()->all()
                : [],
            'history' => $this->transformReportHistory($report),
        ];
    }

    protected function transformReportHistory(ReporteAvanceTarea $report): array
    {
        if (! $report->relationLoaded('historiales')) {
            $report->loadMissing('historiales.creadoPor');
        }

        return $report->historiales
            ? $report->historiales->map(function (ReporteHistorial $historial) {
                return [
                    'id' => (string) $historial->getKey(),
                    'type' => $historial->tipo,
                    'comment' => $historial->comentario,
                    'createdAt' => optional($historial->created_at)->toDateTimeString(),
                    'createdById' => $historial->creado_por ? (string) $historial->creado_por : null,
                    'createdByName' => $historial->creadoPor?->nombre_completo,
                ];
            })->all()
            : [];
    }

    protected function notifyReportCreated(ReporteAvanceTarea $report): void
    {
        $resend = app(ResendMailService::class);

        $projectName = $report->proyecto?->nombre_ubicacion ?? $report->cod_proy;
        $taskTitle = $report->tarea?->titulo ?? 'Tarea';

        $recipients = collect();

        if ($report->tarea && $report->tarea->supervisor) {
            $recipients->push($report->tarea->supervisor);
        }

        if ($report->tarea && $report->tarea->responsables) {
            $recipients = $recipients->merge($report->tarea->responsables);
        }

        if ($report->proyecto && $report->proyecto->responsable) {
            $recipients->push($report->proyecto->responsable);
        }

        $recipients = $recipients
            ->filter(fn ($empleado) => $empleado && $empleado->email)
            ->unique('email');

        if ($recipients->isEmpty()) {
            return;
        }

        foreach ($recipients as $destinatario) {
            $subject = "Nuevo reporte en {$projectName} - {$taskTitle}";
            $html = "<p>Hola {$destinatario->nombre_completo},</p>"
                . "<p>Se registró un nuevo reporte de avance para la tarea "
                . "<strong>{$taskTitle}</strong> en el proyecto "
                . "<strong>{$projectName}</strong>.</p>";

            $resend->send($destinatario->email, $subject, $html);
        }
    }

    protected function notifyReportStatusChange(ReporteAvanceTarea $report, string $estado, ?string $observaciones = null): void
    {
        $report->loadMissing(['proyecto', 'tarea', 'registradoPor']);

        $destinatario = $report->registradoPor;
        if (! $destinatario || ! $destinatario->email) {
            return;
        }

        $resend = app(ResendMailService::class);

        $projectName = $report->proyecto?->nombre_ubicacion ?? $report->cod_proy;
        $taskTitle = $report->tarea?->titulo ?? 'Tarea';

        $estadoLabel = $estado === 'aprobado' ? 'aprobado' : 'rechazado';

        $subject = "Reporte {$estadoLabel} - {$projectName}";

        $html = "<p>Hola {$destinatario->nombre_completo},</p>"
            . "<p>Tu reporte <strong>{$report->titulo}</strong> en la tarea "
            . "<strong>{$taskTitle}</strong> del proyecto "
            . "<strong>{$projectName}</strong> ha sido {$estadoLabel}.</p>";

        if ($observaciones) {
            $html .= "<p><strong>Observaciones del supervisor:</strong><br>"
                . nl2br(e($observaciones)) . '</p>';
        }

        $resend->send($destinatario->email, $subject, $html);
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
