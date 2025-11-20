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
                    ->reactive(),

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
                    ->options(fn () => Empleado::query()
                        ->orderBy('nombre_completo')
                        ->pluck('nombre_completo', 'cod_empleado')
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
