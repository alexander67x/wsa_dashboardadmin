<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Tareas\TareaResource;
use App\Models\Tarea;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class SeguimientoCalendarWidget extends FullCalendarWidget
{
    protected static ?string $heading = 'Calendario de tareas';

    protected int | string | array $columnSpan = 'full';
    public ?string $project = null;

    public static function canView(): bool
    {
        // Ocultar este widget del dashboard principal.
        return false;
    }

    public function mount(?string $project = null): void
    {
        $this->project = $project;
    }

    protected function headerActions(): array
    {
        return [
            Action::make('create')
                ->label('Crear tarea')
                ->icon('heroicon-o-plus')
                ->url(TareaResource::getUrl('create')),
        ];
    }

    /**
     * Fetch tasks within the requested range and expose them as calendar events.
     *
     * @param  array{start: string, end: string}  $info
     */
    public function fetchEvents(array $info): array
    {
        $rangeStart = CarbonImmutable::parse($info['start'])->startOfDay();
        $rangeEnd = CarbonImmutable::parse($info['end'])->endOfDay();

        return $this->baseQuery($rangeStart, $rangeEnd)
            ->get()
            ->map(fn (Tarea $tarea) => $this->mapTaskToEvent($tarea))
            ->filter()
            ->values()
            ->toArray();
    }

    protected function baseQuery(CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return Tarea::query()
            ->select([
                'id_tarea',
                'cod_proy',
                'titulo',
                'estado',
                'fecha_inicio',
                'fecha_fin',
            ])
            ->whereNotNull('fecha_inicio')
            ->when($this->project, fn (Builder $query, string $project) => $query->where('cod_proy', $project))
            ->where(function (Builder $query) use ($start, $end) {
                $query
                    ->whereBetween('fecha_inicio', [$start, $end])
                    ->orWhereBetween('fecha_fin', [$start, $end])
                    ->orWhere(function (Builder $overlapping) use ($start, $end) {
                        $overlapping
                            ->where('fecha_inicio', '<=', $start)
                            ->where(function (Builder $nested) use ($start, $end) {
                                $nested
                                    ->where('fecha_fin', '>=', $start)
                                    ->orWhereNull('fecha_fin');
                            });
                    });
            })
            ->orderBy('fecha_inicio');
    }

    protected function mapTaskToEvent(Tarea $tarea): ?array
    {
        $start = $tarea->fecha_inicio?->toDateString();

        if (! $start) {
            return null;
        }

        $endReference = $tarea->fecha_fin instanceof CarbonInterface
            ? $tarea->fecha_fin
            : $tarea->fecha_inicio;

        if ($endReference?->lessThan($tarea->fecha_inicio)) {
            $endReference = $tarea->fecha_inicio;
        }

        $end = $endReference?->copy()->addDay()->toDateString();
        $color = $this->determineColor($tarea->estado);
        $titleSuffix = $tarea->cod_proy ? " · {$tarea->cod_proy}" : '';
        $title = trim(($tarea->titulo ?? 'Tarea') . $titleSuffix);

        return EventData::make()
            ->id((string) $tarea->getKey())
            ->title($title)
            ->start($start)
            ->end($end)
            ->allDay()
            ->url(TareaResource::getUrl('edit', ['record' => $tarea]))
            ->backgroundColor($color)
            ->borderColor($color)
            ->extendedProps([
                'estado' => $tarea->estado,
                'proyecto' => $tarea->cod_proy,
            ])
            ->toArray();
    }

    protected function determineColor(?string $estado): string
    {
        return match (strtolower($estado ?? '')) {
            'pendiente', 'por iniciar' => '#f97316',
            'en progreso', 'en_progreso', 'proceso' => '#3b82f6',
            'finalizada', 'completada' => '#10b981',
            'bloqueada', 'pausada' => '#ef4444',
            default => '#6366f1',
        };
    }

    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'height' => 'auto',
            'dayMaxEvents' => true,
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,listWeek',
            ],
            'buttonText' => [
                'today' => 'Hoy',
                'month' => 'Mes',
                'week' => 'Semana',
                'day' => 'Día',
                'list' => 'Agenda',
            ],
        ];
    }
}
