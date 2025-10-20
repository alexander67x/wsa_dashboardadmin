<?php

namespace App\Filament\Resources\Proyectos\Pages;

use App\Filament\Resources\Proyectos\ProyectoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProyecto extends EditRecord
{
    protected static string $resource = ProyectoResource::class;

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
        if (!empty($data['coordenadas']) && is_array($data['coordenadas'])) {
            $data['latitud'] = $data['coordenadas']['latitude'] ?? $data['latitud'] ?? null;
            $data['longitud'] = $data['coordenadas']['longitude'] ?? $data['longitud'] ?? null;
        }

        if (isset($data['direccion_ubicacion']) && !isset($data['direccion'])) {
            $data['direccion'] = $data['direccion_ubicacion'];
            unset($data['direccion_ubicacion']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();
        $empleados = $state['empleados'] ?? [];
        if (!empty($empleados) && $this->record instanceof Model) {
            $fechaInicioProyecto = $this->record->fecha_inicio ?? now();
            $syncData = [];
            foreach ($empleados as $empleadoId) {
                $syncData[$empleadoId] = [
                    'fecha_inicio_asignacion' => $fechaInicioProyecto,
                    // Valor por defecto para rol en la asignación
                    'rol_en_proyecto' => 'asignado',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $this->record->empleados()->sync($syncData);
        }
    }
}
