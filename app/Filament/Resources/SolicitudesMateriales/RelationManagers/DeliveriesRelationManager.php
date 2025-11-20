<?php

namespace App\Filament\Resources\SolicitudesMateriales\RelationManagers;

use App\Models\SolicitudMaterial;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DeliveriesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveries';

    protected static ?string $title = 'Entregas registradas';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Entregas registradas para la solicitud')
            ->recordTitleAttribute('numero_entrega')
            ->defaultSort('fecha_entrega', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('numero_entrega')
                    ->label('Entrega')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('material.nombre_producto')
                    ->label('Material')
                    ->wrap()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('cantidad_entregada')
                    ->label('Cantidad entregada')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                Tables\Columns\TextColumn::make('tipo_entrega')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : '—')
                    ->color(fn (?string $state) => match ($state) {
                        'completa' => 'success',
                        'parcial' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : '—')
                    ->color(fn (?string $state) => match ($state) {
                        'en_transito' => 'warning',
                        'recibido' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('almacenDestino.nombre')
                    ->label('Almacén destino')
                    ->wrap()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('fecha_entrega')
                    ->label('Fecha de entrega')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->wrap()
                    ->toggleable(),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->recordUrl(null)
            ->emptyStateHeading('Aún no hay entregas registradas para esta solicitud');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        if ($ownerRecord instanceof SolicitudMaterial) {
            return $ownerRecord->estado === 'enviado';
        }

        return true;
    }
}
