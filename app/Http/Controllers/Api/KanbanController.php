<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Archivo;
use App\Models\Proyecto;
use App\Models\ReporteAvanceTarea;
use App\Models\Tarea;
use App\Services\ProjectAccessService;
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

        $projectId = $validated['projectId'] ?? ($allowed === null
            ? Proyecto::query()->orderBy('cod_proy')->value('cod_proy')
            : ($allowed[0] ?? null));

        if (! $projectId) {
            return $this->emptyBoard();
        }

        ProjectAccessService::ensureCanAccess($request->user(), $projectId);

        $reports = ReporteAvanceTarea::with(['registradoPor', 'archivos'])
            ->where('cod_proy', $projectId)
            ->orderByDesc('fecha_reporte')
            ->get();

        $board = [
            'En revisión' => [],
            'Aprobado' => [],
            'Rechazado' => [],
            'Reenviado' => [],
            'Tareas' => [],
        ];

        foreach ($reports as $report) {
            $column = $this->mapEstadoToColumn($report->estado);
            $board[$column][] = $this->transformReportCard($report);
        }

        $board['Tareas'] = $this->tasksForProject($projectId);

        return $board;
    }

    public function addColumn(Request $request)
    {
        ProjectAccessService::ensureCanAccess($request->user(), $request->input('projectId'));

        $request->validate([
            'name' => ['required', 'string'],
        ]);

        return response()->json([
            'message' => 'Las columnas del tablero se generan automáticamente a partir del estado del reporte.',
        ], 202);
    }

    public function addCard(Request $request)
    {
        ProjectAccessService::ensureCanAccess($request->user(), $request->input('projectId'));

        $data = $request->validate([
            'projectId' => ['required', 'string', 'exists:proyectos,cod_proy'],
            'taskId' => ['required', 'integer', 'exists:tareas,id_tarea'],
            'column' => ['required', Rule::in(['En revisión', 'Reenviado'])],
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

        $task = Tarea::with('responsable')->findOrFail($id);
        ProjectAccessService::ensureCanAccess(request()->user(), $task->cod_proy);

        return [
            'id' => (string) $task->id_tarea,
            'title' => $task->titulo,
            'authorId' => optional($task->responsable)?->cod_empleado ? (string) $task->responsable->cod_empleado : null,
            'authorName' => $task->responsable?->nombre_completo,
            'description' => $task->descripcion,
            'photos' => [],
            'createdAt' => optional($task->created_at)->toDateTimeString(),
            'column' => $task->estado,
        ];
    }

    protected function transformReportCard(ReporteAvanceTarea $report): array
    {
        return [
            'id' => (string) $report->getKey(),
            'title' => $report->titulo,
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
            default => 'En revisión',
        };
    }

    protected function mapColumnToEstado(string $column): string
    {
        return match ($column) {
            'Reenviado' => 'enviado',
            default => 'enviado',
        };
    }

    protected function tasksForProject(string $projectId): array
    {
        ProjectAccessService::ensureCanAccess(request()->user(), $projectId);

        return Tarea::with('responsable')
            ->where('cod_proy', $projectId)
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->map(function (Tarea $task) {
                return [
                    'id' => (string) $task->id_tarea,
                    'title' => $task->titulo,
                    'authorId' => optional($task->responsable)?->cod_empleado ? (string) $task->responsable->cod_empleado : null,
                    'authorName' => $task->responsable?->nombre_completo,
                    'description' => Str::limit($task->descripcion, 280),
                    'photos' => [],
                    'createdAt' => optional($task->created_at)->toDateTimeString(),
                ];
            })
            ->values()
            ->all();
    }

    protected function emptyBoard(): array
    {
        return [
            'En revisión' => [],
            'Aprobado' => [],
            'Rechazado' => [],
            'Reenviado' => [],
            'Tareas' => [],
        ];
    }
}
