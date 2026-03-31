<?php

namespace App\Filament\Resources\ReclamosGarantia\Pages;

use App\Filament\Resources\ReclamosGarantia\ReclamoGarantiaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReclamoGarantias extends ListRecords
{
    protected static string $resource = ReclamoGarantiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo reclamo'),
        ];
    }
}
