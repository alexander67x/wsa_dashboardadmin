<?php

namespace App\Filament\Resources\Tareas\Schemas;

use App\Models\Empleado;
use App\Models\Proyecto;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TareaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cod_proy')
                    ->label('Proyecto')
                    ->options(fn () => Proyecto::orderBy('cod_proy')
                        ->get()
                        ->mapWithKeys(fn ($proyecto) => [
                            $proyecto->cod_proy => "{$proyecto->cod_proy} — {$proyecto->nombre_ubicacion}"
                        ])
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn () => request()->get('cod_proy'))
                    ->disabled(fn () => request()->has('cod_proy'))
                    ->dehydrated()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state) {
                        // Limpiar responsable si cambia el proyecto
                        $set('responsable_id', null);
                    }),

                TextInput::make('titulo')
                    ->label('Título de la Tarea')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: Instalación de sistema eléctrico')
                    ->columnSpanFull(),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->placeholder('Descripción detallada de la tarea...')
                    ->columnSpanFull(),

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
                        'baja' => 'Baja',
                        'media' => 'Media',
                        'alta' => 'Alta',
                    ])
                    ->default('media')
                    ->required(),

                Select::make('responsable_id')
                    ->label('Responsable')
                    ->options(function (Get $get) {
                        $codProy = $get('cod_proy');
                        if (!$codProy) {
                            return [];
                        }
                        
                        // Obtener empleados asignados al proyecto
                        $proyecto = Proyecto::with('empleados')->where('cod_proy', $codProy)->first();
                        if (!$proyecto) {
                            return [];
                        }
                        
                        return $proyecto->empleados
                            ->pluck('nombre_completo', 'cod_empleado')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn (Get $get) => !$get('cod_proy'))
                    ->helperText(fn (Get $get) => !$get('cod_proy') 
                        ? 'Primero selecciona un proyecto' 
                        : 'Solo se muestran empleados asignados al proyecto'),

                Select::make('supervisor_asignado')
                    ->label('Supervisor')
                    ->relationship('supervisor', 'nombre_completo')
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get) => !$get('cod_proy')),

                DatePicker::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->default(now())
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                DatePicker::make('fecha_fin')
                    ->label('Fecha de Vencimiento')
                    ->after('fecha_inicio')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->helperText('Fecha límite para completar la tarea'),

                TextInput::make('duracion_dias')
                    ->label('Duración (días)')
                    ->numeric()
                    ->minValue(1)
                    ->step(1)
                    ->helperText('Número de días estimados para completar la tarea')
                    ->suffix('días'),

            ])
            ->columns(2);
    }
}

