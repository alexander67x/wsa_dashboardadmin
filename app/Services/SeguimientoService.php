<?php

namespace App\Services;

use App\Models\Proyecto;
use App\Models\Tarea;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SeguimientoService
{
    public function getTrackingSummary(): array
    {
        $proyectos = Proyecto::query()
            ->with([
                'tareas:id_tarea,cod_proy,fecha_inicio,fecha_fin,estado',
                'planificacionesSemanales:id_plan,cod_proy,semana,año,avance_esperado_porcentaje,created_at',
                'planificacionesSemanales.ejecuciones:id_ejecucion,id_plan,avance_real_porcentaje',
            ])
            ->orderBy('cod_proy')
            ->get([
                'cod_proy',
                'nombre_ubicacion',
                'descripcion',
            ]);

        $projectOptions = $proyectos->map(function (Proyecto $proyecto) {
            $nombre = $proyecto->nombre_ubicacion ?? $proyecto->descripcion ?? $proyecto->cod_proy;

            return [
                'value' => $proyecto->cod_proy,
                'label' => "{$nombre} ({$proyecto->cod_proy})",
            ];
        })->values();

        $series = $proyectos->map(function (Proyecto $proyecto) {
            return $this->buildProjectSeries($proyecto);
        })->values();

        $defaultProject = data_get($projectOptions->first(), 'value');

        return [
            'projectOptions' => $projectOptions->toArray(),
            'projectSeries' => $series->toArray(),
            'defaultProject' => $defaultProject,
        ];
    }

    public function getGanttData(): array
    {
        $proyectos = Proyecto::query()
            ->with([
                'tareas:id_tarea,cod_proy,titulo,fecha_inicio,fecha_fin,estado',
            ])
            ->orderBy('cod_proy')
            ->get([
                'cod_proy',
                'nombre_ubicacion',
                'descripcion',
            ]);

        $projectOptions = $proyectos->map(function (Proyecto $proyecto) {
            $nombre = $proyecto->nombre_ubicacion ?? $proyecto->descripcion ?? $proyecto->cod_proy;

            return [
                'value' => $proyecto->cod_proy,
                'label' => "{$nombre} ({$proyecto->cod_proy})",
            ];
        })->values();

        $tasksByProject = $proyectos->mapWithKeys(function (Proyecto $proyecto) {
            $nombre = $proyecto->nombre_ubicacion ?? $proyecto->descripcion ?? $proyecto->cod_proy;

            return [
                $proyecto->cod_proy => [
                    'name' => $nombre,
                    'tasks' => $proyecto->tareas
                        ->map(fn (Tarea $tarea) => $this->mapTaskForGantt($tarea))
                        ->filter()
                        ->values()
                        ->all(),
                ],
            ];
        });

        return [
            'projectOptions' => $projectOptions->toArray(),
            'projectTasks' => $tasksByProject->toArray(),
            'defaultProject' => data_get($projectOptions->first(), 'value'),
        ];
    }

    private function buildProjectSeries(Proyecto $proyecto): array
    {
        $planificaciones = $proyecto->planificacionesSemanales
            ? $proyecto->planificacionesSemanales
                ->sortBy(fn ($plan) => sprintf('%04d-%02d', $plan->año, $plan->semana))
                ->values()
            : collect();

        if ($planificaciones->isEmpty()) {
            $planificaciones = $this->synthesizePlansFromTasks($proyecto);
        }

        $hasMultipleYears = $planificaciones->pluck('año')->unique()->count() > 1;
        $totalTareas = max($proyecto->tareas->count(), 1);
        $labels = [];
        $weeklyPercentages = [];
        $totalPercentages = [];
        $curvePercentages = [];
        $detail = [];
        $acumuladoCompletadas = 0;

        foreach ($planificaciones as $plan) {
            $weekPeriod = $this->getWeekPeriod((int) $plan->año, (int) $plan->semana);
            $plannedTasks = $this->countTasksForPeriod($proyecto->tareas, $weekPeriod['start'], $weekPeriod['end'], 'planned');
            $completedTasks = $this->countTasksForPeriod($proyecto->tareas, $weekPeriod['start'], $weekPeriod['end'], 'completed');

            $weeklyPercent = $plannedTasks > 0
                ? round(($completedTasks / $plannedTasks) * 100, 2)
                : 0.0;

            $acumuladoCompletadas += $completedTasks;
            $totalPercent = round(min(100, ($acumuladoCompletadas / $totalTareas) * 100), 2);

            $realLine = null;
            if (isset($plan->ejecuciones)) {
                $realLine = $plan->ejecuciones->avg('avance_real_porcentaje');
            }
            $curvePercent = $realLine !== null
                ? round((float) $realLine, 2)
                : $totalPercent;

            $label = sprintf('Semana %02d', $plan->semana);
            if ($hasMultipleYears) {
                $label .= " ({$plan->año})";
            }

            $labels[] = $label;
            $weeklyPercentages[] = $weeklyPercent;
            $totalPercentages[] = $totalPercent;
            $curvePercentages[] = $curvePercent;

            $detail[] = [
                'semana' => $label,
                'planificado' => round((float) ($plan->avance_esperado_porcentaje ?? 0), 2),
                'avance_real' => $realLine !== null ? round((float) $realLine, 2) : null,
                'tareas_planificadas' => $plannedTasks,
                'tareas_completadas' => $completedTasks,
                'cumplimiento_tareas' => $weeklyPercent,
                'avance_total' => $totalPercent,
            ];
        }

        $nombre = $proyecto->nombre_ubicacion ?? $proyecto->descripcion ?? $proyecto->cod_proy;

        return [
            'codigo' => $proyecto->cod_proy,
            'nombre' => $nombre,
            'labels' => $labels,
            'weekly' => $weeklyPercentages,
            'total' => $totalPercentages,
            'curve' => $curvePercentages,
            'detail' => $detail,
        ];
    }

    private function synthesizePlansFromTasks(Proyecto $proyecto): Collection
    {
        if ($proyecto->tareas->isEmpty()) {
            return collect();
        }

        return $proyecto->tareas
            ->filter(fn ($tarea) => $tarea->fecha_inicio !== null)
            ->map(function ($tarea) {
                $inicio = $tarea->fecha_inicio instanceof Carbon
                    ? $tarea->fecha_inicio
                    : Carbon::parse($tarea->fecha_inicio);
                $tarea->fecha_inicio_cache = $inicio;

                return $tarea;
            })
            ->groupBy(fn ($tarea) => $tarea->fecha_inicio_cache->format('o-W'))
            ->sortKeys()
            ->map(function (Collection $group) {
                /** @var \App\Models\Tarea $first */
                $first = $group->first();
                $inicio = $first->fecha_inicio_cache ?? $first->fecha_inicio;

                if ($inicio instanceof Carbon === false) {
                    $inicio = Carbon::parse($inicio);
                }

                return (object) [
                    'semana' => (int) $inicio->isoWeek(),
                    'año' => (int) $inicio->isoWeekYear(),
                    'avance_esperado_porcentaje' => 0,
                    'ejecuciones' => collect(),
                ];
            })
            ->values();
    }

    private function getWeekPeriod(int $year, int $week): array
    {
        $start = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $end = (clone $start)->endOfWeek();

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    private function countTasksForPeriod(Collection $tareas, Carbon $inicio, Carbon $fin, string $mode): int
    {
        return $tareas->filter(function ($tarea) use ($inicio, $fin, $mode) {
            if ($mode === 'planned' && $tarea->fecha_inicio) {
                return $this->dateBetween($tarea->fecha_inicio, $inicio, $fin);
            }

            if ($mode === 'completed' && $tarea->estado === 'finalizada' && $tarea->fecha_fin) {
                return $this->dateBetween($tarea->fecha_fin, $inicio, $fin);
            }

            return false;
        })->count();
    }

    private function dateBetween($fecha, Carbon $inicio, Carbon $fin): bool
    {
        $value = $fecha instanceof Carbon ? $fecha : Carbon::parse($fecha);

        return $value->betweenIncluded($inicio, $fin);
    }

    private function mapTaskForGantt(Tarea $tarea): ?array
    {
        if (! $tarea->fecha_inicio) {
            return null;
        }

        $inicio = $tarea->fecha_inicio instanceof Carbon
            ? $tarea->fecha_inicio->copy()
            : Carbon::parse($tarea->fecha_inicio);

        $fin = $tarea->fecha_fin instanceof Carbon
            ? $tarea->fecha_fin->copy()
            : ($tarea->fecha_fin ? Carbon::parse($tarea->fecha_fin) : $inicio->copy());

        if ($fin->lessThan($inicio)) {
            $fin = $inicio->copy();
        }

        return [
            'id' => (string) $tarea->getKey(),
            'name' => $tarea->titulo ?: 'Tarea '.$tarea->getKey(),
            'start' => $inicio->format('Y-m-d'),
            'end' => $fin->format('Y-m-d'),
            'status' => $tarea->estado ?? 'sin estado',
            'progress' => $this->inferProgress($tarea->estado),
            'duration_days' => $inicio->diffInDays($fin) + 1,
        ];
    }

    private function inferProgress(?string $estado): int
    {
        return match (strtolower((string) $estado)) {
            'finalizada', 'completada', 'cerrada' => 100,
            'en progreso', 'en_progreso', 'proceso' => 65,
            'pendiente', 'por iniciar', 'por_iniciar' => 10,
            'bloqueada', 'pausada' => 35,
            default => 45,
        };
    }
}
