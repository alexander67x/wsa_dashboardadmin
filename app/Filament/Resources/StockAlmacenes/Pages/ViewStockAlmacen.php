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
        $data['cantidad_disponible'] = $this->formatNumber($this->record->cantidad_disponible);
        $data['cantidad_reservada'] = $this->formatNumber($this->record->cantidad_reservada);
        $data['cantidad_minima_alerta'] = $this->formatNumber($this->record->cantidad_minima_alerta);
        $data['garantia_dias'] = $this->record->garantia_dias;
        $data['fecha_registro'] = optional($this->record->created_at)?->toDateString();
        $data['fecha_fin_garantia'] = $this->record->fecha_fin_garantia;
        $data['stock_usable'] = $this->formatNumber(
            max(0, (float) $this->record->cantidad_disponible - (float) $this->record->cantidad_reservada),
        );

        return $data;
    }

    private function formatNumber(mixed $value): string
    {
        $normalized = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');

        return $normalized === '' ? '0' : $normalized;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
