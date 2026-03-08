<?php

namespace App\Filament\Resources\Clientes\RelationManagers;

use App\Filament\Resources\Proyectos\ProyectoResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProyectosRelationManager extends RelationManager
{
    protected static string $relationship = 'proyectos';

    protected static ?string $title = 'Proyectos';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Proyectos asociados')
            ->recordTitleAttribute('cod_proy')
            ->defaultSort('fecha_inicio', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('cod_proy')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('nombre_ubicacion')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pendiente' => 'Pendiente',
                        'en_progreso' => 'En progreso',
                        'completado' => 'Completado',
                        'cancelado' => 'Cancelado',
                        default => $state ? ucfirst(str_replace('_', ' ', $state)) : '—',
                    }),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin_estimada')
                    ->label('Fin estimado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->headerActions([])
            ->actions([])
            ->recordUrl(fn ($record): string => ProyectoResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Este cliente no tiene proyectos asociados');
    }
}
