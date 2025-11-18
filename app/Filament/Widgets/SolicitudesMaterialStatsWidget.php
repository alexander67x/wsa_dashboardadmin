<?php

namespace App\Filament\Widgets;

use App\Models\SolicitudMaterial;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SolicitudesMaterialStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $total = SolicitudMaterial::count();
        $pendientes = SolicitudMaterial::pendientes()->count();
        $aprobadas = SolicitudMaterial::aprobadas()->count();
        $rechazadas = SolicitudMaterial::where('estado', 'rechazada')->count();
        $recibidas = SolicitudMaterial::where('estado', 'recibida')->count();
        $urgentes = SolicitudMaterial::urgentes()->count();

        return [
            Stat::make('Solicitudes totales', $total)
                ->description('Todas las solicitudes de materiales')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Pendientes de revisión', $pendientes)
                ->description('A la espera de aprobación')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendientes > 0 ? 'warning' : 'success'),

            Stat::make('Aprobadas', $aprobadas)
                ->description('Listas para despacho')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Rechazadas', $rechazadas)
                ->description('No aprobadas')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Recibidas', $recibidas)
                ->description('Entregadas completamente')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),

            Stat::make('Urgentes', $urgentes)
                ->description('Marcadas como urgentes')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($urgentes > 0 ? 'danger' : 'gray'),
        ];
    }
}

