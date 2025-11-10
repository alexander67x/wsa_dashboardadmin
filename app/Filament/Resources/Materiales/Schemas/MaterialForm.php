<?php

namespace App\Filament\Resources\Materiales\Schemas;

use App\Models\Almacen;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo_producto')
                    ->label('Código del Producto')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Ej: MAT-001')
                    ->columnSpan(1),

                TextInput::make('nombre_producto')
                    ->label('Nombre del Producto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Select::make('id_subgrupo')
                    ->label('Subgrupo')
                    ->relationship('subgrupo', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('codigo_subgrupo')
                            ->label('Código')
                            ->required(),
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required(),
                    ])
                    ->columnSpan(1),

                TextInput::make('unidad_medida')
                    ->label('Unidad de Medida')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: kg, m, unidades, cajas')
                    ->columnSpan(1),

                TextInput::make('costo_unitario_promedio_bs')
                    ->label('Costo Unitario Promedio (Bs.)')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('Bs.')
                    ->default(0)
                    ->columnSpan(1),

                TextInput::make('equivalencia')
                    ->label('Equivalencia')
                    ->numeric()
                    ->step(0.01)
                    ->helperText('Ej: 1 caja = 12 unidades')
                    ->columnSpan(1),

                TextInput::make('unidad_equivalencia')
                    ->label('Unidad de Equivalencia')
                    ->maxLength(255)
                    ->placeholder('Ej: caja, paquete')
                    ->columnSpan(1),

                TextInput::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric()
                    ->step(0.01)
                    ->default(0)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('stock_maximo')
                    ->label('Stock Máximo')
                    ->numeric()
                    ->step(0.01)
                    ->columnSpan(1),

                Select::make('criticidad')
                    ->label('Criticidad')
                    ->options([
                        'critico' => 'Crítico',
                        'no_critico' => 'No Crítico',
                    ])
                    ->default('no_critico')
                    ->required()
                    ->columnSpan(1),

                Select::make('almacenes')
                    ->label('Almacenes')
                    ->relationship('almacenes', 'nombre', fn ($query) => $query->where('activo', true))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->codigo_almacen} - {$record->nombre} ({$record->tipo})")
                    ->helperText('Seleccione los almacenes donde está disponible este material (puede ser almacén central o subalmacenes)')
                    ->columnSpanFull(),

                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->required()
                    ->columnSpan(1),
            ]);
    }
}

