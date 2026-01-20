<?php

namespace App\Filament\Resources\Tareas\Schemas;

use App\Models\Hito;
use App\Models\Proyecto;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                    ->nullable()
                    ->default(fn () => request()->get('cod_proy'))
                    ->disabled(fn () => request()->has('cod_proy'))
                    ->dehydrated()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state) {
                        // Limpiar responsables si cambia el proyecto
                        $set('responsables', []);
                        $set('id_hito', null);
                    }),

                Select::make('id_hito')
                    ->label('Hito semanal')
                    ->options(function (Get $get) {
                        $codProy = $get('cod_proy');
                        if (! $codProy) {
                            return [];
                        }

                        return Hito::query()
                            ->where('cod_proy', $codProy)
                            ->orderBy('fecha_hito')
                            ->get()
                            ->mapWithKeys(function ($hito) {
                                $inicio = optional($hito->fecha_hito)->format('d/m');
                                $fin = optional($hito->fecha_final_hito)->format('d/m');

                                return [
                                    $hito->id_hito => "{$hito->titulo} — {$inicio} al {$fin}",
                                ];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->helperText('Los hitos se cargan automáticamente al seleccionar un proyecto.')
                    ->placeholder('Sin hito asignado'),

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

                Select::make('responsables')
                    ->label('Responsables')
                    ->relationship(
                        name: 'responsables',
                        titleAttribute: 'nombre_completo',
                        modifyQueryUsing: function (Builder $query, Get $get) {
                            $codProy = $get('cod_proy');

                            if ($codProy) {
                                $project = Proyecto::query()
                                    ->where('cod_proy', $codProy)
                                    ->first(['responsable_proyecto', 'supervisor_obra']);
                                $extraIds = collect([$project?->responsable_proyecto, $project?->supervisor_obra])
                                    ->filter()
                                    ->unique()
                                    ->values();

                                $query->where(function (Builder $inner) use ($codProy, $extraIds) {
                                    $inner->whereHas('asignaciones', fn ($subQuery) => $subQuery
                                        ->where('cod_proy', $codProy)
                                        ->where(function ($statusQuery) {
                                            $statusQuery
                                                ->where('estado', 'activo')
                                                ->orWhereNull('estado');
                                        }));

                                    if ($extraIds->isNotEmpty()) {
                                        $inner->orWhereIn('cod_empleado', $extraIds->all());
                                    }
                                });
                            } else {
                                $query->whereRaw('1 = 0');
                            }
                        }
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Selecciona uno o más responsables del proyecto.'),

                Select::make('supervisor_asignado')
                    ->label('Supervisor')
                    ->relationship(
                        'supervisor',
                        'nombre_completo',
                        modifyQueryUsing: function (Builder $query, Get $get) {
                            $codProy = $get('cod_proy');

                            if (! $codProy) {
                                $query->whereRaw('1 = 0');
                                return;
                            }

                            $project = Proyecto::query()
                                ->where('cod_proy', $codProy)
                                ->first(['responsable_proyecto', 'supervisor_obra']);
                            $extraIds = collect([$project?->responsable_proyecto, $project?->supervisor_obra])
                                ->filter()
                                ->unique()
                                ->values();

                            $query->where(function (Builder $inner) use ($codProy, $extraIds) {
                                $inner->whereHas('asignaciones', fn ($subQuery) => $subQuery
                                    ->where('cod_proy', $codProy)
                                    ->where(function ($statusQuery) {
                                        $statusQuery
                                            ->where('estado', 'activo')
                                            ->orWhereNull('estado');
                                    }));

                                if ($extraIds->isNotEmpty()) {
                                    $inner->orWhereIn('cod_empleado', $extraIds->all());
                                }
                            });
                        }
                    )
                    ->searchable()
                    ->preload(),

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
