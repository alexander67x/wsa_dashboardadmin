<?php

namespace App\Filament\Widgets;

use App\Models\Proyecto;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class ProjectsProgressChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Avance financiero por proyecto (Top 10)';

    protected string $color = 'info';

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $proyectos = Proyecto::whereNotNull('avance_financiero')
            ->orderByDesc('avance_financiero')
            ->limit(10)
            ->get(['nombre_ubicacion', 'avance_financiero']);

        $labels = $proyectos->pluck('nombre_ubicacion')->all();
        $data = $proyectos->pluck('avance_financiero')->map(fn ($v) => (float) $v)->all();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Avance financiero (%)',
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
            ],
            'scales' => [
                'x' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Porcentaje',
                    ],
                ],
            ],
        ];
    }
}
