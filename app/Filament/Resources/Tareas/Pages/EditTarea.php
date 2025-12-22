<?php

namespace App\Filament\Resources\Tareas\Pages;

use App\Filament\Resources\Tareas\TareaResource;
use App\Services\ResendMailService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTarea extends EditRecord
{
    protected static string $resource = TareaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', [
            'tableFilters' => [
                'cod_proy' => [
                    'value' => $this->record->cod_proy,
                ],
            ],
        ]);
    }

    protected function afterSave(): void
    {
        $this->record->loadMissing(['responsables', 'proyecto']);

        $resend = app(ResendMailService::class);

        $projectName = $this->record->proyecto?->nombre_ubicacion ?? $this->record->cod_proy;
        $taskTitle = $this->record->titulo;

        foreach ($this->record->responsables as $responsable) {
            if (! $responsable->email) {
                continue;
            }

            $subject = "Actualización de tarea: {$taskTitle}";
            $html = "<p>Hola {$responsable->nombre_completo},</p>"
                . "<p>La tarea <strong>{$taskTitle}</strong> en el proyecto "
                . "<strong>{$projectName}</strong> ha sido actualizada.</p>";

            $resend->send($responsable->email, $subject, $html);
        }
    }
}
