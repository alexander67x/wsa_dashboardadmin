<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Concerns\HandlesArchivoUploads;
use App\Filament\Resources\Clientes\ClienteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCliente extends EditRecord
{
    use HandlesArchivoUploads;

    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->captureUploadedFiles('documentos');

        return $data;
    }

    protected function afterSave(): void
    {
        $documentos = $this->pullUploadedFiles('documentos');

        if (empty($documentos)) {
            return;
        }

        $record = $this->record;
        $user = auth()->user();
        $empleado = $user?->empleado;
        $creadoPor = $empleado?->cod_empleado ?? 0;

        $this->storeArchivos($documentos, [
            'entidad' => 'clientes',
            'entidad_id' => $record->cod_cliente,
            'creado_por' => $creadoPor,
            'folder' => 'clientes/documentos',
        ]);
    }
}
