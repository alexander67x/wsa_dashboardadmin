<?php

namespace App\Filament\Widgets;

use App\Models\Material;
use App\Models\SolicitudMaterial;
use App\Models\StockAlmacen;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MaterialesStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 21;

    protected function getStats(): array
    {
        $totalMateriales = Material::where('activo', true)->count();

        $stockGlobal = (float) StockAlmacen::sum('cantidad_disponible');
        $stockGlobalTexto = number_format($stockGlobal, 2, '.', ' ') . ' uds.';

        $solicitudesPendientes = SolicitudMaterial::whereIn('estado', ['borrador', 'pendiente'])->count();
        $solicitudesEnviado = SolicitudMaterial::where('estado', 'enviado')->count();

        return [
            Stat::make('Materiales activos', $totalMateriales)
                ->description('En catálogo')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            Stat::make('Stock global disponible', $stockGlobalTexto)
                ->description('Suma en todos los almacenes')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('Solicitudes pendientes', $solicitudesPendientes)
                ->description('Borrador / Pendiente de aprobación')
                ->descriptionIcon('heroicon-m-clock')
                ->color($solicitudesPendientes > 0 ? 'warning' : 'success'),

            Stat::make('Solicitudes en entrega', $solicitudesEnviado)
                ->description('Estado "enviado"')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),
        ];
    }
}
