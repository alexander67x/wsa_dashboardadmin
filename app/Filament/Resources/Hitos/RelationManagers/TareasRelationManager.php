<?php

namespace App\Filament\Resources\Hitos\RelationManagers;

use App\Models\Proyecto;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class TareasRelationManager extends RelationManager
{
    protected static string $relationship = 'tareas';

    protected static ?string $title = 'Tareas del hito';

    public function form(Schema $schema): Schema
    {
        $ownerProject = $this->getOwnerRecord()->proyecto;

        return $schema
            ->columns(2)
            ->components([
                TextInput::make('titulo')
                    ->label('Título de la tarea')
                    ->required()
                    ->maxLength(255),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En Proceso',
                        'en_pausa' => 'En Pausa',
                        'en_revision' => 'En Revisión',
                        'finalizada' => 'Finalizada',
                        'cancelada' => 'Cancelada',
                    ])
                    ->default('pendiente')
                    ->required(),

                Select::make('prioridad')
                    ->label('Prioridad')
                    ->options([
                        'alta' => 'Alta',
                        'media' => 'Media',
                        'baja' => 'Baja',
                    ])
                    ->default('media')
                    ->required(),

                Select::make('responsable_id')
                    ->label('Responsable')
                    ->options(fn () => $ownerProject
                        ? $ownerProject->empleados
                            ->pluck('nombre_completo', 'cod_empleado')
                            ->toArray()
                        : [])
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder($ownerProject ? 'Selecciona un responsable' : 'Asigna empleados al proyecto'),

                Select::make('supervisor_asignado')
                    ->label('Supervisor')
                    ->relationship('supervisor', 'nombre_completo')
                    ->searchable()
                    ->preload()
                    ->columnSpan(2),

                DatePicker::make('fecha_inicio')
                    ->label('Inicio')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->columnSpan(1),

                DatePicker::make('fecha_fin')
                    ->label('Fin')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->after('fecha_inicio')
                    ->columnSpan(1),

                TextInput::make('duracion_dias')
                    ->label('Duración (días)')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('días'),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->recordTitleAttribute('titulo')
            ->columns([
                TextColumn::make('titulo')
                    ->label('Tarea')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'alta' => 'danger',
                        'media' => 'warning',
                        'baja' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('responsable.nombre_completo')
                    ->label('Responsable')
                    ->badge()
                    ->color('info')
                    ->placeholder('Sin asignar'),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y'),

                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->date('d/m/Y'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar tarea')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['cod_proy'] = $this->getOwnerRecord()->cod_proy;

                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('fecha_inicio');
    }
}
