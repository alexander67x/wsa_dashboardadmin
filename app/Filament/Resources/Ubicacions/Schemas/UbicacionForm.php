<?php

namespace App\Filament\Resources\Ubicacions\Schemas;

use App\Filament\Components\MapPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class UbicacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre_ubicacion')
                    ->required(),
                Textarea::make('direccion')
                    ->columnSpanFull(),
                TextInput::make('ciudad'),
                TextInput::make('pais')
                    ->required()
                    ->default('Bolivia'),
                
                // Mapa y coordenadas
                MapPicker::make('coordenadas')
                    ->label('Ubicación en el Mapa')
                    ->columnSpanFull(),
                
                // Campos ocultos para latitud y longitud (se llenan automáticamente desde el mapa)
                TextInput::make('latitud')
                    ->label('Latitud')
                    ->numeric()
                    ->step(0.0000001)
                    ->hidden()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $coordenadas = $get('coordenadas');
                        if ($coordenadas && is_array($coordenadas)) {
                            $coordenadas['latitude'] = $state;
                            $set('coordenadas', $coordenadas);
                        }
                    }),
                
                TextInput::make('longitud')
                    ->label('Longitud')
                    ->numeric()
                    ->step(0.0000001)
                    ->hidden()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $coordenadas = $get('coordenadas');
                        if ($coordenadas && is_array($coordenadas)) {
                            $coordenadas['longitude'] = $state;
                            $set('coordenadas', $coordenadas);
                        }
                    }),
                
                Select::make('tipo_ubicacion')
                    ->options(['obra' => 'Obra', 'almacen' => 'Almacen', 'cliente' => 'Cliente'])
                    ->default('obra')
                    ->required(),
                Toggle::make('activo')
                    ->required(),
            ]);
    }
}
