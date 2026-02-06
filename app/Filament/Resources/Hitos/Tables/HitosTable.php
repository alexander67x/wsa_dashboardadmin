<?php

namespace App\Filament\Resources\Hitos\Tables;

use App\Models\Proyecto;
use App\Services\ProjectAccessService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class HitosTable
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
            ->recordActions([
                EditAction::make(),
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
