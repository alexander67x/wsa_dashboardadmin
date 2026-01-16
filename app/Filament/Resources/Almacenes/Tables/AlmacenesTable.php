<?php

namespace App\Filament\Resources\Almacenes\Tables;

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

class AlmacenesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with(['responsableEmpleado', 'almacenPadre', 'proyecto']);

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
                TextColumn::make('codigo_almacen')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
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
                    ->sortable(),

                TextColumn::make('almacenPadre.nombre')
                    ->label('Almacén Padre')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A')
                    ->toggleable(),

                TextColumn::make('proyecto.cod_proy')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A')
                    ->toggleable(),

                TextColumn::make('responsableEmpleado.nombre_completo')
                    ->label('Responsable')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ciudad')
                    ->label('Ciudad')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('subalmacenes_count')
                    ->label('Subalmacenes')
                    ->counts('subalmacenes')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'central' => 'Central',
                        'proyecto' => 'Proyecto',
                        'temporal' => 'Temporal',
                    ]),

                SelectFilter::make('activo')
                    ->label('Estado')
                    ->options([
                        true => 'Activo',
                        false => 'Inactivo',
                    ]),

                SelectFilter::make('almacenPadre')
                    ->label('Almacén Padre')
                    ->relationship('almacenPadre', 'nombre', function ($almacenQuery) {
                        if (! $almacenQuery) {
                            return $almacenQuery;
                        }

                        $user = Auth::user();
                        if (! $user) {
                            return $almacenQuery->whereRaw('1 = 0');
                        }

                        if ($user->empleado?->role?->slug === 'responsable_proyecto') {
                            $allowed = ProjectAccessService::allowedProjectIds($user);

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
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
