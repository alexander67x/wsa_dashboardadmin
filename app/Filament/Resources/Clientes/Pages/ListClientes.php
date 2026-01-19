<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Resources\Clientes\ClienteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListClientes extends ListRecords
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo cliente'),
        ];
    }

    protected function getTableRecordUrlUsing(): ?\Closure
    {
        return fn (Model $record): string => static::getResource()::getUrl('view', ['record' => $record]);
    }
}
