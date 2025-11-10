<?php

namespace App\Filament\Resources\Materiales\Pages;

use App\Filament\Resources\Materiales\MaterialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMaterial extends EditRecord
{
    protected static string $resource = MaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Sincronizar almacenes si vienen en el formulario
        $state = $this->form->getState();
        $almacenes = $state['almacenes'] ?? [];
        
        if ($this->record instanceof Model) {
            // Obtener almacenes actuales con sus datos pivot
            $almacenesActuales = $this->record->almacenes()->get();
            $syncData = [];
            
            // Preparar datos para sincronizar
            foreach ($almacenes as $almacenId) {
                $almacenActual = $almacenesActuales->firstWhere('id_almacen', $almacenId);
                
                if ($almacenActual) {
                    // Mantener los valores existentes
                    $syncData[$almacenId] = [
                        'cantidad_disponible' => $almacenActual->pivot->cantidad_disponible ?? 0,
                        'cantidad_reservada' => $almacenActual->pivot->cantidad_reservada ?? 0,
                        'cantidad_minima_alerta' => $almacenActual->pivot->cantidad_minima_alerta ?? $this->record->stock_minimo ?? 0,
                    ];
                } else {
                    // Nuevo almacén, valores por defecto
                    $syncData[$almacenId] = [
                        'cantidad_disponible' => 0,
                        'cantidad_reservada' => 0,
                        'cantidad_minima_alerta' => $this->record->stock_minimo ?? 0,
                    ];
                }
            }
            
            // Sincronizar almacenes
            $this->record->almacenes()->sync($syncData);
        }
    }
}

