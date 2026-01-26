<?php

namespace App\Filament\Resources\StockAlmacenes\Tables;

use App\Services\ProjectAccessService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StockAlmacenesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with(['material', 'almacen']);

                $user = Auth::user();
                if (! $user) {
                    return $query->whereRaw('1 = 0');
                }

                if ($user->empleado?->role?->slug === 'responsable_proyecto') {
                    $allowed = ProjectAccessService::allowedProjectIds($user, true);

                    if ($allowed === null) {
                        return $query;
                    }

                    if (empty($allowed)) {
                        return $query->whereRaw('1 = 0');
                    }

                    return $query->whereHas('almacen', function ($almacenQuery) use ($allowed) {
                        $almacenQuery->whereIn('cod_proy', $allowed);
                    });
                }

                return $query;
            })
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
                    ->relationship('almacen', 'nombre', function ($almacenQuery) {
                        if (! $almacenQuery) {
                            return $almacenQuery;
                        }

                        $user = Auth::user();
                        if (! $user) {
                            return $almacenQuery->whereRaw('1 = 0');
                        }

                        if ($user->empleado?->role?->slug === 'responsable_proyecto') {
                            $allowed = ProjectAccessService::allowedProjectIds($user, true);

                            if ($allowed === null) {
                                return $almacenQuery;
                            }

                            if (empty($allowed)) {
                                return $almacenQuery->whereRaw('1 = 0');
                            }

                            return $almacenQuery->whereIn('cod_proy', $allowed);
                        }

                        return $almacenQuery;
                    })
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
                EditAction::make()
                    ->visible(function () {
                        $user = Auth::user();

                        // Gerencia solo puede ver, no editar.
                        if ($user && $user->empleado?->role?->slug === 'gerencia') {
                            return false;
                        }

                        return true;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible(function () {
                    $user = Auth::user();

                    // Sin acciones masivas destructivas para Gerencia.
                    if ($user && $user->empleado?->role?->slug === 'gerencia') {
                        return false;
                    }

                    return true;
                }),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
