<?php

namespace App\Filament\Resources\BajasGarantia\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BajaGarantiaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('material_info')
                ->label('Material')
                ->disabled()
                ->dehydrated(false)
                ->formatStateUsing(fn ($state, $record): string => $record && $record->material
                    ? "{$record->material->codigo_producto} - {$record->material->nombre_producto}"
                    : '—'),
            TextInput::make('almacen_info')
                ->label('Almacén')
                ->disabled()
                ->dehydrated(false)
                ->formatStateUsing(fn ($state, $record): string => $record?->almacen?->nombre ?? '—'),
            TextInput::make('proyecto_info')
                ->label('Proyecto')
                ->disabled()
                ->dehydrated(false)
                ->formatStateUsing(fn ($state, $record): string => $record?->cod_proy ?? '—'),
            TextInput::make('cantidad_baja')
                ->label('Cantidad dada de baja')
                ->disabled()
                ->dehydrated(false),
            TextInput::make('fecha_baja')
                ->label('Fecha de baja')
                ->disabled()
                ->dehydrated(false)
                ->formatStateUsing(fn ($state, $record): string => $record?->fecha_baja?->format('d/m/Y H:i') ?? '—'),
            TextInput::make('registrado_por_info')
                ->label('Registrado por')
                ->disabled()
                ->dehydrated(false)
                ->formatStateUsing(fn ($state, $record): string => $record?->registradoPor?->nombre_completo ?? '—'),
            Textarea::make('motivo')
                ->label('Motivo de daño')
                ->disabled()
                ->dehydrated(false)
                ->rows(3)
                ->columnSpanFull(),
            Textarea::make('observaciones')
                ->label('Observaciones')
                ->disabled()
                ->dehydrated(false)
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }
}
