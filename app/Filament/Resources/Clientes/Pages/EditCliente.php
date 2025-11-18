<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Resources\Clientes\ClienteResource;
use App\Models\Archivo;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;

    protected ?array $documentosSubidos = null;

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
        $this->documentosSubidos = $data['documentos'] ?? null;

        unset($data['documentos']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (empty($this->documentosSubidos)) {
            return;
        }

        $record = $this->record;
        $user = auth()->user();
        $empleado = $user?->empleado;
        $creadoPor = $empleado?->cod_empleado ?? 0;

        foreach ($this->documentosSubidos as $path) {
            Archivo::create([
                'entidad' => 'clientes',
                'entidad_id' => $record->cod_cliente,
                'nombre_original' => basename($path),
                'ruta_storage' => $path,
                'tipo_mime' => null,
                'tamano_bytes' => null,
                'es_foto' => false,
                'es_evidencia_principal' => false,
                'creado_por' => $creadoPor,
            ]);
        }
    }
}
