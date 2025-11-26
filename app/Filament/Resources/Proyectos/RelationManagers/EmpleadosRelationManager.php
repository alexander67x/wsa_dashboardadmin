<?php

namespace App\Filament\Resources\Proyectos\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Table;

class EmpleadosRelationManager extends RelationManager
{
    protected static string $relationship = 'empleados';

    protected static ?string $title = 'Equipo asignado';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Empleados asignados')
            ->recordTitleAttribute('nombre_completo')
            ->columns([
                Tables\Columns\TextColumn::make('nombre_completo')
                    ->label('Empleado')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('puesto')
                    ->label('Puesto')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pivot.rol_en_proyecto')
                    ->label('Rol en el proyecto')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('pivot.fecha_inicio_asignacion')
                    ->label('Inicio')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('pivot.fecha_fin_asignacion')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('pivot.estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? str($state)->replace('_', ' ')->title() : 'Activo'),
            ])
            ->headerActions([])
            ->actions([
                DetachAction::make()
                    ->label('Quitar')
                    ->requiresConfirmation(),
            ])
            ->emptyStateHeading('Sin empleados asignados')
            ->emptyStateDescription('Asigna colaboradores desde el formulario del proyecto.');
    }
}
