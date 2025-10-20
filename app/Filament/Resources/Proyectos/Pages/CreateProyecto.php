<?php

namespace App\Filament\Resources\Proyectos\Pages;

use App\Filament\Resources\Proyectos\ProyectoResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

use Illuminate\Support\Arr;

use Illuminate\Database\Eloquent\Model;

class CreateProyecto extends CreateRecord
{
    protected static string $resource = ProyectoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Mapear coordenadas del MapPicker al esquema de la tabla proyectos
        if (!empty($data['coordenadas']) && is_array($data['coordenadas'])) {
            $data['latitud'] = $data['coordenadas']['latitude'] ?? $data['latitud'] ?? null;
            $data['longitud'] = $data['coordenadas']['longitude'] ?? $data['longitud'] ?? null;
        }

        // Normalizar nombre del campo direccion (formularios anteriores usaban direccion_ubicacion)
        if (isset($data['direccion_ubicacion']) && !isset($data['direccion'])) {
            $data['direccion'] = $data['direccion_ubicacion'];
            unset($data['direccion_ubicacion']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // sincronizar empleados si vienen en el formulario
        $state = $this->form->getState();
        $empleados = $state['empleados'] ?? [];
        if (!empty($empleados) && $this->record instanceof Model) {
            // Construir array para sync con datos pivot: usar la fecha de inicio del proyecto
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
