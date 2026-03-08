<?php

namespace App\Filament\Resources\Fases\Pages;

use App\Filament\Resources\Fases\FaseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFase extends ViewRecord
{
    protected static string $resource = FaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
