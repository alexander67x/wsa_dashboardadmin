<?php

namespace App\Filament\Resources\Almacenes\Pages;

use App\Filament\Resources\Almacenes\AlmacenResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAlmacen extends CreateRecord
{
    protected static string $resource = AlmacenResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
