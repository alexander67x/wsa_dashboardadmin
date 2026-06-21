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

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '480px';

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
                    'barPercentage' => 0.9,
                    'categoryPercentage' => 0.7,
                ],
                [
                    'label' => 'Días transcurridos',
                    'data' => $transcurrido,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)', // green-500
                    'barPercentage' => 0.9,
                    'categoryPercentage' => 0.7,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|RawJs|null
     */
    protected function getOptions(): array|RawJs|null
    {
        return RawJs::make(<<<'JS'
{
  maintainAspectRatio: false,
  indexAxis: 'y',
  resizeDelay: 200,
  onResize: function (chart) {
    if (chart.canvas && chart.canvas.parentNode) {
      chart.canvas.parentNode.style.height = '340px';
    }
  },
  layout: {
    padding: {
      top: 20,
      right: 32,
      bottom: 16,
      left: 12
    }
  },
  scales: {
    y: {
      ticks: {
        autoSkip: false,
        padding: 12
      },
      grid: {
        offset: true
      }
    },
    x: {
      title: {
        display: true,
        text: 'Días'
      },
      ticks: {
        padding: 8
      }
    }
  }
}
JS);
    }
}
