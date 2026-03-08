<?php

namespace App\Filament\Resources\StockAlmacenes\Pages;

use App\Filament\Resources\StockAlmacenes\StockAlmacenResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStockAlmacen extends ViewRecord
{
    protected static string $resource = StockAlmacenResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['garantia_dias'] = $this->record->garantia_dias;
        $data['fecha_registro'] = optional($this->record->created_at)?->toDateString();
        $data['fecha_fin_garantia'] = $this->record->fecha_fin_garantia;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
