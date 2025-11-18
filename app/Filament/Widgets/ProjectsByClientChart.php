<?php

namespace App\Filament\Widgets;

use App\Models\Cliente;
use Filament\Widgets\ChartWidget;

class ProjectsByClientChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Proyectos por cliente (Top 5)';

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
}
