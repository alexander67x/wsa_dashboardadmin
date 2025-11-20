<?php

namespace App\Filament\Resources\Almacenes\Pages;

use App\Filament\Resources\Almacenes\AlmacenResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlmacen extends EditRecord
{
    protected static string $resource = AlmacenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['coordenadas'] = [
            'latitude' => $data['latitud'] ?? -16.2902,
            'longitude' => $data['longitud'] ?? -63.5887,
        ];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si es almacén central, limpiar campos relacionados
        if (isset($data['tipo']) && $data['tipo'] === 'central') {
            $data['id_almacen_padre'] = null;
            $data['cod_proy'] = null;
        }

        // Si no es proyecto, limpiar cod_proy
        if (isset($data['tipo']) && $data['tipo'] !== 'proyecto') {
            $data['cod_proy'] = null;
        }

        if (! empty($data['coordenadas']) && is_array($data['coordenadas'])) {
            $data['latitud'] = $data['coordenadas']['latitude'] ?? $data['latitud'] ?? null;
            $data['longitud'] = $data['coordenadas']['longitude'] ?? $data['longitud'] ?? null;
        }

        unset($data['coordenadas']);

        return $data;
    }
}
