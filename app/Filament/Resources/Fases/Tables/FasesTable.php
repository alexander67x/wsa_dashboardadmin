<?php

namespace App\Filament\Resources\Fases\Tables;

use App\Filament\Resources\Fases\FaseResource;
use App\Models\Proyecto;
use App\Services\ProjectAccessService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class FasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with('proyecto');

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
                    ->options(function () {
                        $query = Proyecto::orderBy('cod_proy');

                        $user = Auth::user();
                        if ($user?->empleado?->role?->slug === 'responsable_proyecto') {
                            $allowed = ProjectAccessService::allowedProjectIds($user);

                            if ($allowed === null) {
                                return $query
                                    ->pluck('nombre_ubicacion', 'cod_proy')
                                    ->map(fn ($nombre, $cod) => "{$cod} — {$nombre}")
                                    ->toArray();
                            }

                            if (empty($allowed)) {
                                return [];
                            }

                            $query->whereIn('cod_proy', $allowed);
                        }

                        return $query
                            ->pluck('nombre_ubicacion', 'cod_proy')
                            ->map(fn ($nombre, $cod) => "{$cod} — {$nombre}")
                            ->toArray();
                    })
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
            ->recordUrl(fn ($record): string => FaseResource::getUrl('edit', ['record' => $record]))
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('cod_proy')
            ->paginated([10, 25, 50]);
    }
}
