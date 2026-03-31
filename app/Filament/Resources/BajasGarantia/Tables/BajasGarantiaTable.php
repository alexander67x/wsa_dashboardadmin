<?php

namespace App\Filament\Resources\BajasGarantia\Tables;

use App\Filament\Resources\BajasGarantia\BajaGarantiaResource;
use App\Models\Proyecto;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BajasGarantiaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['material', 'almacen', 'proyecto', 'registradoPor', 'reclamo']))
            ->columns([
                TextColumn::make('id_reclamo')
                    ->label('Reclamo')
                    ->sortable(),
                TextColumn::make('proyecto.cod_proy')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('almacen.nombre')
                    ->label('Almacén')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('material.codigo_producto')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('material.nombre_producto')
                    ->label('Material')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('cantidad_baja')
                    ->label('Cantidad dada de baja')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('fecha_baja')
                    ->label('Fecha de baja')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('registradoPor.nombre_completo')
                    ->label('Registrado por')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('cod_proy')
                    ->label('Proyecto')
                    ->options(fn () => Proyecto::orderBy('cod_proy')->pluck('cod_proy', 'cod_proy')->toArray())
                    ->searchable()
                    ->preload(),
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
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->recordUrl(fn ($record): string => BajaGarantiaResource::getUrl('view', ['record' => $record]))
            ->defaultSort('fecha_baja', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
}
