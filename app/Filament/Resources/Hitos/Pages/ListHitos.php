<?php

namespace App\Filament\Resources\Hitos\Pages;

use App\Filament\Resources\Hitos\HitoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHitos extends ListRecords
{
    protected static string $resource = HitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo hito'),
        ];
    }
}
