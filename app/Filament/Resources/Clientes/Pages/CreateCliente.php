<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Concerns\HandlesArchivoUploads;
use App\Filament\Resources\Clientes\ClienteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCliente extends CreateRecord
{
    use HandlesArchivoUploads;

    protected static string $resource = ClienteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->captureUploadedFiles('documentos');

        return $data;
    }

    protected function afterCreate(): void
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
