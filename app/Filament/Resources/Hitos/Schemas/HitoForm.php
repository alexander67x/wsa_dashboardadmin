<?php

namespace App\Filament\Resources\Hitos\Schemas;

use App\Models\Empleado;
use App\Models\Fase;
use App\Models\Proyecto;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class HitoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('cod_proy')
                    ->label('Proyecto')
                    ->options(fn () => Proyecto::query()
                        ->orderBy('cod_proy')
                        ->pluck('nombre_ubicacion', 'cod_proy')
                        ->map(fn ($nombre, $cod) => "{$cod} — {$nombre}")
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if (! $state) {
                            $set('creado_por', null);

                            return;
                        }

                        $responsableId = Proyecto::query()
                            ->where('cod_proy', $state)
                            ->value('responsable_proyecto');

                        $set('creado_por', $responsableId ?: null);
                    }),

                Select::make('id_fase')
                    ->label('Fase')
                    ->options(fn (Get $get) => Fase::query()
                        ->where('cod_proy', $get('cod_proy'))
                        ->orderBy('nombre_fase')
                        ->pluck('nombre_fase', 'id_fase')
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get) => ! $get('cod_proy'))
                    ->placeholder('Sin fase'),

                TextInput::make('titulo')
                    ->label('Título del hito')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),

                DatePicker::make('fecha_hito')
                    ->label('Fecha de inicio / semana')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required(),

                DatePicker::make('fecha_final_hito')
                    ->label('Fecha objetivo')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('fecha_hito')
                    ->required(),

                Select::make('tipo')
                    ->label('Tipo de hito')
                    ->options([
                        'intermedio' => 'Intermedio',
                        'entrega' => 'Entrega',
                        'revision' => 'Revisión',
                    ])
                    ->default('intermedio'),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_ejecucion' => 'En ejecución',
                        'completado' => 'Completado',
                        'atrasado' => 'Atrasado',
                    ])
                    ->default('pendiente')
                    ->required(),

                Toggle::make('es_critico')
                    ->label('Es crítico')
                    ->helperText('Indica si este hito es crítico en el seguimiento del proyecto'),

                Select::make('creado_por')
                    ->label('Responsable')
                    ->options(fn (Get $get) => Empleado::query()
                        ->when(
                            $get('cod_proy'),
                            function ($query, $codProy) {
                                $responsableId = Proyecto::query()
                                    ->where('cod_proy', $codProy)
                                    ->value('responsable_proyecto');

                                $query
                                    ->whereHas('role', fn ($roleQuery) => $roleQuery->where('slug', 'responsable_proyecto'))
                                    ->where(function ($inner) use ($codProy, $responsableId) {
                                        $inner->whereHas('asignaciones', fn ($subQuery) => $subQuery
                                            ->where('cod_proy', $codProy)
                                            ->where(function ($statusQuery) {
                                                $statusQuery
                                                    ->where('estado', 'activo')
                                                    ->orWhereNull('estado');
                                            }));

                                        if ($responsableId) {
                                            $inner->orWhere('cod_empleado', $responsableId);
                                        }
                                    });
                            },
                            fn ($query) => $query->whereRaw('1 = 0')
                        )
                        ->orderBy('nombre_completo')
                        ->pluck('nombre_completo', 'cod_empleado')
                        ->toArray())
                    ->live()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('cod_proy'))
                    ->helperText('Solo se listan responsables del proyecto.'),
            ]);
    }
}
