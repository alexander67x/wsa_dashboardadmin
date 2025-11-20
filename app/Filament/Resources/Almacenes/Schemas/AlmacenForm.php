<?php

namespace App\Filament\Resources\Almacenes\Schemas;

use App\Filament\Components\MapPicker;
use App\Models\Almacen;
use App\Models\Empleado;
use App\Models\Proyecto;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
class AlmacenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo_almacen')
                    ->label('Código del Almacén')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Ej: ALM-001')
                    ->columnSpan(1),

                TextInput::make('nombre')
                    ->label('Nombre del Almacén')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Select::make('tipo')
                    ->label('Tipo de Almacén')
                    ->options([
                        'central' => 'Central',
                        'proyecto' => 'Proyecto',
                        'temporal' => 'Temporal',
                    ])
                    ->required()
                    ->default('proyecto')
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        // Si es central, limpiar campos relacionados
                        if ($state === 'central') {
                            $set('id_almacen_padre', null);
                            $set('cod_proy', null);
                        }
                    })
                    ->columnSpan(1),

                Select::make('id_almacen_padre')
                    ->label('Almacén Padre')
                    ->relationship('almacenPadre', 'nombre', fn ($query) => $query->where('tipo', 'central'))
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get) => $get('tipo') !== 'central')
                    ->required(fn (Get $get) => $get('tipo') !== 'central')
                    ->helperText('Seleccione el almacén central del cual depende este almacén')
                    ->columnSpan(1),

                Select::make('cod_proy')
                    ->label('Proyecto')
                    ->relationship('proyecto', 'cod_proy')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn (Proyecto $record): string => "{$record->cod_proy} - {$record->nombre_ubicacion}")
                    ->visible(fn (Get $get) => $get('tipo') === 'proyecto')
                    ->required(fn (Get $get) => $get('tipo') === 'proyecto')
                    ->helperText('Seleccione el proyecto asociado a este almacén')
                    ->columnSpan(1),

                Select::make('responsable')
                    ->label('Responsable')
                    ->options(fn () => Empleado::orderBy('nombre_completo')
                        ->pluck('nombre_completo', 'cod_empleado')
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(1),

                Textarea::make('direccion')
                    ->label('Dirección')
                    ->rows(2)
                    ->columnSpanFull(),

                TextInput::make('ciudad')
                    ->label('Ciudad')
                    ->maxLength(255)
                    ->default('La Paz')
                    ->columnSpan(1),

                TextInput::make('pais')
                    ->label('País')
                    ->maxLength(255)
                    ->default('Bolivia')
                    ->columnSpan(1),

                MapPicker::make('coordenadas')
                    ->label('Ubicación en el Mapa')
                    ->columnSpanFull(),

                TextInput::make('latitud')
                    ->label('Latitud')
                    ->numeric()
                    ->step(0.0000001)
                    ->hidden()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $coordenadas = $get('coordenadas') ?? [];
                        $coordenadas['latitude'] = $state;
                        $set('coordenadas', $coordenadas);
                    }),

                TextInput::make('longitud')
                    ->label('Longitud')
                    ->numeric()
                    ->step(0.0000001)
                    ->hidden()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $coordenadas = $get('coordenadas') ?? [];
                        $coordenadas['longitude'] = $state;
                        $set('coordenadas', $coordenadas);
                    }),

                Select::make('tipo_ubicacion')
                    ->label('Tipo de Ubicación')
                    ->options([
                        'almacen' => 'Almacén',
                        'temporal' => 'Temporal',
                    ])
                    ->default('almacen')
                    ->columnSpan(1),

                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->required()
                    ->columnSpan(1),
            ]);
    }
}

