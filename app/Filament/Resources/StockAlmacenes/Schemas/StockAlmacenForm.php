<?php

namespace App\Filament\Resources\StockAlmacenes\Schemas;

use App\Models\Almacen;
use App\Models\Material;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StockAlmacenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('id_material')
                    ->label('Material')
                    ->relationship('material', 'nombre_producto')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn (Material $record): string => "{$record->codigo_producto} - {$record->nombre_producto}")
                    ->afterStateUpdated(function (Get $get, $set) {
                        // Si se selecciona un material, cargar su stock mínimo como cantidad mínima de alerta
                        $materialId = $get('id_material');
                        if ($materialId) {
                            $material = Material::find($materialId);
                            if ($material && !$get('cantidad_minima_alerta')) {
                                $set('cantidad_minima_alerta', $material->stock_minimo);
                            }
                        }
                    })
                    ->columnSpan(1),

                Select::make('id_almacen')
                    ->label('Almacén')
                    ->relationship('almacen', 'nombre', fn ($query) => $query->where('activo', true))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn (Almacen $record): string => "{$record->codigo_almacen} - {$record->nombre} ({$record->tipo})")
                    ->columnSpan(1),

                TextInput::make('cantidad_disponible')
                    ->label('Cantidad Disponible')
                    ->numeric()
                    ->step(0.01)
                    ->default(0)
                    ->required()
                    ->minValue(0)
                    ->columnSpan(1),

                TextInput::make('cantidad_reservada')
                    ->label('Cantidad Reservada')
                    ->numeric()
                    ->step(0.01)
                    ->default(0)
                    ->required()
                    ->minValue(0)
                    ->columnSpan(1),

                TextInput::make('cantidad_minima_alerta')
                    ->label('Cantidad Mínima de Alerta')
                    ->numeric()
                    ->step(0.01)
                    ->default(0)
                    ->required()
                    ->minValue(0)
                    ->helperText('Alerta cuando el stock disponible llegue a este nivel')
                    ->columnSpan(1),

                TextInput::make('ubicacion_fisica')
                    ->label('Ubicación Física')
                    ->maxLength(255)
                    ->placeholder('Ej: Estante 5, Pasillo 3')
                    ->helperText('Estante, pasillo o sector donde se encuentra el material')
                    ->columnSpan(1),

                TextInput::make('id_lote')
                    ->label('ID Lote')
                    ->numeric()
                    ->nullable()
                    ->helperText('Opcional: ID del lote si existe')
                    ->columnSpanFull(),
            ]);
    }
}

