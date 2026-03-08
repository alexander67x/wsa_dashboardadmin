<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Archivo;
use App\Models\Proyecto;
use App\Models\ReporteAvanceTarea;
use App\Models\Tarea;
use App\Services\ProjectAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KanbanController extends Controller
{
    public function board(Request $request): array
    {
        $validated = $request->validate([
            'projectId' => ['nullable', 'string', 'exists:proyectos,cod_proy'],
        ]);

        $allowed = ProjectAccessService::allowedProjectIds($request->user());
        if ($allowed === []) {
            return $this->emptyBoard();
        }

        $projectId = $validated['projectId'] ?? $this->resolveDefaultProjectId($request, $allowed);

        if (! $projectId) {
            return $this->emptyBoard();
        }

        ProjectAccessService::ensureCanAccess($request->user(), $projectId);

        // Reportes de avance para el tablero clásico
        $reports = ReporteAvanceTarea::with(['registradoPor', 'archivos'])
            ->where('cod_proy', $projectId)
            ->orderByDesc('fecha_reporte')
            ->get();

        $board = [
            'En revisi¢n' => [],
            'Aprobado' => [],
            'Rechazado' => [],
            'Reenviado' => [],
            'Tareas' => [],
        ];

        foreach ($reports as $report) {
            $column = $this->mapEstadoToColumn($report->estado);
            $board[$column][] = $this->transformReportCard($report);
        }

        // Listado plano de tareas (como antes)
        $board['Tareas'] = $this->tasksForProject($projectId);

        // Estructura simplificada de 3 tableros basada en tareas:
        // - pendiente: tareas sin reportes de avance
        // - revision: tareas con reportes en revisión (enviados/borrador)
        // - finalizada: tareas con estado finalizada
        $tasks = $this->buildVisibleTasksQuery($request->user(), $projectId)
            ->with('responsables')
            ->get();

        $reportsByTask = $reports->groupBy('id_tarea');

        $pending = [];
        $inReview = [];
        $completed = [];

        foreach ($tasks as $task) {
            $card = $this->transformTaskCard($task);
            $taskReports = $reportsByTask->get($task->id_tarea) ?? collect();

            // Tareas finalizadas van siempre al tablero de "finalizada"
            if ($task->estado === 'finalizada') {
                $completed[] = $card;
                continue;
            }

            // Tareas con reportes en estado de revisión (enviados o borrador)
            $hasReviewReports = $taskReports->contains(function (ReporteAvanceTarea $report) {
                return in_array($report->estado, ['enviado', 'borrador'], true);
            });

            if ($hasReviewReports) {
                $inReview[] = $card;
                continue;
            }

            // Resto de tareas (sin reportes o solo rechazados) se consideran pendientes
            $pending[] = $card;
        }

        // Agregar estructura simplificada al payload
        $board['pendiente'] = $pending;
        $board['revision'] = $inReview;
        $board['finalizada'] = $completed;

        return $board;
    }

    public function addColumn(Request $request)
    {
        ProjectAccessService::ensureCanAccess($request->user(), $request->input('projectId'));

        $request->validate([
            'name' => ['required', 'string'],
        ]);

        return response()->json([
            'message' => 'Las columnas del tablero se generan autom ticamente a partir del estado del reporte.',
        ], 202);
    }

    public function addCard(Request $request)
    {
        ProjectAccessService::ensureCanAccess($request->user(), $request->input('projectId'));

        $data = $request->validate([
            'projectId' => ['required', 'string', 'exists:proyectos,cod_proy'],
            'taskId' => ['required', 'integer', 'exists:tareas,id_tarea'],
            'column' => ['required', Rule::in(['En revisi¢n', 'Reenviado'])],
            'card' => ['required', 'array'],
            'card.title' => ['required', 'string', 'max:255'],
            'card.description' => ['required', 'string'],
            'card.authorId' => ['required', 'integer', 'exists:empleados,cod_empleado'],
            'card.photos' => ['array'],
        ]);

        $task = Tarea::where('cod_proy', $data['projectId'])->findOrFail($data['taskId']);

        $estado = $this->mapColumnToEstado($data['column']);

        $report = DB::transaction(function () use ($data, $task, $estado) {
            $report = ReporteAvanceTarea::create([
                'id_tarea' => $task->id_tarea,
                'cod_proy' => $data['projectId'],
                'titulo' => $data['card']['title'],
                'descripcion' => $data['card']['description'],
                'fecha_reporte' => now(),
                'registrado_por' => $data['card']['authorId'],
                'estado' => $estado,
            ]);

            $attachmentIds = collect($data['card']['photos'] ?? [])
                ->map(function ($value) {
                    return is_numeric($value) ? (int) $value : null;
                })
                ->filter()
                ->values();

            if ($attachmentIds->isNotEmpty()) {
                $validIds = Archivo::whereIn('id_archivo', $attachmentIds)->pluck('id_archivo')->all();
                if (! empty($validIds)) {
                    $report->archivos()->attach($validIds);
                }
            }

            return $report;
        });

        $report->load(['registradoPor', 'archivos']);

        return response()->json($this->transformReportCard($report), 201);
    }

    public function showCard(int|string $id): array
    {
        $report = ReporteAvanceTarea::with(['registradoPor', 'archivos'])->find($id);
        if ($report) {
            ProjectAccessService::ensureCanAccess(request()->user(), $report->cod_proy);
            $card = $this->transformReportCard($report);
            $card['column'] = $this->mapEstadoToColumn($report->estado);
            return $card;
        }

        $task = Tarea::with('responsables')->findOrFail($id);
        ProjectAccessService::ensureCanAccess(request()->user(), $task->cod_proy);

        $responsibles = $this->serializeResponsables($task);
        $primaryResponsible = $responsibles[0] ?? null;

        return [
            'id' => (string) $task->id_tarea,
            'title' => $task->titulo,
            // Identificadores para navegaci¢n en el front
            'taskId' => (string) $task->id_tarea,
            'projectId' => $task->cod_proy ? (string) $task->cod_proy : null,
            'task' => [
                'id' => (string) $task->id_tarea,
                'projectId' => $task->cod_proy ? (string) $task->cod_proy : null,
            ],
            'metadata' => [
                'cod_proy' => $task->cod_proy ? (string) $task->cod_proy : null,
                'cod_tarea' => (string) $task->id_tarea,
            ],
            'authorId' => $primaryResponsible['id'] ?? null,
            'authorName' => $primaryResponsible['name'] ?? null,
            'description' => $task->descripcion,
            'photos' => [],
            'createdAt' => optional($task->created_at)->toDateTimeString(),
            'column' => $task->estado,
            'responsibleIds' => collect($responsibles)->pluck('id')->all(),
            'responsibles' => $responsibles,
        ];
    }

    protected function transformReportCard(ReporteAvanceTarea $report): array
    {
        return [
            'id' => (string) $report->getKey(),
            'title' => $report->titulo,
            // Identificadores para navegaci¢n en el front
            'taskId' => $report->id_tarea ? (string) $report->id_tarea : null,
            'projectId' => $report->cod_proy ? (string) $report->cod_proy : null,
            'task' => $report->id_tarea ? [
                'id' => (string) $report->id_tarea,
                'projectId' => $report->cod_proy ? (string) $report->cod_proy : null,
            ] : null,
            'metadata' => [
                'cod_proy' => $report->cod_proy ? (string) $report->cod_proy : null,
                'cod_tarea' => $report->id_tarea ? (string) $report->id_tarea : null,
            ],
            'authorId' => optional($report->registradoPor)?->cod_empleado ? (string) $report->registradoPor->cod_empleado : null,
            'authorName' => $report->registradoPor?->nombre_completo,
            'description' => Str::limit($report->descripcion, 280),
            'photos' => $report->archivos
                ? $report->archivos->map(fn (Archivo $archivo) => $archivo->url)->filter()->values()->all()
                : [],
            'createdAt' => optional($report->fecha_reporte)->toDateString(),
        ];
    }

    protected function mapEstadoToColumn(?string $estado): string
    {
        return match ($estado) {
            'aprobado' => 'Aprobado',
            'rechazado' => 'Rechazado',
            'borrador' => 'Reenviado',
            default => 'En revisi¢n',
        };
    }

    protected function mapColumnToEstado(string $column): string
    {
        return match ($column) {
            'Reenviado' => 'enviado',
            default => 'enviado',
        };
    }

    protected function transformTaskCard(Tarea $task): array
    {
        $responsibles = $this->serializeResponsables($task);
        $primaryResponsible = $responsibles[0] ?? null;

        return [
            'id' => (string) $task->id_tarea,
            'title' => $task->titulo,
            // Identificadores para navegaci¢n en el front
            'taskId' => (string) $task->id_tarea,
            'projectId' => $task->cod_proy ? (string) $task->cod_proy : null,
            'task' => [
                'id' => (string) $task->id_tarea,
                'projectId' => $task->cod_proy ? (string) $task->cod_proy : null,
            ],
            'metadata' => [
                'cod_proy' => $task->cod_proy ? (string) $task->cod_proy : null,
                'cod_tarea' => (string) $task->id_tarea,
            ],
            'authorId' => $primaryResponsible['id'] ?? null,
            'authorName' => $primaryResponsible['name'] ?? null,
            'description' => Str::limit($task->descripcion, 280),
            'photos' => [],
            'createdAt' => optional($task->created_at)->toDateTimeString(),
            'responsibleIds' => collect($responsibles)->pluck('id')->all(),
            'responsibles' => $responsibles,
        ];
    }

    protected function tasksForProject(string $projectId): array
    {
        ProjectAccessService::ensureCanAccess(request()->user(), $projectId);

        return $this->buildVisibleTasksQuery(request()->user(), $projectId)
            ->with('responsables')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Tarea $task) => $this->transformTaskCard($task))
            ->values()
            ->all();
    }

    protected function emptyBoard(): array
    {
        return [
            'En revisi¢n' => [],
            'Aprobado' => [],
            'Rechazado' => [],
            'Reenviado' => [],
            'Tareas' => [],
        ];
    }

    protected function serializeResponsables(Tarea $task): array
    {
        return $task->responsables
            ->map(fn ($empleado) => [
                'id' => (string) $empleado->cod_empleado,
                'name' => $empleado->nombre_completo,
                'position' => $empleado->cargo,
            ])
            ->values()
            ->all();
    }

    protected function resolveDefaultProjectId(Request $request, ?array $allowed): ?string
    {
        if ($allowed === []) {
            return null;
        }

        $taskProjectId = $this->buildVisibleTasksQuery($request->user())
            ->select('cod_proy')
            ->distinct()
            ->orderBy('cod_proy')
            ->value('cod_proy');

        if ($taskProjectId) {
            return $taskProjectId;
        }

        if ($allowed === null) {
            return Proyecto::query()->orderBy('cod_proy')->value('cod_proy');
        }

        return $allowed[0] ?? null;
    }

    protected function buildVisibleTasksQuery($user, ?string $projectId = null): Builder
    {
        $query = Tarea::query();

        if ($projectId) {
            $query->where('cod_proy', $projectId);
        }

        $empleadoId = $user?->empleado?->cod_empleado;
        $roleSlug = $user?->empleado?->role?->slug;

        // Para personal de obra, solo mostrar tareas donde esté asignado como responsable.
        if ($roleSlug === 'personal_obra' && $empleadoId) {
            $query->whereHas('responsables', function (Builder $responsablesQuery) use ($empleadoId) {
                $responsablesQuery->where('empleados.cod_empleado', $empleadoId);
            });
        }

        return $query;
    }
}
