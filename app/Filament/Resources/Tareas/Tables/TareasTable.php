<?php

namespace App\Filament\Resources\Tareas\Tables;

use App\Models\Proyecto;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TareasTable
{
    public static function configure(Table $table): Table
    {
        $selectedProyecto = request()->get('cod_proy') 
            ?? request()->get('tableFilters')['cod_proy']['value'] ?? null;
            
        // Mostrar todas las tareas por defecto

        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->titulo),
                
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'en_proceso' => 'warning',
                        'en_pausa' => 'warning',
                        'en_revision' => 'info',
                        'finalizada' => 'success',
                        'cancelada' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En Proceso',
                        'en_pausa' => 'En Pausa',
                        'en_revision' => 'En Revisión',
                        'finalizada' => 'Finalizada',
                        'cancelada' => 'Cancelada',
                        default => $state,
                    }),
                
                TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'alta' => 'danger',
                        'media' => 'warning',
                        'baja' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'Sin prioridad')
                    ->sortable(),
                
                TextColumn::make('responsable.nombre_completo')
                    ->label('Responsable')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin asignar')
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('fecha_fin')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record) {
                        if (!$record->fecha_fin) return null;
                        if (in_array($record->estado, ['finalizada', 'cancelada'])) return null;
                        if ($record->fecha_fin->isPast()) return 'danger';
                        if ($record->fecha_fin->isToday() || $record->fecha_fin->isTomorrow()) return 'warning';
                        return null;
                    }),
                
                TextColumn::make('duracion_dias')
                    ->label('Duración (días)')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('column.nombre')
                    ->label('Columna Kanban')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
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
            ->filters(array_filter([
                SelectFilter::make('cod_proy')
                    ->label('Proyecto')
                    ->options(fn () => Proyecto::orderBy('cod_proy')
                        ->get()
                        ->mapWithKeys(fn ($proyecto) => [
                            $proyecto->cod_proy => "{$proyecto->cod_proy} — {$proyecto->nombre_ubicacion}"
                        ])
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->default($selectedProyecto)
                    ->placeholder('Todos los proyectos')
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            return $query->where('cod_proy', $data['value']);
                        }
                        return $query; // Mostrar todos los proyectos si no hay filtro
                    }),
                
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En Proceso',
                        'en_pausa' => 'En Pausa',
                        'en_revision' => 'En Revisión',
                        'finalizada' => 'Finalizada',
                        'cancelada' => 'Cancelada',
                    ])
                    ->multiple(),
                
                SelectFilter::make('prioridad')
                    ->label('Prioridad')
                    ->options([
                        'alta' => 'Alta',
                        'media' => 'Media',
                        'baja' => 'Baja',
                    ])
                    ->multiple(),
                
                SelectFilter::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'nombre_completo')
                    ->searchable()
                    ->preload(),
                
                TrashedFilter::make(),
            ]))
            ->recordActions([
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
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading($selectedProyecto ? 'No hay tareas en este proyecto' : 'Selecciona un proyecto para ver las tareas')
            ->emptyStateDescription($selectedProyecto ? 'Crea tu primera tarea usando el botón "Nueva Tarea"' : 'Usa el filtro de "Proyecto" para seleccionar uno')
            ->emptyStateIcon($selectedProyecto ? 'heroicon-o-clipboard-document-list' : 'heroicon-o-folder');
    }
}
