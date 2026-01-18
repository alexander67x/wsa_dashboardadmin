<?php

namespace App\Filament\Resources\Reportes\Tables;

use App\Services\ProjectAccessService;
use App\Models\Proyecto;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ReportesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with(['proyecto', 'tarea', 'registradoPor', 'aprobadoPor']);

                $user = Auth::user();
                if (! $user) {
                    return $query->whereRaw('1 = 0');
                }

                if (in_array($user->empleado?->role?->slug, ['responsable_proyecto', 'supervisor'], true)) {
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
                    ->label('Revisado por')
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record || ! in_array($record->estado ?? null, ['aprobado', 'rechazado'])) {
                            return '—';
                        }

                        $prefix = $record->estado === 'aprobado' ? 'Aprobado por: ' : 'Rechazado por: ';
                        return $state ? $prefix . $state : $prefix . '—';
                    })
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('fecha_aprobacion')
                    ->label('Fecha de Revisión')
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record || ! in_array($record->estado ?? null, ['aprobado', 'rechazado'])) {
                            return '—';
                        }

                        if (! $state) {
                            return '—';
                        }

                        $date = $state instanceof \Illuminate\Support\Carbon ? $state : \Illuminate\Support\Carbon::parse($state);

                        $prefix = $record->estado === 'aprobado' ? 'Aprobado el ' : 'Rechazado el ';
                        return $prefix . $date->format('d/m/Y H:i');
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('cod_proy')
                    ->label('Proyecto')
                    ->options(function () {
                        $query = Proyecto::orderBy('cod_proy');
                        $user = Auth::user();

                        if ($user && in_array($user->empleado?->role?->slug, ['responsable_proyecto', 'supervisor'], true)) {
                            $allowed = ProjectAccessService::allowedProjectIds($user);

                            if ($allowed === null) {
                            return $query->pluck('cod_proy', 'cod_proy')->toArray();
                            }

                            if (empty($allowed)) {
                                return [];
                            }

                            $query->whereIn('cod_proy', $allowed);
                        }

                        return $query->pluck('cod_proy', 'cod_proy')->toArray();
                    })
                    ->searchable()
                    ->preload(),
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

