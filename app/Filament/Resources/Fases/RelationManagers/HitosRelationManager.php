<?php

namespace App\Filament\Resources\Fases\RelationManagers;

use App\Filament\Resources\Fases\Pages\ViewFase;
use App\Filament\Resources\Hitos\HitoResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class HitosRelationManager extends RelationManager
{
    protected static string $relationship = 'hitos';

    protected static ?string $title = 'Hitos de la fase';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Hitos asociados')
            ->recordTitleAttribute('titulo')
            ->defaultSort('fecha_hito')
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Hito')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pendiente' => 'Pendiente',
                        'en_ejecucion' => 'En ejecución',
                        'completado' => 'Completado',
                        'atrasado' => 'Atrasado',
                        default => $state ? ucfirst($state) : '—',
                    }),
                Tables\Columns\TextColumn::make('fecha_hito')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_final_hito')
                    ->label('Objetivo')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : 'Intermedio'),
            ])
            ->headerActions([])
            ->actions([])
            ->recordUrl(fn ($record): string => HitoResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('Esta fase no tiene hitos asociados');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        return $pageClass === ViewFase::class;
    }
}
