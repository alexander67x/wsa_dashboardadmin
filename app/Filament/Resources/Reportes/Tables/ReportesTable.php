<?php

namespace App\Filament\Resources\Reportes\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['proyecto', 'tarea', 'registradoPor', 'aprobadoPor']))
            ->columns([
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                TextColumn::make('proyecto.nombre_ubicacion')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('tarea.titulo')
                    ->label('Tarea')
                    ->searchable()
                    ->limit(30),
                
                TextColumn::make('registradoPor.nombre_completo')
                    ->label('Registrado por')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('fecha_reporte')
                    ->label('Fecha de Reporte')
                    ->date('d/m/Y')
                    ->sortable(),
                
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aprobado' => 'success',
                        'rechazado' => 'danger',
                        'enviado' => 'warning',
                        'borrador' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        'enviado' => 'Pendiente',
                        'borrador' => 'Borrador',
                        default => ucfirst($state),
                    })
                    ->sortable(),
                
                TextColumn::make('aprobadoPor.nombre_completo')
                    ->label('Aprobado por')
                    ->default('—')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('fecha_aprobacion')
                    ->label('Fecha de Aprobación')
                    ->dateTime('d/m/Y H:i')
                    ->default('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'enviado' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        'borrador' => 'Borrador',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('fecha_reporte', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}

