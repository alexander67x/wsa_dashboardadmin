<?php

namespace App\Filament\Resources\Reportes\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReporteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Título')
                            ->disabled()
                            ->dehydrated(false),
                        
                        TextInput::make('proyecto.nombre_ubicacion')
                            ->label('Proyecto')
                            ->disabled()
                            ->dehydrated(false),
                        
                        TextInput::make('tarea.titulo')
                            ->label('Tarea')
                            ->disabled()
                            ->dehydrated(false),
                        
                        TextInput::make('estado')
                            ->label('Estado')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'aprobado' => 'Aprobado',
                                'rechazado' => 'Rechazado',
                                'enviado' => 'Pendiente',
                                'borrador' => 'Borrador',
                                default => ucfirst($state),
                            }),
                        
                        TextInput::make('fecha_reporte')
                            ->label('Fecha de Reporte')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y') : '—'),
                    ])
                    ->columns(2),
                
                Section::make('Detalles del Reporte')
                    ->schema([
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->rows(4),
                        
                        Textarea::make('dificultades_encontradas')
                            ->label('Dificultades Encontradas')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->rows(3)
                            ->default('—'),
                        
                        Textarea::make('materiales_utilizados')
                            ->label('Materiales Utilizados')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->rows(3)
                            ->default('—'),
                    ])
                    ->columns(2),
                
                Section::make('Información del Registro')
                    ->schema([
                        TextInput::make('registradoPor.nombre_completo')
                            ->label('Registrado por')
                            ->disabled()
                            ->dehydrated(false),
                        
                        TextInput::make('fecha_reporte')
                            ->label('Fecha de Registro')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : '—'),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Section::make('Aprobación')
                    ->schema([
                        TextInput::make('aprobadoPor.nombre_completo')
                            ->label('Aprobado por')
                            ->disabled()
                            ->dehydrated(false)
                            ->default('—'),
                        
                        TextInput::make('fecha_aprobacion')
                            ->label('Fecha de Aprobación')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : '—'),
                        
                        Textarea::make('observaciones_supervisor')
                            ->label('Observaciones del Supervisor')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->rows(3)
                            ->default('—'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->visible(fn ($record) => $record && in_array($record->estado ?? '', ['aprobado', 'rechazado'])),
                
                Section::make('Evidencias')
                    ->schema([
                        Textarea::make('archivos_list')
                            ->label('Archivos Adjuntos')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state, $record) {
                                if (!$record || !$record->archivos) {
                                    return 'No hay archivos adjuntos';
                                }
                                
                                $archivos = $record->archivos;
                                if ($archivos->isEmpty()) {
                                    return 'No hay archivos adjuntos';
                                }
                                
                                $lista = [];
                                foreach ($archivos as $archivo) {
                                    $url = $archivo->url ?? '#';
                                    if ($archivo->es_foto) {
                                        $lista[] = "📷 Foto: {$archivo->nombre_original} - Ver: {$url}";
                                    } else {
                                        $lista[] = "📄 Archivo: {$archivo->nombre_original} - Ver: {$url}";
                                    }
                                }
                                
                                return implode("\n", $lista);
                            })
                            ->rows(5),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record && $record->archivos && $record->archivos->isNotEmpty()),
            ]);
    }
}

