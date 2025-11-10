<?php

namespace App\Filament\Resources\StockAlmacenes\Pages;

use App\Filament\Resources\StockAlmacenes\StockAlmacenResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStockAlmacen extends EditRecord
{
    protected static string $resource = StockAlmacenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

