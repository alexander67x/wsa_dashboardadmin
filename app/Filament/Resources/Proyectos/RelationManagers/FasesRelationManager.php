<?php

namespace App\Filament\Resources\Proyectos\RelationManagers;

use App\Filament\Resources\Fases\FaseResource;
use App\Filament\Resources\Proyectos\Pages\ViewProyecto;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FasesRelationManager extends RelationManager
{
    protected static string $relationship = 'fases';

    protected static ?string $title = 'Fases del proyecto';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Fases asociadas')
            ->recordTitleAttribute('nombre_fase')
            ->defaultSort('orden')
            ->columns([
                Tables\Columns\TextColumn::make('nombre_fase')
                    ->label('Fase')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('orden')
                    ->label('Orden')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'planificada' => 'Planificada',
                        'en_ejecucion' => 'En ejecución',
                        'finalizada' => 'Finalizada',
                        'pausada' => 'Pausada',
                        default => $state ? ucfirst($state) : '—',
                    }),
                Tables\Columns\TextColumn::make('porcentaje_avance')
                    ->label('Avance')
                    ->suffix('%')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 1)),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->headerActions([])
            ->actions([])
            ->recordUrl(fn ($record): string => FaseResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Este proyecto no tiene fases asociadas');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        return $pageClass === ViewProyecto::class;
    }
}
