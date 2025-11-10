<?php

namespace App\Filament\Resources\Tareas\Pages;

use App\Filament\Resources\Tareas\TareaResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class CreateTarea extends CreateRecord
{
    protected static string $resource = TareaResource::class;

    public ?string $codProyFromFilter = null;

    public function mount(): void
    {
        parent::mount();
        
        // Obtener el proyecto desde query string o filtros
        $this->codProyFromFilter = request()->get('cod_proy')
            ?? request()->get('tableFilters')['cod_proy']['value'] ?? null;
        
        // Pre-llenar el proyecto si viene desde el contexto
        if ($this->codProyFromFilter) {
            $this->form->fill([
                'cod_proy' => $this->codProyFromFilter,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        $codProy = $this->record->cod_proy ?? $this->codProyFromFilter;
        
        if ($codProy) {
            return $this->getResource()::getUrl('index', [
                'tableFilters' => [
                    'cod_proy' => [
                        'value' => $codProy,
                    ],
                ],
            ]);
        }
        
        return $this->getResource()::getUrl('index');
    }
}

