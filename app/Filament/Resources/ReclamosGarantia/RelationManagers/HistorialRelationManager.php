<?php

namespace App\Filament\Resources\ReclamosGarantia\RelationManagers;

use App\Filament\Resources\ReclamosGarantia\Pages\ViewReclamoGarantia;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class HistorialRelationManager extends RelationManager
{
    protected static string $relationship = 'historial';

    protected static ?string $title = 'Historial';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Historial del reclamo')
            ->recordTitleAttribute('id_historial')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_anterior')
                    ->label('Estado anterior')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('estado_nuevo')
                    ->label('Estado nuevo'),
                Tables\Columns\TextColumn::make('accion')
                    ->label('Acción')
                    ->wrap()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('comentario')
                    ->label('Comentario')
                    ->limit(50)
                    ->tooltip(fn ($record): string => (string) ($record->comentario ?? ''))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('creadoPor.nombre_completo')
                    ->label('Usuario')
                    ->placeholder('Sistema'),
            ])
            ->headerActions([])
            ->actions([
                Action::make('ver_detalle')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalle del historial')
                    ->form([
                        TextInput::make('created_at')
                            ->label('Fecha')
                            ->disabled(),
                        TextInput::make('estado_anterior')
                            ->label('Estado anterior')
                            ->disabled()
                            ->placeholder('—'),
                        TextInput::make('estado_nuevo')
                            ->label('Estado nuevo')
                            ->disabled(),
                        TextInput::make('accion')
                            ->label('Acción')
                            ->disabled()
                            ->placeholder('—'),
                        Textarea::make('comentario')
                            ->label('Comentario')
                            ->rows(6)
                            ->disabled()
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextInput::make('usuario')
                            ->label('Usuario')
                            ->disabled()
                            ->placeholder('Sistema'),
                    ])
                    ->fillForm(fn ($record): array => [
                        'created_at' => optional($record->created_at)->format('d/m/Y H:i') ?? '—',
                        'estado_anterior' => $record->estado_anterior,
                        'estado_nuevo' => $record->estado_nuevo,
                        'accion' => $record->accion,
                        'comentario' => $record->comentario,
                        'usuario' => $record->creadoPor->nombre_completo ?? 'Sistema',
                    ]),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        return $pageClass === ViewReclamoGarantia::class;
    }
}
