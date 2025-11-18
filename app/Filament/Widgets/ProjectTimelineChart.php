<?php

namespace App\Filament\Widgets;

use App\Models\Proyecto;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ProjectTimelineChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Cronograma global de proyectos';

    protected string $color = 'secondary';

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $proyectos = Proyecto::whereNotNull('fecha_inicio')
            ->whereNotNull('fecha_fin_estimada')
            ->orderBy('fecha_inicio')
            ->limit(12)
            ->get(['nombre_ubicacion', 'fecha_inicio', 'fecha_fin_estimada']);

        $labels = [];
        $duraciones = [];
        $transcurrido = [];

        $hoy = Carbon::today();

        foreach ($proyectos as $proyecto) {
            $inicio = Carbon::parse($proyecto->fecha_inicio);
            $fin = Carbon::parse($proyecto->fecha_fin_estimada);

            $duracion = max(1, $inicio->diffInDays($fin));
            $pasados = $inicio->isAfter($hoy) ? 0 : min($duracion, $inicio->diffInDays($hoy));

            $labels[] = $proyecto->nombre_ubicacion;
            $duraciones[] = $duracion;
            $transcurrido[] = $pasados;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Duración (días)',
                    'data' => $duraciones,
                    'backgroundColor' => 'rgba(148, 163, 184, 0.6)', // slate-400
                ],
                [
                    'label' => 'Días transcurridos',
                    'data' => $transcurrido,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)', // green-500
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
