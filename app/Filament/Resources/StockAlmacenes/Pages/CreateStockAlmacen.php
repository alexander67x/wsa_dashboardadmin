<?php

namespace App\Filament\Resources\StockAlmacenes\Pages;

use App\Filament\Resources\StockAlmacenes\StockAlmacenResource;
use App\Models\StockAlmacen;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CreateStockAlmacen extends CreateRecord
{
    protected static string $resource = StockAlmacenResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('id_almacen')
                ->label('Almacén')
                ->relationship('almacen', 'nombre', fn ($query) => $query->where('activo', true))
                ->searchable()
                ->preload()
                ->required(),

            Repeater::make('items')
                ->label('Materiales del almacén')
                ->schema([
                    Select::make('id_material')
                        ->label('Material')
                        ->relationship('material', 'nombre_producto')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('cantidad_disponible')
                        ->label('Cantidad disponible')
                        ->numeric()
                        ->required()
                        ->default(0),

                    TextInput::make('cantidad_minima_alerta')
                        ->label('Mínimo en alerta')
                        ->numeric()
                        ->default(0),
                ])
                ->minItems(1)
                ->columnSpanFull(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $almacenId = $data['id_almacen'] ?? null;
        $items = $data['items'] ?? [];

        $created = null;

        if ($almacenId && ! empty($items)) {
            foreach ($items as $item) {
                if (empty($item['id_material'])) {
                    continue;
                }

                $created = StockAlmacen::updateOrCreate(
                    [
                        'id_almacen' => $almacenId,
                        'id_material' => $item['id_material'],
                    ],
                    [
                        'cantidad_disponible' => $item['cantidad_disponible'] ?? 0,
                        'cantidad_minima_alerta' => $item['cantidad_minima_alerta'] ?? 0,
                    ],
                );
            }
        }

        // Devolver algún registro válido para que Filament complete el flujo.
        return $created ?? new StockAlmacen();
    }
}
