<?php

namespace App\Filament\Widgets;

use App\Models\Proyecto;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProyectosStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Proyectos', Proyecto::count())
                ->description('Todos los proyectos')
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),
            
            Stat::make('Proyectos Activos', Proyecto::where('estado', 'activo')->count())
                ->description('En ejecución')
                ->descriptionIcon('heroicon-m-play')
                ->color('success'),
            
            Stat::make('Proyectos Completados', Proyecto::where('estado', 'completado')->count())
                ->description('Finalizados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            
            // Presupuesto Total eliminado (no se usa presupuesto_inicial)
        ];
    }
}
