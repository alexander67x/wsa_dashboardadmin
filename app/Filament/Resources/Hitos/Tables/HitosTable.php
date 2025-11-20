<?php

namespace App\Filament\Resources\Hitos\Tables;

use App\Models\Proyecto;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HitosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->label('Hito / Semana')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('proyecto.nombre_ubicacion')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => "{$record->cod_proy} — {$state}"),

                TextColumn::make('fecha_hito')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fecha_final_hito')
                    ->label('Objetivo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->estado === 'atrasado' ? 'danger' : null),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pendiente' => Color::Gray,
                        'en_ejecucion' => Color::Amber,
                        'completado' => Color::Green,
                        'atrasado' => Color::Red,
                        default => Color::Gray,
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pendiente' => 'Pendiente',
                        'en_ejecucion' => 'En ejecución',
                        'completado' => 'Completado',
                        'atrasado' => 'Atrasado',
                        default => ucfirst($state),
                    }),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : 'Intermedio'),

                IconColumn::make('es_critico')
                    ->label('Crítico')
                    ->boolean(),
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
                        'pendiente' => 'Pendiente',
                        'en_ejecucion' => 'En ejecución',
                        'completado' => 'Completado',
                        'atrasado' => 'Atrasado',
                    ]),

                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'intermedio' => 'Intermedio',
                        'entrega' => 'Entrega',
                        'revision' => 'Revisión',
                    ]),
            ])
            ->recordAction('edit')
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha_hito', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
}
