<?php

namespace App\Filament\Widgets;

use App\Models\Proyecto;
use Filament\Widgets\Widget;

class ProjectsMapWidget extends Widget
{
    protected static ?int $sort = -1;

    /**
     * @var view-string
     */
    protected string $view = 'filament.widgets.projects-map-widget';

    public static function canView(): bool
    {
        // Ocultamos este widget del dashboard
        return false;
    }

    protected function getViewData(): array
    {
        $projects = Proyecto::with('cliente')
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get()
            ->map(function (Proyecto $p) {
                return [
                    'name' => $p->nombre_ubicacion,
                    'client' => $p->cliente?->nombre_cliente,
                    'lat' => (float) $p->latitud,
                    'lng' => (float) $p->longitud,
                    'estado' => $p->estado,
                ];
            })
            ->values()
            ->all();

        return [
            'projects' => $projects,
        ];
    }
}
