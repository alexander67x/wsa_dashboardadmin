<?php

namespace App\Filament\Resources\SolicitudesMateriales\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SolicitudesMaterialesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['proyecto', 'solicitadoPor', 'aprobadaPor', 'items.material']))
            ->columns([
                TextColumn::make('numero_solicitud')
                    ->label('Número de Solicitud')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('proyecto.nombre_ubicacion')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                TextColumn::make('solicitadoPor.nombre_completo')
                    ->label('Solicitado por')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('fecha_solicitud')
                    ->label('Fecha de Solicitud')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                TextColumn::make('fecha_requerida')
                    ->label('Fecha Requerida')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record && $record->fecha_requerida && $record->fecha_requerida < now() ? 'danger' : null),
                
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aprobada' => 'success',
                        'rechazada' => 'danger',
                        'pendiente' => 'warning',
                        'enviado' => 'info',
                        'recibida' => 'success',
                        'cancelada' => 'gray',
                        'borrador' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aprobada' => 'Aprobada',
                        'rechazada' => 'Rechazada',
                        'pendiente' => 'Pendiente',
                        'enviado' => 'Enviado',
                        'recibida' => 'Recibida',
                        'cancelada' => 'Cancelada',
                        'borrador' => 'Borrador',
                        default => ucfirst($state),
                    })
                    ->sortable(),
                
                IconColumn::make('urgente')
                    ->label('Urgente')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('porcentaje_entregado')
                    ->label('% Entregado')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 1)
                    ->color(fn ($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable()
                    ->visible(fn ($record) => $record && in_array($record->estado ?? '', ['aprobada', 'enviado', 'recibida'])),
                
                TextColumn::make('aprobadaPor.nombre_completo')
                    ->label('Aprobada por')
                    ->default('—')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('fecha_aprobacion')
                    ->label('Fecha de Aprobación')
                    ->dateTime('d/m/Y H:i')
                    ->default('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'pendiente' => 'Pendiente',
                        'aprobada' => 'Aprobada',
                        'enviado' => 'Enviado',
                        'recibida' => 'Recibida',
                        'rechazada' => 'Rechazada',
                        'cancelada' => 'Cancelada',
                    ]),
                
                SelectFilter::make('urgente')
                    ->label('Urgente')
                    ->options([
                        1 => 'Sí',
                        0 => 'No',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('fecha_solicitud', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}

