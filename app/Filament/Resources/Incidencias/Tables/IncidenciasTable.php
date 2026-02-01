<?php

namespace App\Filament\Resources\Incidencias\Tables;

use App\Services\ProjectAccessService;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class IncidenciasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with(['proyecto', 'tarea', 'reportadoPor', 'asignadoA']);

                $user = Auth::user();
                if (! $user) {
                    return $query->whereRaw('1 = 0');
                }

                if ($user->empleado?->role?->slug === 'responsable_proyecto') {
                    $allowed = ProjectAccessService::allowedProjectIds($user);

                    if ($allowed === null) {
                        return $query;
                    }

                    if (empty($allowed)) {
                        return $query->whereRaw('1 = 0');
                    }

                    return $query->whereIn('cod_proy', $allowed);
                }

                return $query;
            })
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
                
                TextColumn::make('tipo_incidencia')
                    ->label('Tipo')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record?->tipo_incidencia)
                    ->color(fn (?string $tipo): string => match ($tipo) {
                        null => 'gray',
                        'accidente' => 'danger',
                        'falla_equipos' => 'warning',
                        'retraso_material' => 'info',
                        'problema_calidad' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($state): string {
                        $tipo = is_string($state) ? trim($state) : $state;
                        if (! $tipo) {
                            return '—';
                        }

                        return match ($tipo) {
                            'falla_equipos' => 'Falla Equipos',
                            'retraso_material' => 'Retraso Material',
                            'problema_calidad' => 'Problema Calidad',
                            default => ucfirst(str_replace('_', ' ', $tipo)),
                        };
                    }),
                
                TextColumn::make('severidad')
                    ->label('Severidad')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record?->severidad)
                    ->color(fn (?string $severidad): string => match ($severidad) {
                        null => 'gray',
                        'critica' => 'danger',
                        'alta' => 'warning',
                        'media' => 'info',
                        'baja' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(function ($state): string {
                        $severidad = is_string($state) ? trim($state) : $state;
                        return $severidad ? ucfirst($severidad) : '—';
                    }),
                
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        null => 'gray',
                        'cerrada' => 'success',
                        'registrada' => 'success',
                        'resuelta' => 'info',
                        'verificacion' => 'warning',
                        'en_proceso' => 'primary',
                        'reabierta' => 'danger',
                        'abierta' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        null => '—',
                        'en_proceso' => 'En Proceso',
                        'verificacion' => 'Verificación',
                        default => ucfirst($state),
                    })
                    ->sortable(),
                
                TextColumn::make('reportadoPor.nombre_completo')
                    ->label('Reportado por')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('asignadoA.nombre_completo')
                    ->label('Asignado a')
                    ->default('—')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('fecha_reportado')
                    ->label('Fecha de Reporte')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                TextColumn::make('fecha_resolucion')
                    ->label('Fecha de Resolución')
                    ->dateTime('d/m/Y H:i')
                    ->default('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'abierta' => 'Abierta',
                        'en_proceso' => 'En Proceso',
                        'resuelta' => 'Resuelta',
                        'verificacion' => 'Verificación',
                        'registrada' => 'Registrada',
                        'cerrada' => 'Cerrada',
                        'reabierta' => 'Reabierta',
                    ]),
                SelectFilter::make('severidad')
                    ->label('Severidad')
                    ->options([
                        'critica' => 'Crítica',
                        'alta' => 'Alta',
                        'media' => 'Media',
                        'baja' => 'Baja',
                    ]),
                SelectFilter::make('tipo_incidencia')
                    ->label('Tipo')
                    ->options([
                        'falla_equipos' => 'Falla Equipos',
                        'accidente' => 'Accidente',
                        'retraso_material' => 'Retraso Material',
                        'problema_calidad' => 'Problema Calidad',
                        'otro' => 'Otro',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('fecha_reportado', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}

