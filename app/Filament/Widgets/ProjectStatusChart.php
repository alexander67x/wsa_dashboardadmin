<?php

namespace App\Filament\Widgets;

use App\Models\Proyecto;
use Filament\Widgets\ChartWidget;

class ProjectStatusChart extends ChartWidget
{
    protected static ?int $sort = 99;

    protected ?string $heading = 'Estado de proyectos';

    protected string $color = 'primary';

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $estados = [
            'activo' => 'Activo',
            'completado' => 'Completado',
            'pausado' => 'Pausado',
            'cancelado' => 'Cancelado',
        ];

        $labels = [];
        $values = [];

        foreach ($estados as $key => $label) {
            $labels[] = $label;
            $values[] = Proyecto::where('estado', $key)->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Cantidad de proyectos',
                    'data' => $values,
                ],
            ],
        ];
    }
}
