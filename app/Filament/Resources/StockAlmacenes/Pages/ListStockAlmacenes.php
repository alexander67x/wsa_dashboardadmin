<?php

namespace App\Filament\Resources\StockAlmacenes\Pages;

use App\Filament\Resources\StockAlmacenes\StockAlmacenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListStockAlmacenes extends ListRecords
{
    protected static string $resource = StockAlmacenResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        if ($user?->empleado?->role?->slug === 'responsable_proyecto') {
            return [];
        }

        return [CreateAction::make()];
    }
}
