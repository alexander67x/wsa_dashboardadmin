<?php

namespace App\Filament\Resources\Asistencias\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AsistenciasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user.empleado.proyectosBasicos']))
            ->columns([
                TextColumn::make('empleado_nombre')
                    ->label('Empleado')
                    ->getStateUsing(function ($record) {
                        return $record->user?->empleado?->nombre_completo
                            ?? $record->user?->name
                            ?? '—';
                    })
                    ->sortable(),
                TextColumn::make('proyectos')
                    ->label('Proyecto(s)')
                    ->getStateUsing(function ($record) {
                        $proyectos = $record->user?->empleado?->proyectosBasicos;

                        if (! $proyectos || $proyectos->isEmpty()) {
                            return '—';
                        }

                        return $proyectos
                            ->map(fn ($proyecto) => $proyecto->cod_proy . ' — ' . ($proyecto->nombre_ubicacion ?? ''))
                            ->filter()
                            ->implode(', ');
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'open' => 'warning',
                        'closed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'open' => 'Abierta',
                        'closed' => 'Cerrada',
                        default => $state ?: '—',
                    })
                    ->sortable(),
                TextColumn::make('check_in_at')
                    ->label('Check-in')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('check_out_at')
                    ->label('Check-out')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('hours_worked')
                    ->label('Horas')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2) : '—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('location')
                    ->label('Ubicación')
                    ->getStateUsing(function ($record) {
                        return $record->check_out_location_label
                            ?: $record->check_in_location_label
                            ?: '—';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'open' => 'Abierta',
                        'closed' => 'Cerrada',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('check_in_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
