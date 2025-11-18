<?php

namespace App\Filament\Resources\Almacenes\RelationManagers;

use App\Models\Material;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables;

class StockRelationManager extends RelationManager
{
    protected static string $relationship = 'stock';

    protected static ?string $title = 'Stock del almacén';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('id_material')
                ->label('Material')
                ->options(Material::orderBy('nombre_producto')->pluck('nombre_producto', 'id_material'))
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('cantidad_disponible')
                ->label('Cantidad disponible')
                ->numeric()
                ->required(),

            TextInput::make('cantidad_minima_alerta')
                ->label('Mínimo en alerta')
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('material.codigo_producto')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('material.nombre_producto')
                    ->label('Material')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cantidad_disponible')
                    ->label('Disponible')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('cantidad_minima_alerta')
                    ->label('Mín. alerta')
                    ->numeric(2)
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}

