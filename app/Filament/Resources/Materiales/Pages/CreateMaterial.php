<?php

namespace App\Filament\Resources\Materiales\Pages;

use App\Filament\Resources\Materiales\MaterialResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMaterial extends CreateRecord
{
    protected static string $resource = MaterialResource::class;

    protected function afterCreate(): void
    {
        // Sincronizar almacenes si vienen en el formulario
        $state = $this->form->getState();
        $almacenes = $state['almacenes'] ?? [];
        
        if (!empty($almacenes) && $this->record instanceof Model) {
            // Sincronizar almacenes usando la relación many-to-many
            // Filament maneja automáticamente la relación, pero necesitamos crear los registros en stock_almacen
            $syncData = [];
            foreach ($almacenes as $almacenId) {
                $syncData[$almacenId] = [
                    'cantidad_disponible' => 0,
                    'cantidad_reservada' => 0,
                    'cantidad_minima_alerta' => $this->record->stock_minimo ?? 0,
                ];
            }
            $this->record->almacenes()->sync($syncData);
        }
    }
}

