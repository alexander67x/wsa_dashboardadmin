<?php

namespace App\Filament\Resources\ReclamosGarantia\Tables;

use App\Filament\Resources\ReclamosGarantia\ReclamoGarantiaResource;
use App\Models\Proyecto;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReclamosGarantiaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['material', 'almacen', 'proyecto', 'creadoPor']))
            ->columns([
                TextColumn::make('id_reclamo')
                    ->label('ID')
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
                TextColumn::make('cantidad_reclamada')
                    ->label('Cantidad')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'abierto' => 'warning',
                        'aprobado' => 'info',
                        'rechazado' => 'danger',
                        'finalizado' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'abierto' => 'Abierto',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        'finalizado' => 'Finalizado',
                        default => ucfirst($state),
                    }),
                TextColumn::make('resultado_final')
                    ->label('Resultado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'devuelto_stock' => 'Devuelto a stock',
                        'baja_definitiva' => 'Baja definitiva',
                        default => '—',
                    })
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'abierto' => 'Abierto',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        'finalizado' => 'Finalizado',
                    ]),
                SelectFilter::make('garantia')
                    ->label('Garantía')
                    ->options([
                        'activa' => 'Activa',
                        'vencida' => 'Vencida',
                    ])
                    ->query(function ($query, array $state) {
                        $value = $state['value'] ?? null;
                        if (! $value) {
                            return $query;
                        }

                        if ($value === 'activa') {
                            return $query->whereHas('stock', function ($stockQuery) {
                                $stockQuery
                                    ->whereNotNull('created_at')
                                    ->where('garantia_dias', '>', 0)
                                    ->whereRaw('DATE_ADD(created_at, INTERVAL garantia_dias DAY) >= CURDATE()');
                            });
                        }

                        return $query->whereHas('stock', function ($stockQuery) {
                            $stockQuery->where(function ($inner) {
                                $inner
                                    ->whereNull('created_at')
                                    ->orWhere('garantia_dias', '<=', 0)
                                    ->orWhereRaw('DATE_ADD(created_at, INTERVAL garantia_dias DAY) < CURDATE()');
                            });
                        });
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->recordUrl(fn ($record): string => ReclamoGarantiaResource::getUrl('view', ['record' => $record]))
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
}
