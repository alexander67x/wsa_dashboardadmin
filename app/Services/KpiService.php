<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Proyecto;
use App\Models\Incidencia;
use App\Models\Tarea;
use Illuminate\Support\Collection;

class KpiService
{
    /**
     * Lead time medio (días) por proyecto:
     *  - Tareas: fecha_fin - fecha_inicio (tareas finalizadas).
     *  - Incidencias: fecha_resolucion - fecha_reportado (incidencias resueltas).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLeadTimeByProject(): array
    {
        /** @var Collection<int, Proyecto> $proyectos */
        $proyectos = Proyecto::query()
            ->with(['tareas' => function ($query) {
                $query
                    ->select('id_tarea', 'cod_proy', 'fecha_inicio', 'fecha_fin', 'estado')
                    ->whereNotNull('fecha_inicio')
                    ->whereNotNull('fecha_fin')
                    ->where('estado', 'finalizada');
            }])
            ->get(['cod_proy', 'nombre_ubicacion', 'descripcion']);

        /** @var Collection<int, Incidencia> $incidencias */
        $incidencias = Incidencia::query()
            ->select('cod_proy', 'fecha_reportado', 'fecha_resolucion')
            ->whereNotNull('fecha_reportado')
            ->whereNotNull('fecha_resolucion')
            ->get();

        $incidenciasByProject = $incidencias->groupBy('cod_proy');

        $result = [];

        foreach ($proyectos as $proyecto) {
            $nombreProyecto = $proyecto->nombre_ubicacion ?? $proyecto->descripcion ?? $proyecto->cod_proy;

            /** @var Collection<int, Tarea> $tareas */
            $tareas = $proyecto->tareas;

            $totalTaskDays = 0;
            $taskCount = 0;

            foreach ($tareas as $tarea) {
                if (! $tarea->fecha_inicio || ! $tarea->fecha_fin) {
                    continue;
                }

                $inicio = $tarea->fecha_inicio;
                $fin = $tarea->fecha_fin;

                // Si por error de datos la fecha fin es anterior a la de inicio,
                // consideramos lead time 0 para no generar valores negativos.
                if ($fin->lessThan($inicio)) {
                    $dias = 0;
                } else {
                    $dias = $inicio->diffInDays($fin) + 1;
                }

                $totalTaskDays += $dias;
                $taskCount++;
            }

            /** @var Collection<int, Incidencia> $incPorProyecto */
            $incPorProyecto = $incidenciasByProject->get($proyecto->cod_proy, collect());

            $totalIncidentDays = 0;
            $incidentCount = 0;

            foreach ($incPorProyecto as $incidencia) {
                if (! $incidencia->fecha_reportado || ! $incidencia->fecha_resolucion) {
                    continue;
                }

                $inicio = $incidencia->fecha_reportado;
                $fin = $incidencia->fecha_resolucion;

                if ($fin->lessThan($inicio)) {
                    $dias = 0;
                } else {
                    $dias = $inicio->diffInDays($fin) + 1;
                }

                $totalIncidentDays += $dias;
                $incidentCount++;
            }

            if ($taskCount === 0 && $incidentCount === 0) {
                continue;
            }

            $avgTaskLeadTime = $taskCount > 0 ? round($totalTaskDays / $taskCount, 2) : null;
            $avgIncidentLeadTime = $incidentCount > 0 ? round($totalIncidentDays / $incidentCount, 2) : null;

            $result[] = [
                'cod_proy' => $proyecto->cod_proy,
                'proyecto' => $nombreProyecto,
                'lead_time_tareas_dias' => $avgTaskLeadTime,
                'lead_time_incidencias_dias' => $avgIncidentLeadTime,
                'tareas_contemplacion' => $taskCount,
                'incidencias_contemplacion' => $incidentCount,
            ];
        }

        return $result;
    }

    /**
     * Ranking simple de productividad por proyecto:
     *  - Cuenta cuántas tareas finalizadas tiene cada empleado en cada proyecto.
     *  - Incluye también el total de tareas asignadas para calcular una tasa de cumplimiento.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEmployeeTaskCompletionRankingByProject(): array
    {
        /** @var Collection<int, Proyecto> $proyectos */
        $proyectos = Proyecto::query()
            ->with(['tareas' => function ($query) {
                $query
                    ->select('id_tarea', 'cod_proy', 'estado')
                    ->with(['responsables' => function ($q) {
                        $q->select('empleados.cod_empleado', 'empleados.nombre_completo');
                    }]);
            }])
            ->get(['cod_proy', 'nombre_ubicacion', 'descripcion']);

        $result = [];

        foreach ($proyectos as $proyecto) {
            /** @var Collection<int, Tarea> $tareas */
            $tareas = $proyecto->tareas;

            if ($tareas->isEmpty()) {
                continue;
            }

            // [empleado_id => ['nombre' => ..., 'asignadas' => int, 'finalizadas' => int]]
            $byEmployee = [];

            foreach ($tareas as $tarea) {
                /** @var Collection<int, Empleado> $responsables */
                $responsables = $tarea->responsables;

                if ($responsables->isEmpty()) {
                    continue;
                }

                foreach ($responsables as $empleado) {
                    $id = $empleado->cod_empleado;
                    $nombre = $empleado->nombre_completo ?? ('Empleado '.$id);

                    if (! isset($byEmployee[$id])) {
                        $byEmployee[$id] = [
                            'empleado_id' => $id,
                            'empleado_nombre' => $nombre,
                            'tareas_asignadas' => 0,
                            'tareas_finalizadas' => 0,
                        ];
                    }

                    $byEmployee[$id]['tareas_asignadas']++;

                    if ($tarea->estado === 'finalizada') {
                        $byEmployee[$id]['tareas_finalizadas']++;
                    }
                }
            }

            if (empty($byEmployee)) {
                continue;
            }

            // Calcular tasa de cumplimiento y ordenar ranking (más tareas finalizadas primero)
            $empleados = collect($byEmployee)
                ->map(function (array $row): array {
                    $asignadas = max((int) $row['tareas_asignadas'], 1);
                    $finalizadas = (int) $row['tareas_finalizadas'];

                    $row['tasa_cumplimiento'] = round(($finalizadas / $asignadas) * 100, 2);

                    return $row;
                })
                ->sortByDesc('tareas_finalizadas')
                ->values()
                ->all();

            $nombreProyecto = $proyecto->nombre_ubicacion ?? $proyecto->descripcion ?? $proyecto->cod_proy;

            $result[] = [
                'cod_proy' => $proyecto->cod_proy,
                'proyecto' => $nombreProyecto,
                'empleados' => $empleados,
            ];
        }

        return $result;
    }
}
