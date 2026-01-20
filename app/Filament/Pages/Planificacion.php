<?php

namespace App\Filament\Pages;

use App\Services\ResendMailService;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\Empleado;
use App\Models\Proyecto;
use App\Models\Tarea;
use App\Models\AsignacionProyecto;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class Planificacion extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Planificación';
    protected static ?string $title = 'Planificación';
    protected string $view = 'filament.pages.planificacion';

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Ocultar de la navegación, ahora se usa TareaResource
    }

    public ?string $codProy = null;
    public ?KanbanBoard $board = null;

    public array $columns = [];
    public array $tareasByColumn = [];

    public ?string $nuevoTitulo = null;
    public array $nuevosResponsables = [];

    protected array $defaultColumns = [
        [ 'nombre' => 'Por hacer',   'orden' => 1, 'es_entrada' => true,  'es_salida' => false ],
        [ 'nombre' => 'En progreso', 'orden' => 2, 'es_entrada' => false, 'es_salida' => false ],
        [ 'nombre' => 'Hecho',       'orden' => 3, 'es_entrada' => false, 'es_salida' => true  ],
    ];

    public function mount(): void
    {
        $this->refreshData();
    }

    public function assignResponsables(int $idTarea, array $empleadoIds = []): void
    {
        if (! $this->codProy) {
            return;
        }

        $tarea = Tarea::where('cod_proy', $this->codProy)->find($idTarea);
        if (! $tarea) {
            return;
        }

        $empleados = collect($empleadoIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $validos = $empleados->isEmpty()
            ? collect()
            : AsignacionProyecto::where('cod_proy', $this->codProy)
                ->whereIn('cod_empleado', $empleados)
                ->pluck('cod_empleado')
                ->unique();

        $tarea->responsables()->sync($validos->all());

        $tarea->loadMissing(['responsables', 'proyecto']);
        $this->notifyTaskResponsables($tarea, 'updated');

        $this->refreshData();
    }

    public function updatedCodProy(): void
    {
        $this->refreshData();
    }

    protected function refreshData(): void
    {
        $this->board = null;
        $this->columns = [];
        $this->tareasByColumn = [];

        if (!$this->codProy) {
            return;
        }

        $this->board = KanbanBoard::firstOrCreate(
            ['cod_proy' => $this->codProy],
            ['nombre' => 'Kanban ' . $this->codProy, 'activo' => true]
        );

        $this->ensureDefaultColumns();

        $cols = $this->board->columns()->get();
        $this->columns = $cols->keyBy('id_column')->toArray();

        foreach ($cols as $col) {
            $this->tareasByColumn[$col->id_column] = Tarea::where('cod_proy', $this->codProy)
                ->where('wip_column_id', $col->id_column)
                ->with('responsables')
                ->orderBy('id_tarea')
                ->get();
        }
    }

    protected function ensureDefaultColumns(): void
    {
        $existing = $this->board->columns()->pluck('nombre')->all();
        foreach ($this->defaultColumns as $def) {
            if (!in_array($def['nombre'], $existing, true)) {
                $this->board->columns()->create($def);
            }
        }
    }

    public function getProyectosProperty(): Collection
    {
        return Proyecto::query()->orderBy('cod_proy')->get(['cod_proy', 'nombre_ubicacion']);
    }

    public function getEmpleadosProperty(): Collection
    {
        if (!$this->codProy) {
            return new Collection();
        }

        $project = Proyecto::query()
            ->where('cod_proy', $this->codProy)
            ->first(['responsable_proyecto', 'supervisor_obra']);
        $extraIds = collect([$project?->responsable_proyecto, $project?->supervisor_obra])
            ->filter()
            ->unique()
            ->values();

        return Empleado::query()
            ->where(function ($query) use ($extraIds) {
                $query->whereHas('asignaciones', fn ($subQuery) => $subQuery
                    ->where('cod_proy', $this->codProy)
                    ->where(function ($statusQuery) {
                        $statusQuery
                            ->where('estado', 'activo')
                            ->orWhereNull('estado');
                    }));

                if ($extraIds->isNotEmpty()) {
                    $query->orWhereIn('cod_empleado', $extraIds->all());
                }
            })
            ->orderBy('nombre_completo')
            ->get();
    }

    public function createTarea(): void
    {
        if (!$this->board || !$this->codProy) return;
        if (!$this->nuevoTitulo) return;

        $responsablesSeleccionados = collect($this->nuevosResponsables)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($responsablesSeleccionados->isEmpty()) {
            return;
        }

        $validos = AsignacionProyecto::where('cod_proy', $this->codProy)
            ->whereIn('cod_empleado', $responsablesSeleccionados)
            ->pluck('cod_empleado')
            ->unique()
            ->values();

        if ($validos->isEmpty()) {
            return;
        }

        $entrada = $this->board->columns()->where('es_entrada', true)->orderBy('orden')->first();
        if (!$entrada) $entrada = $this->board->columns()->orderBy('orden')->first();

        $tarea = Tarea::create([
            'cod_proy' => $this->codProy,
            'titulo' => $this->nuevoTitulo,
            'estado' => 'pendiente',
            'wip_column_id' => $entrada?->id_column,
        ]);

        $tarea->responsables()->sync($validos->all());
        $tarea->loadMissing(['responsables', 'proyecto']);
        $this->notifyTaskResponsables($tarea, 'created');

        $this->refreshData();
        $this->nuevoTitulo = null;
        $this->nuevosResponsables = [];
    }

    public function updateTarea(int $id, array $data): void
    {
        $t = Tarea::where('cod_proy', $this->codProy)->find($id);
        if (!$t) return;
        $t->fill($data);
        $t->save();
        $this->refreshData();
    }

    public function deleteTarea(int $id): void
    {
        $t = Tarea::where('cod_proy', $this->codProy)->find($id);
        if ($t) $t->delete();
        $this->refreshData();
    }

    public function moveTarea(int $idTarea, int $toColumnId): void
    {
        $t = Tarea::where('cod_proy', $this->codProy)->find($idTarea);
        $col = KanbanColumn::where('board_id', $this->board?->id_board)->find($toColumnId);
        if (!$t || !$col) return;

        $t->wip_column_id = $col->id_column;
        $t->estado = $this->mapEstadoForColumn($col->nombre, $t->estado);
        $t->save();

        $this->refreshData();
    }

    protected function mapEstadoForColumn(string $nombreColumna, string $estadoActual): string
    {
        return match ($nombreColumna) {
            'Por hacer' => 'pendiente',
            'En progreso' => 'en_proceso',
            'Hecho' => 'finalizada',
            default => $estadoActual,
        };
    }

    protected function notifyTaskResponsables(Tarea $tarea, string $context): void
    {
        $resend = app(ResendMailService::class);

        $projectName = $tarea->proyecto?->nombre_ubicacion ?? $tarea->cod_proy;
        $taskTitle = $tarea->titulo;

        $subjectPrefix = match ($context) {
            'created' => 'Nueva tarea asignada',
            'updated' => 'Actualización de tarea',
            default => 'Tarea',
        };

        foreach ($tarea->responsables as $responsable) {
            if (! $responsable->email) {
                continue;
            }

            $subject = "{$subjectPrefix}: {$taskTitle}";
            $html = "<p>Hola {$responsable->nombre_completo},</p>"
                . "<p>La tarea <strong>{$taskTitle}</strong> en el proyecto "
                . "<strong>{$projectName}</strong> ha sido "
                . ($context === 'created' ? 'asignada' : 'actualizada')
                . ".</p>";

            $resend->send($responsable->email, $subject, $html);
        }
    }
}
