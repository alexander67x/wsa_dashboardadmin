<?php

namespace App\Filament\Resources\Tareas\Pages;

use App\Filament\Resources\Tareas\TareaResource;
use App\Models\Proyecto;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTareas extends ListRecords
{
    protected static string $resource = TareaResource::class;

    public ?string $selectedProyecto = null;

    public function getView(): string
    {
        return 'filament.resources.tareas.list-records';
    }

    public function mount(): void
    {
        parent::mount();
        
        // Obtener proyecto de query string o filtro
        $codProy = request()->get('cod_proy');
        $filters = request()->get('tableFilters', []);
        
        $this->selectedProyecto = $codProy 
            ?? ($filters['cod_proy']['value'] ?? null);
    }

    protected function getHeaderActions(): array
    {
        $codProy = $this->selectedProyecto ?? request()->get('tableFilters')['cod_proy']['value'] ?? null;
        
        return [
            CreateAction::make()
                ->label('Nueva Tarea')
                ->url(function () use ($codProy) {
                    $url = $this->getResource()::getUrl('create');
                    if ($codProy) {
                        $url .= '?cod_proy=' . $codProy;
                    }
                    return $url;
                })
                ->disabled(!$codProy)
                ->tooltip($codProy ? 'Crear nueva tarea para el proyecto seleccionado' : 'Primero selecciona un proyecto'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getProyectoProperty(): ?Proyecto
    {
        if (!$this->selectedProyecto) {
            return null;
        }
        
        return Proyecto::where('cod_proy', $this->selectedProyecto)->first();
    }
}
