<?php

namespace App\Filament\Pages;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
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
    public ?int $nuevoResponsable = null;

    protected array $defaultColumns = [
        [ 'nombre' => 'Por hacer',   'orden' => 1, 'es_entrada' => true,  'es_salida' => false ],
        [ 'nombre' => 'En progreso', 'orden' => 2, 'es_entrada' => false, 'es_salida' => false ],
        [ 'nombre' => 'Hecho',       'orden' => 3, 'es_entrada' => false, 'es_salida' => true  ],
    ];

    public function mount(): void
    {
        $this->refreshData();
    }

    public function assignResponsable(int $idTarea, ?int $empleadoId): void
    {
        $t = Tarea::where('cod_proy', $this->codProy)->find($idTarea);
        if (!$t) return;

        if ($empleadoId) {
            $pertenece = AsignacionProyecto::where('cod_proy', $this->codProy)
                ->where('cod_empleado', $empleadoId)
                ->exists();
            if (!$pertenece) {
                return; // No asignar empleados fuera del proyecto
            }
        }

        $t->responsable_id = $empleadoId ?: null;
        $t->save();
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
        if (!$this->codProy) return new Collection();
        $proyecto = Proyecto::with('empleados')->where('cod_proy', $this->codProy)->first();
        return $proyecto?->empleados ?? new Collection();
    }

    public function createTarea(): void
    {
        if (!$this->board) return;
        if (!$this->nuevoTitulo) return;
        $responsableId = $this->nuevoResponsable;
        if (!$responsableId) return; // responsable es requerido por el esquema

        $pertenece = AsignacionProyecto::where('cod_proy', $this->codProy)
            ->where('cod_empleado', $responsableId)
            ->exists();
        if (!$pertenece) return;
        $entrada = $this->board->columns()->where('es_entrada', true)->orderBy('orden')->first();
        if (!$entrada) $entrada = $this->board->columns()->orderBy('orden')->first();

        Tarea::create([
            'cod_proy' => $this->codProy,
            'titulo' => $this->nuevoTitulo,
            'estado' => 'pendiente',
            'responsable_id' => $responsableId,
            'wip_column_id' => $entrada?->id_column,
        ]);

        $this->refreshData();
        $this->nuevoTitulo = null;
        $this->nuevoResponsable = null;
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
}
