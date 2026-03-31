<?php

namespace App\Filament\Widgets;

use App\Services\SeguimientoService;
use App\Models\Proyecto;
use Filament\Widgets\ChartWidget;

class ProjectStatusChart extends ChartWidget
{
    protected static ?int $sort = 99;

    // KPI temporalmente deshabilitado por solicitud.
    public static function canView(): bool
    {
        return false;
    }

    protected ?string $heading = 'Avance de proyectos (tareas vs planificación)';

    protected string $color = 'primary';

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        /** @var SeguimientoService $service */
        $service = app(SeguimientoService::class);

        $tracking = $service->getTrackingSummary();
        $series = collect($tracking['projectSeries'] ?? []);

        $projects = $series
            ->map(function (array $project): array {
                $totals = $project['total'] ?? [];
                $current = ! empty($totals) ? (float) end($totals) : 0.0;

                return [
                    'nombre' => $project['nombre'] ?? $project['codigo'] ?? 'Proyecto',
                    'avance' => max(0.0, min(100.0, $current)),
                ];
            })
            ->sortByDesc('avance')
            ->values()
            ->all();

        $labels = array_map(fn ($p) => $p['nombre'], $projects);
        $values = array_map(fn ($p) => $p['avance'], $projects);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Avance de proyecto (%)',
                    'data' => $values,
                ],
            ],
        ];
    }
}
