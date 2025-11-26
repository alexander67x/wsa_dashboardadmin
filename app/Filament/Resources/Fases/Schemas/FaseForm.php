<?php

namespace App\Filament\Resources\Fases\Schemas;

use App\Models\Fase;
use App\Models\Proyecto;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class FaseForm
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
                    ->reactive()
                    ->default(fn () => request()->get('cod_proy'))
                    ->afterStateUpdated(function (Set $set, $state) {
                        if (! $state) {
                            $set('orden', null);
                            return;
                        }

                        $maxOrder = Fase::where('cod_proy', $state)->max('orden');
                        $set('orden', ($maxOrder ?? 0) + 1);
                    }),

                TextInput::make('nombre_fase')
                    ->label('Nombre de la fase')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('orden')
                    ->label('Orden')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->helperText('Define el orden en la planificación del proyecto.'),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'planificada' => 'Planificada',
                        'en_ejecucion' => 'En ejecución',
                        'finalizada' => 'Finalizada',
                        'pausada' => 'Pausada',
                    ])
                    ->default('planificada')
                    ->required(),

                TextInput::make('porcentaje_avance')
                    ->label('Avance (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.1)
                    ->default(0)
                    ->helperText('Porcentaje de avance real de la fase.'),

                DatePicker::make('fecha_inicio')
                    ->label('Fecha de inicio')
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                DatePicker::make('fecha_fin')
                    ->label('Fecha de fin')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->after('fecha_inicio'),
            ]);
    }
}
