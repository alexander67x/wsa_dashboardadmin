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

        return $data;
    }
}

