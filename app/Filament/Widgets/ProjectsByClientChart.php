<?php

namespace App\Filament\Widgets;

use App\Models\Cliente;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class ProjectsByClientChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Proyectos por cliente (Top 5)';

    protected string $color = 'success';

    protected ?string $maxHeight = '320px';

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $clientes = Cliente::withCount('proyectos')
            ->orderByDesc('proyectos_count')
            ->limit(5)
            ->get();

        if ($clientes->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'Proyectos',
                        'data' => [],
                    ],
                ],
            ];
        }

        $labels = $clientes->pluck('nombre_cliente')->all();
        $data = $clientes->pluck('proyectos_count')->all();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Proyectos',
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
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'maxRotation' => 25,
                        'minRotation' => 0,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
