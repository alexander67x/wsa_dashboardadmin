<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class InventoryByProjectChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Valor de materiales por proyecto (Top 5)';

    protected string $color = 'warning';

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $rows = DB::table('stock_almacen as s')
            ->join('almacenes as a', 's.id_almacen', '=', 'a.id_almacen')
            ->join('proyectos as p', 'a.cod_proy', '=', 'p.cod_proy')
            ->join('materiales as m', 's.id_material', '=', 'm.id_material')
            ->where('a.tipo', 'proyecto')
            ->selectRaw('p.nombre_ubicacion as proyecto, SUM(s.cantidad_disponible * m.costo_unitario_promedio_bs) as total')
            ->groupBy('p.cod_proy', 'p.nombre_ubicacion')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        if ($rows->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'Valor de materiales (Bs)',
                        'data' => [],
                    ],
                ],
            ];
        }

        $labels = $rows->pluck('proyecto')->all();
        $data = $rows->pluck('total')->map(fn ($v) => (float) $v)->all();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Valor de materiales (Bs)',
                    'data' => $data,
                ],
            ],
        ];
    }
}
