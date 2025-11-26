<?php

namespace App\Filament\Resources\Fases\Tables;

use App\Models\Proyecto;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_fase')
                    ->label('Fase')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('proyecto.nombre_ubicacion')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => "{$record->cod_proy} — {$state}"),

                TextColumn::make('orden')
                    ->label('Orden')
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'finalizada' => 'success',
                        'en_ejecucion' => 'warning',
                        'planificada' => 'gray',
                        'pausada' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'planificada' => 'Planificada',
                        'en_ejecucion' => 'En ejecución',
                        'finalizada' => 'Finalizada',
                        'pausada' => 'Pausada',
                        default => ucfirst($state),
                    }),

                TextColumn::make('porcentaje_avance')
                    ->label('Avance')
                    ->suffix('%')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 1)),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('cod_proy')
                    ->label('Proyecto')
                    ->options(fn () => Proyecto::orderBy('cod_proy')
                        ->pluck('nombre_ubicacion', 'cod_proy')
                        ->map(fn ($nombre, $cod) => "{$cod} — {$nombre}")
                        ->toArray())
                    ->searchable()
                    ->preload(),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'planificada' => 'Planificada',
                        'en_ejecucion' => 'En ejecución',
                        'finalizada' => 'Finalizada',
                        'pausada' => 'Pausada',
                    ]),
            ])
            ->recordAction('edit')
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('cod_proy')
            ->paginated([10, 25, 50]);
    }
}
