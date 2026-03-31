<?php

namespace App\Filament\Resources\Almacenes\RelationManagers;

use App\Models\Material;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();
        $isGerencia = $user && $user->empleado?->role?->slug === 'gerencia';

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
                    ->getStateUsing(fn ($record) => max(0, (float) $record->cantidad_disponible - (float) $record->cantidad_reservada))
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('cantidad_minima_alerta')
                    ->label('Mín. alerta')
                    ->numeric(2)
                    ->sortable(),
            ])
            ->headerActions($isGerencia ? [] : [
                CreateAction::make(),
            ])
            ->actions($isGerencia ? [] : [
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
