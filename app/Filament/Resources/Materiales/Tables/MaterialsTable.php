<?php

namespace App\Filament\Resources\Materiales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaterialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['subgrupo', 'almacenes']))
            ->columns([
                TextColumn::make('codigo_producto')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nombre_producto')
                    ->label('Nombre del Producto')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('subgrupo.nombre')
                    ->label('Subgrupo')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('unidad_medida')
                    ->label('Unidad')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('costo_unitario_promedio_bs')
                    ->label('Costo Unitario')
                    ->money('BOB', locale: 'es')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stock_minimo')
                    ->label('Stock Mín.')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stock_maximo')
                    ->label('Stock Máx.')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('almacenes_count')
                    ->label('Almacenes')
                    ->counts('almacenes')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('almacenes.nombre')
                    ->label('Ubicaciones')
                    ->badge()
                    ->color('info')
                    ->separator(',')
                    ->limit(3)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('criticidad')
                    ->label('Criticidad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critico' => 'danger',
                        'no_critico' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'critico' => 'Crítico',
                        'no_critico' => 'No Crítico',
                        default => $state,
                    })
                    ->sortable(),

                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('criticidad')
                    ->label('Criticidad')
                    ->options([
                        'critico' => 'Crítico',
                        'no_critico' => 'No Crítico',
                    ]),

                SelectFilter::make('activo')
                    ->label('Estado')
                    ->options([
                        true => 'Activo',
                        false => 'Inactivo',
                    ]),

                SelectFilter::make('almacenes')
                    ->label('Almacén')
                    ->relationship('almacenes', 'nombre')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}

