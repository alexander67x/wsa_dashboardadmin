<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Proyectos\ProyectoResource;
use App\Models\Proyecto;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProyectosStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 20;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $hoy = Carbon::today();

        $totalProyectos = Proyecto::count();
        $activos = Proyecto::where('estado', 'activo')->count();
        $completados = Proyecto::where('estado', 'completado')->count();
        $cancelados = Proyecto::where('estado', 'cancelado')->count();

        $enAtraso = Proyecto::where('estado', 'activo')
            ->whereDate('fecha_fin_estimada', '<', $hoy)
            ->count();

        $porIniciar = Proyecto::whereDate('fecha_inicio', '>', $hoy)->count();

        return [
            Stat::make('Total Proyectos', $totalProyectos)
                ->description('Todos los proyectos')
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary')
                ->url(ProyectoResource::getUrl('index')),

            Stat::make('Proyectos Activos', $activos)
                ->description('En ejecución')
                ->descriptionIcon('heroicon-m-play')
                ->color('success')
                ->url(ProyectoResource::getUrl('index')),

            Stat::make('Proyectos Completados', $completados)
                ->description('Finalizados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info')
                ->url(ProyectoResource::getUrl('index')),

            Stat::make('Proyectos en Atraso', $enAtraso)
                ->description('Activos con fecha fin vencida')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($enAtraso > 0 ? 'danger' : 'success')
                ->url(ProyectoResource::getUrl('index')),

            Stat::make('Proyectos por Iniciar', $porIniciar)
                ->description('Con fecha inicio futura')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning')
                ->url(ProyectoResource::getUrl('index')),

            Stat::make('Proyectos Cancelados', $cancelados)
                ->description('No ejecutados')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('gray')
                ->url(ProyectoResource::getUrl('index')),
        ];
    }
}
