<?php

namespace App\Console\Commands;

use App\Services\OneSignalService;
use Illuminate\Console\Command;

class SendOneSignalTest extends Command
{
    protected $signature = 'onesignal:test 
        {heading=Prueba OneSignal : Texto del encabezado} 
        {content=Notificación de prueba : Texto del cuerpo}';

    protected $description = 'Envía una notificación de prueba a OneSignal usando las credenciales configuradas.';

    public function handle(OneSignalService $oneSignal): int
    {
        $heading = $this->argument('heading');
        $content = $this->argument('content');

        $this->info("Enviando notificación: {$heading} - {$content}");

        $oneSignal->sendNotification($heading, $content);

        $this->info('Solicitud enviada. Revisa el panel de OneSignal o tu navegador suscrito.');

        return self::SUCCESS;
    }
}
