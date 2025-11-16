<?php

namespace App\Filament\Resources\Incidencias\Schemas;

use App\Filament\Components\ImageGallery;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IncidenciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Información General
                TextInput::make('titulo')
                    ->label('Título')
                    ->disabled()
                    ->dehydrated(false),
                
                TextInput::make('proyecto_nombre')
                    ->label('Proyecto')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return '—';
                        }
                        $record->loadMissing('proyecto');
                        return $record->proyecto?->nombre_ubicacion ?? '—';
                    }),
                
                TextInput::make('tarea_titulo')
                    ->label('Tarea')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return '—';
                        }
                        $record->loadMissing('tarea');
                        return $record->tarea?->titulo ?? '—';
                    }),
                
                TextInput::make('tipo_incidencia')
                    ->label('Tipo de Incidencia')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn (?string $tipo): string => match ($tipo) {
                        null => '—',
                        'falla_equipos' => 'Falla Equipos',
                        'retraso_material' => 'Retraso Material',
                        'problema_calidad' => 'Problema Calidad',
                        default => ucfirst(str_replace('_', ' ', $tipo)),
                    }),
                
                TextInput::make('severidad')
                    ->label('Severidad')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn (?string $severidad): string => $severidad ? ucfirst($severidad) : '—'),
                
                TextInput::make('estado')
                    ->label('Estado')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        null => '—',
                        'en_proceso' => 'En Proceso',
                        'verificacion' => 'Verificación',
                        default => ucfirst($state),
                    }),
                
                TextInput::make('fecha_reportado')
                    ->label('Fecha de Reporte')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state) {
                        if (!$state) {
                            return '—';
                        }
                        if (is_string($state)) {
                            try {
                                $state = \Carbon\Carbon::parse($state);
                            } catch (\Exception $e) {
                                return $state;
                            }
                        }
                        return $state instanceof \DateTime ? $state->format('d/m/Y H:i') : '—';
                    }),
                
                // Detalles de la Incidencia
                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->rows(4),
                
                // Información del Reporte
                TextInput::make('reportado_por_nombre')
                    ->label('Reportado por')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return '—';
                        }
                        $record->loadMissing('reportadoPor');
                        return $record->reportadoPor?->nombre_completo ?? '—';
                    }),
                
                TextInput::make('asignado_a_nombre')
                    ->label('Asignado a')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return '—';
                        }
                        $record->loadMissing('asignadoA');
                        return $record->asignadoA?->nombre_completo ?? '—';
                    })
                    ->visible(fn ($record) => $record && $record->asignado_a),
                
                // Resolución
                TextInput::make('fecha_resolucion')
                    ->label('Fecha de Resolución')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state) {
                        if (!$state) {
                            return '—';
                        }
                        if (is_string($state)) {
                            try {
                                $state = \Carbon\Carbon::parse($state);
                            } catch (\Exception $e) {
                                return $state;
                            }
                        }
                        return $state instanceof \DateTime ? $state->format('d/m/Y H:i') : '—';
                    })
                    ->visible(fn ($record) => $record && $record->fecha_resolucion),
                
                Textarea::make('solucion_implementada')
                    ->label('Solución Implementada')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->rows(3)
                    ->default('—')
                    ->visible(fn ($record) => $record && $record->solucion_implementada),
                
                // Ubicación
                TextInput::make('ubicacion')
                    ->label('Ubicación (GPS)')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return '—';
                        }
                        if ($record->latitud && $record->longitud) {
                            return "Lat: {$record->latitud}, Lon: {$record->longitud}";
                        }
                        return '—';
                    })
                    ->visible(fn ($record) => $record && $record->latitud && $record->longitud),
                
                // Evidencias - Imágenes
                ImageGallery::make('images_gallery')
                    ->label('Fotos de la Incidencia')
                    ->columnSpanFull()
                    ->dehydrated(false)
                    ->visible(fn ($record) => $record && $record->archivos && $record->archivos->where('es_foto', true)->isNotEmpty()),
            ]);
    }
}

