<?php

namespace App\Filament\Resources\Incidencias\Schemas;

use App\Filament\Components\ImageGallery;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
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
                    ->formatStateUsing(function ($state, $record): string {
                        $tipo = $record?->tipo_incidencia ?? $state;
                        $tipo = is_string($tipo) ? trim($tipo) : $tipo;

                        if (! $tipo) {
                            return '—';
                        }

                        return match ($tipo) {
                            'falla_equipos' => 'Falla Equipos',
                            'retraso_material' => 'Retraso Material',
                            'problema_calidad' => 'Problema Calidad',
                            default => ucfirst(str_replace('_', ' ', $tipo)),
                        };
                    }),
                
                TextInput::make('severidad')
                    ->label('Severidad')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record): string {
                        $severidad = $record?->severidad ?? $state;
                        $severidad = is_string($severidad) ? trim($severidad) : $severidad;

                        return $severidad ? ucfirst($severidad) : '—';
                    }),
                
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
                        if (! $state) {
                            return '—';
                        }
                        if (is_string($state)) {
                            try {
                                $state = \Carbon\Carbon::parse($state);
                            } catch (\Exception $e) {
                                return $state;
                            }
                        }
                        if ($state instanceof \DateTimeInterface) {
                            return \Carbon\Carbon::instance($state)
                                ->timezone(config('app.timezone'))
                                ->format('d/m/Y H:i');
                        }

                        return '—';
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
                    ->label('Fecha de registro')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '—';
                        }
                        if (is_string($state)) {
                            try {
                                $state = \Carbon\Carbon::parse($state);
                            } catch (\Exception $e) {
                                return $state;
                            }
                        }
                        if ($state instanceof \DateTimeInterface) {
                            return \Carbon\Carbon::instance($state)
                                ->timezone(config('app.timezone'))
                                ->format('d/m/Y H:i');
                        }

                        return '—';
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

                Textarea::make('comentario_registro')
                    ->label('Comentario de registro')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->rows(3)
                    ->default('—')
                    ->formatStateUsing(function ($state, $record): string {
                        if (! $record) {
                            return '—';
                        }
                        $record->loadMissing('historial');
                        $registro = $record->historial
                            ->firstWhere('estado_nuevo', 'registrada');
                        return $registro?->comentario ?: '—';
                    })
                    ->visible(fn ($record) => $record && in_array($record->estado, ['registrada', 'cerrada'], true)),
                
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

                ViewField::make('incidencia_map')
                    ->label('Mapa')
                    ->columnSpanFull()
                    ->view('filament.components.incidencia-map')
                    ->viewData(fn ($record) => [
                        'recordId' => $record?->id_incidencia,
                        'lat' => $record?->latitud,
                        'lng' => $record?->longitud,
                        'label' => $record?->titulo ?? 'Incidencia',
                    ]),
                
                // Evidencias - Imágenes
                ImageGallery::make('images_gallery')
                    ->label('Fotos de la Incidencia')
                    ->columnSpanFull()
                    ->dehydrated(false)
                    ->visible(fn ($record) => $record && $record->archivos && $record->archivos->where('es_foto', true)->isNotEmpty()),
            ]);
    }
}

