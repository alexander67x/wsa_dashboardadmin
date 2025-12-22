<?php

namespace App\Filament\Resources\Tareas\Pages;

use App\Filament\Resources\Tareas\TareaResource;
use App\Services\ResendMailService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class CreateTarea extends CreateRecord
{
    protected static string $resource = TareaResource::class;

    public ?string $codProyFromFilter = null;

    public function mount(): void
    {
        parent::mount();
        
        // Obtener el proyecto desde query string o filtros
        $this->codProyFromFilter = request()->get('cod_proy')
            ?? request()->get('tableFilters')['cod_proy']['value'] ?? null;
        
        // Pre-llenar el proyecto si viene desde el contexto
        if ($this->codProyFromFilter) {
            $this->form->fill([
                'cod_proy' => $this->codProyFromFilter,
            ]);
        }
    }

    protected function afterCreate(): void
    {
        $this->record->loadMissing(['responsables', 'proyecto']);

        $resend = app(ResendMailService::class);

        $projectName = $this->record->proyecto?->nombre_ubicacion ?? $this->record->cod_proy;
        $taskTitle = $this->record->titulo;

        foreach ($this->record->responsables as $responsable) {
            if (! $responsable->email) {
                continue;
            }

            $subject = "Nueva tarea asignada: {$taskTitle}";
            $html = "<p>Hola {$responsable->nombre_completo},</p>"
                . "<p>Se te ha asignado la tarea <strong>{$taskTitle}</strong> "
                . "en el proyecto <strong>{$projectName}</strong>.</p>";

            $resend->send($responsable->email, $subject, $html);
        }
    }

    protected function getRedirectUrl(): string
    {
        $codProy = $this->record->cod_proy ?? $this->codProyFromFilter;
        
        if ($codProy) {
            return $this->getResource()::getUrl('index', [
                'tableFilters' => [
                    'cod_proy' => [
                        'value' => $codProy,
                    ],
                ],
            ]);
        }
        
        return $this->getResource()::getUrl('index');
    }
}
