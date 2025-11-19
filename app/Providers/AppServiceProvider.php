<?php

namespace App\Providers;

use App\Models\Incidencia;
use App\Models\ReporteAvanceTarea;
use App\Models\SolicitudMaterial;
use App\Services\OneSignalService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $oneSignal = $this->app->make(OneSignalService::class);

        Incidencia::created(function (Incidencia $incidencia) use ($oneSignal) {
            $oneSignal->sendNotification(
                heading: 'Nueva incidencia',
                content: "Incidencia #{$incidencia->id}: {$incidencia->titulo}",
            );
        });

        SolicitudMaterial::created(function (SolicitudMaterial $solicitud) use ($oneSignal) {
            $oneSignal->sendNotification(
                heading: 'Nueva solicitud de materiales',
                content: "Solicitud {$solicitud->codigo} creada.",
            );
        });

        ReporteAvanceTarea::created(function (ReporteAvanceTarea $reporte) use ($oneSignal) {
            $oneSignal->sendNotification(
                heading: 'Nuevo reporte de avance',
                content: "Reporte para tarea {$reporte->tarea_id} creado.",
            );
        });

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
