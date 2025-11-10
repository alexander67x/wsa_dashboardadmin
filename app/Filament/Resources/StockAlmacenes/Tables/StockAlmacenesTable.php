<?php

namespace App\Filament\Resources\StockAlmacenes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockAlmacenesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['material', 'almacen']))
            ->columns([
                TextColumn::make('material.codigo_producto')
                    ->label('Código Material')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('material.nombre_producto')
                    ->label('Material')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('almacen.codigo_almacen')
                    ->label('Código Almacén')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('almacen.nombre')
                    ->label('Almacén')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('almacen.tipo')
                    ->label('Tipo Almacén')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'central' => 'success',
                        'proyecto' => 'primary',
                        'temporal' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'central' => 'Central',
                        'proyecto' => 'Proyecto',
                        'temporal' => 'Temporal',
                        default => $state,
                    })
                    ->toggleable(),

                TextColumn::make('cantidad_disponible')
                    ->label('Disponible')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($record) => $record->cantidad_disponible <= $record->cantidad_minima_alerta ? 'danger' : 'success'),

                TextColumn::make('cantidad_reservada')
                    ->label('Reservada')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->color('warning')
                    ->toggleable(),

                TextColumn::make('cantidad_disponible_real')
                    ->label('Disponible Real')
                    ->getStateUsing(fn ($record) => $record->cantidad_disponible - $record->cantidad_reservada)
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('cantidad_minima_alerta')
                    ->label('Mín. Alerta')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('necesita_reposicion')
                    ->label('Alerta')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->cantidad_disponible <= $record->cantidad_minima_alerta)
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success')
                    ->sortable(),

                TextColumn::make('ubicacion_fisica')
                    ->label('Ubicación')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('id_almacen')
                    ->label('Almacén')
                    ->relationship('almacen', 'nombre')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('id_material')
                    ->label('Material')
                    ->relationship('material', 'nombre_producto')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('necesita_reposicion')
                    ->label('Necesita Reposición')
                    ->options([
                        true => 'Sí',
                        false => 'No',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === true) {
                            return $query->whereRaw('cantidad_disponible <= cantidad_minima_alerta');
                        }
                        if ($state['value'] === false) {
                            return $query->whereRaw('cantidad_disponible > cantidad_minima_alerta');
                        }
                        return $query;
                    }),
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
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}

