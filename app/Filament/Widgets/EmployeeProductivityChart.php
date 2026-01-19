<?php

namespace App\Filament\Widgets;

use App\Services\KpiService;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class EmployeeProductivityChart extends ChartWidget
{
    protected static ?int $sort = 26;

    protected ?string $heading = 'Empleado más productivo por proyecto';

    protected string $color = 'success';

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        /** @var KpiService $service */
        $service = app(KpiService::class);

        $projects = collect($service->getEmployeeTaskCompletionRankingByProject());

        // Para cada proyecto, tomar al empleado con más tareas finalizadas
        $topPerProject = $projects
            ->map(function (array $project): ?array {
                $empleados = collect($project['empleados'] ?? []);
                $top = $empleados->first();

                if (! $top) {
                    return null;
                }

                return [
                    'cod_proy' => $project['cod_proy'],
                    'proyecto' => $project['proyecto'],
                    'empleado_nombre' => $top['empleado_nombre'],
                    'tareas_finalizadas' => (int) ($top['tareas_finalizadas'] ?? 0),
                    'tasa_cumplimiento' => (float) ($top['tasa_cumplimiento'] ?? 0),
                ];
            })
            ->filter()
            ->sortByDesc('tareas_finalizadas')
            ->take(15)
            ->values();

        $labels = $topPerProject->map(function (array $row): string {
            return "{$row['cod_proy']} - {$row['empleado_nombre']}";
        })->all();

        $data = $topPerProject
            ->pluck('tareas_finalizadas')
            ->map(fn ($v) => (int) $v)
            ->all();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Tareas finalizadas (empleado top)',
                    'data' => $data,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|RawJs|null
     */
    protected function getOptions(): array|RawJs|null
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'callbacks' => [
                        'label' => RawJs::make('function (context) {
                            const value = context.parsed.x ?? context.parsed.y;
                            return `Tareas finalizadas: ${value}`;
                        }'),
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Tareas finalizadas',
                    ],
                ],
            ],
        ];
    }
}

