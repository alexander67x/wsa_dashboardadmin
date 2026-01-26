<?php

namespace App\Filament\Resources\Incidencias\Pages;

use App\Filament\Resources\Incidencias\IncidenciaResource;
use Filament\Resources\Pages\ViewRecord;

class ViewIncidencia extends ViewRecord
{
    protected static string $resource = IncidenciaResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar relaciones necesarias
        $this->record->load(['proyecto', 'tarea', 'reportadoPor', 'asignadoA', 'archivos', 'historial.usuarioCambio']);
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

