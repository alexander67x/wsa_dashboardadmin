<?php

namespace App\Filament\Resources\Proyectos\RelationManagers;

use App\Filament\Resources\Proyectos\Pages\ViewProyecto;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StockAlmacenRelationManager extends RelationManager
{
    protected static string $relationship = 'stockAlmacen';

    protected static ?string $title = 'Stock del almacén';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
        
            ->heading('Stock del almacén')
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['material', 'almacen'])
                ->whereHas('almacen', fn (Builder $almacenQuery) => $almacenQuery->where('activo', true)))
            ->columns([
                Tables\Columns\TextColumn::make('material.codigo_producto')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('material.nombre_producto')
                    ->label('Material')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('cantidad_disponible')
                    ->label('Disponible')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad_minima_alerta')
                    ->label('Mín. alerta')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
            ])
            ->headerActions([])
            ->actions([])
            ->emptyStateHeading('No existe stock para el almacén asociado a este proyecto');
            
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        return $pageClass === ViewProyecto::class;
    }
}
