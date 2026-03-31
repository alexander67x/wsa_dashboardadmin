<?php

namespace App\Filament\Resources\ReclamosGarantia\Schemas;

use App\Models\StockAlmacen;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ReclamoGarantiaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('id_stock')
                    ->label('Stock afectado')
                    ->options(fn () => self::stockOptions())
                    ->default(fn () => request()->get('id_stock'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        self::fillStockDetails($set, $state);
                    }),

                TextInput::make('cantidad_reclamada')
                    ->label('Cantidad reclamada')
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->minValue(0.01),

                TextInput::make('material_info')
                    ->label('Material')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('almacen_info')
                    ->label('Almacén')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('garantia_dias_info')
                    ->label('Garantía (días)')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('fecha_registro_info')
                    ->label('Fecha registro')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('fecha_fin_garantia_info')
                    ->label('Fecha fin garantía')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('stock_real_info')
                    ->label('Stock real disponible')
                    ->disabled()
                    ->dehydrated(false),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'abierto' => 'Abierto',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        'finalizado' => 'Finalizado',
                    ])
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn (?string $operation): bool => in_array($operation, ['view', 'edit'], true)),

                Select::make('resultado_final')
                    ->label('Resultado final')
                    ->options([
                        'devuelto_stock' => 'Devuelto a stock',
                        'baja_definitiva' => 'Baja definitiva',
                    ])
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn (?string $operation): bool => in_array($operation, ['view', 'edit'], true)),

                Textarea::make('motivo_dano')
                    ->label('Motivo del daño')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    private static function stockOptions(): array
    {
        return StockAlmacen::query()
            ->with(['material:id_material,codigo_producto,nombre_producto', 'almacen:id_almacen,codigo_almacen,nombre,cod_proy'])
            ->whereNotNull('created_at')
            ->where('garantia_dias', '>', 0)
            ->whereRaw('DATE_ADD(created_at, INTERVAL garantia_dias DAY) >= CURDATE()')
            ->get()
            ->filter(fn (StockAlmacen $stock) => ((float) $stock->cantidad_disponible - (float) $stock->cantidad_reservada) > 0)
            ->mapWithKeys(function (StockAlmacen $stock) {
                $material = trim((string) ($stock->material?->codigo_producto.' - '.$stock->material?->nombre_producto));
                $almacen = trim((string) ($stock->almacen?->codigo_almacen.' - '.$stock->almacen?->nombre));
                $garantia = (int) ($stock->garantia_dias ?? 0);
                $stockReal = (float) $stock->cantidad_disponible - (float) $stock->cantidad_reservada;
                $fechaFin = $stock->fecha_fin_garantia ? date('d/m/Y', strtotime($stock->fecha_fin_garantia)) : '—';

                return [
                    $stock->getKey() => "{$material} | {$almacen} | Garantía {$garantia} días (vence {$fechaFin}) | Stock real {$stockReal}",
                ];
            })
            ->toArray();
    }

    public static function fillStockDetails(Set $set, mixed $stockId): void
    {
        if (! $stockId) {
            foreach ([
                'material_info',
                'almacen_info',
                'garantia_dias_info',
                'fecha_registro_info',
                'fecha_fin_garantia_info',
                'stock_real_info',
            ] as $field) {
                $set($field, null);
            }

            return;
        }

        /** @var StockAlmacen|null $stock */
        $stock = StockAlmacen::query()
            ->with(['material:id_material,codigo_producto,nombre_producto', 'almacen:id_almacen,codigo_almacen,nombre'])
            ->find($stockId);

        if (! $stock) {
            return;
        }

        $set('material_info', trim((string) ($stock->material?->codigo_producto.' - '.$stock->material?->nombre_producto)));
        $set('almacen_info', trim((string) ($stock->almacen?->codigo_almacen.' - '.$stock->almacen?->nombre)));
        $set('garantia_dias_info', (string) ($stock->garantia_dias ?? 0));
        $set('fecha_registro_info', optional($stock->created_at)->format('d/m/Y'));
        $set('fecha_fin_garantia_info', $stock->fecha_fin_garantia ? date('d/m/Y', strtotime($stock->fecha_fin_garantia)) : null);
        $set('stock_real_info', number_format(((float) $stock->cantidad_disponible - (float) $stock->cantidad_reservada), 2));
    }
}
