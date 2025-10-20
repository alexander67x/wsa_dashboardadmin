<?php

namespace App\Filament\Resources\Proyectos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\HeaderAction;
use Filament\Tables\Actions\HeaderActionsPosition;

class ProyectosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cod_proy')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('cliente.nombre_cliente')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                
                // Mostrar el nombre de la ubicación desde la propia tabla proyectos
                TextColumn::make('nombre_ubicacion')
                    ->label('Ubicación')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin ubicación'),
                
                TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                
                TextColumn::make('fecha_fin_estimada')
                    ->label('Fin Estimado')
                    ->date('d/m/Y')
                    ->sortable(),
                
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'pausado' => 'warning',
                        'cancelado' => 'danger',
                        'completado' => 'primary',
                    }),
                
                // Presupuesto eliminado de la tabla
                
                TextColumn::make('responsable.nombre_completo')
                    ->label('Responsable')
                    ->searchable()
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
                SelectFilter::make('estado')
                    ->options([
                        'activo' => 'Activo',
                        'completado' => 'Completado',
                        'cancelado' => 'Cancelado',
                        'pausado' => 'Pausado',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
