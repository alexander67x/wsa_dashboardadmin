<?php

namespace App\Filament\Resources\Tareas\RelationManagers;

use App\Filament\Resources\Reportes\ReporteResource;
use App\Filament\Resources\Tareas\Pages\ViewTarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ReportesRelationManager extends RelationManager
{
    protected static string $relationship = 'reportes';

    protected static ?string $title = 'Reportes de avance';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Reportes asociados a la tarea')
            ->recordTitleAttribute('titulo')
            ->defaultSort('fecha_reporte', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(60),
                Tables\Columns\TextColumn::make('fecha_reporte')
                    ->label('Fecha de reporte')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aprobado' => 'success',
                        'rechazado' => 'danger',
                        'enviado' => 'warning',
                        'borrador' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        'enviado' => 'Pendiente',
                        'borrador' => 'Borrador',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('registradoPor.nombre_completo')
                    ->label('Registrado por')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->headerActions([])
            ->actions([])
            ->recordUrl(fn ($record): string => ReporteResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Esta tarea no tiene reportes asociados');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        return $pageClass === ViewTarea::class;
    }
}
