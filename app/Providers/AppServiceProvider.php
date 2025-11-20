<?php

namespace App\Providers;

use App\Models\Incidencia;
use App\Models\ReporteAvanceTarea;
use App\Models\SolicitudMaterial;
use App\Models\Empleado;
use App\Models\User;
use App\Services\OneSignalService;
use App\Services\ExpoPushService;
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
                url: route('filament.admin.resources.incidencias.view', $incidencia),
            );
        });

        SolicitudMaterial::created(function (SolicitudMaterial $solicitud) use ($oneSignal) {
            $oneSignal->sendNotification(
                heading: 'Nueva solicitud de materiales',
                content: "Solicitud {$solicitud->codigo} creada.",
                url: route('filament.admin.resources.solicitudes-materiales.solicitud-materials.view', $solicitud),
            );
        });

        ReporteAvanceTarea::created(function (ReporteAvanceTarea $reporte) use ($oneSignal) {
            $oneSignal->sendNotification(
                heading: 'Nuevo reporte de avance',
                content: "Reporte para tarea {$reporte->tarea_id} creado.",
                url: route('filament.admin.resources.reportes.view', $reporte),
            );
        });

        // Expo push notifications for mobile app
        ReporteAvanceTarea::updated(function (ReporteAvanceTarea $reporte) {
            $originalEstado = $reporte->getOriginal('estado');
            $nuevoEstado = $reporte->estado;

            if ($originalEstado === $nuevoEstado) {
                return;
            }

            if (! in_array($nuevoEstado, ['aprobado', 'rechazado'], true)) {
                return;
            }

            $empleado = $reporte->registradoPor;
            $user = $empleado?->user;

            if (! $user) {
                return;
            }

            foreach ($user->devices as $device) {
                ExpoPushService::send(
                    $device->expo_token,
                    $nuevoEstado === 'aprobado' ? 'Reporte aprobado' : 'Reporte rechazado',
                    "Tu reporte \"{$reporte->titulo}\" fue {$nuevoEstado}.",
                    [
                        'type' => 'report',
                        'id' => (string) $reporte->getKey(),
                        'status' => $nuevoEstado,
                    ],
                );
            }
        });

        SolicitudMaterial::updated(function (SolicitudMaterial $solicitud) {
            $originalEstado = $solicitud->getOriginal('estado');
            $nuevoEstado = $solicitud->estado;

            if ($originalEstado === $nuevoEstado) {
                return;
            }

            if (! in_array($nuevoEstado, ['aprobada', 'rechazada'], true)) {
                return;
            }

            $empleado = $solicitud->solicitadoPor;
            $user = $empleado?->user;

            if (! $user) {
                return;
            }

            foreach ($user->devices as $device) {
                ExpoPushService::send(
                    $device->expo_token,
                    $nuevoEstado === 'aprobada' ? 'Solicitud aprobada' : 'Solicitud rechazada',
                    "Tu solicitud {$solicitud->numero_solicitud} fue {$nuevoEstado}.",
                    [
                        'type' => 'material_request',
                        'id' => (string) $solicitud->getKey(),
                        'status' => $nuevoEstado,
                    ],
                );
            }
        });

        Incidencia::updated(function (Incidencia $incidencia) {
            $originalEstado = $incidencia->getOriginal('estado');
            $nuevoEstado = $incidencia->estado;

            if ($originalEstado === $nuevoEstado) {
                return;
            }

            if (! in_array($nuevoEstado, ['resuelta', 'cerrada'], true)) {
                return;
            }

            $empleado = $incidencia->reportadoPor;
            $user = $empleado?->user;

            if (! $user) {
                return;
            }

            foreach ($user->devices as $device) {
                ExpoPushService::send(
                    $device->expo_token,
                    'Incidencia actualizada',
                    "La incidencia \"{$incidencia->titulo}\" ahora está {$nuevoEstado}.",
                    [
                        'type' => 'incident',
                        'id' => (string) $incidencia->getKey(),
                        'status' => $nuevoEstado,
                    ],
                );
            }
        });

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
