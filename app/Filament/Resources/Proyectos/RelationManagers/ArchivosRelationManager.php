<?php

namespace App\Filament\Resources\Proyectos\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ArchivosRelationManager extends RelationManager
{
    protected static string $relationship = 'archivos';

    protected static ?string $title = 'Documentos';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Documentos del proyecto')
            ->recordTitleAttribute('nombre_original')
            ->columns([
                Tables\Columns\TextColumn::make('nombre_original')
                    ->label('Nombre')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ruta_storage')
                    ->label('URL')
                    ->formatStateUsing(fn (string $state) => str($state)->limit(40))
                    ->url(fn ($record) => $record->ruta_storage, true)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('tipo_mime')
                    ->label('Tipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tamano_bytes')
                    ->label('Tamaño')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2) . ' KB' : '—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Subido')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([])
            ->actions([
                Action::make('descargar')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => $record->ruta_storage, true)
                    ->openUrlInNewTab(),
                DeleteAction::make(),
            ]);
    }
}
