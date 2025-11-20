<?php

namespace App\Filament\Resources\Hitos\Pages;

use App\Filament\Resources\Hitos\HitoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHito extends EditRecord
{
    protected static string $resource = HitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
