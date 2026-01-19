<?php

namespace App\Filament\Widgets;

use App\Services\KpiService;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class LeadTimeByRoleChart extends ChartWidget
{
    protected static ?int $sort = 25;

    protected ?string $heading = 'Lead time medio por proyecto (tareas vs incidencias)';

    protected string $color = 'warning';

    protected int | string | array $columnSpan = 'full';

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

        $rows = collect($service->getLeadTimeByProject());

        // ordenar por lead time de tareas (desc)
        $rows = $rows
            ->sortByDesc(function (array $row): float {
                return (float) ($row['lead_time_tareas_dias'] ?? 0);
            })
            ->take(15); // limitar a 15 proyectos para que el gráfico sea legible

        $labels = $rows->pluck('proyecto')->all();

        $taskData = $rows
            ->pluck('lead_time_tareas_dias')
            ->map(fn ($v) => $v !== null ? round((float) $v, 2) : null)
            ->all();

        $incidentData = $rows
            ->pluck('lead_time_incidencias_dias')
            ->map(fn ($v) => $v !== null ? round((float) $v, 2) : null)
            ->all();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Lead time tareas (días)',
                    'data' => $taskData,
                ],
                [
                    'label' => 'Lead time incidencias (días)',
                    'data' => $incidentData,
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
            ],
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Días',
                    ],
                ],
            ],
        ];
    }
}
