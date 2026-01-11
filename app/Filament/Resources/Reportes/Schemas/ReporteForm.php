<?php

namespace App\Filament\Resources\Reportes\Schemas;

use App\Filament\Components\ImageGallery;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReporteForm
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
                        return $state instanceof \DateTime ? $state->format('d/m/Y') : '—';
                    }),
                
                // Detalles del Reporte
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
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return 'No se registraron materiales utilizados';
                        }
                        
                        $record->loadMissing('materiales.material');
                        
                        if (!$record->materiales || $record->materiales->isEmpty()) {
                            return 'No se registraron materiales utilizados';
                        }
                        
                        $lista = [];
                        foreach ($record->materiales as $reporteMaterial) {
                            $material = $reporteMaterial->material;
                            $nombreMaterial = $material ? $material->nombre_producto : "Material ID: {$reporteMaterial->id_material}";
                            $cantidad = number_format($reporteMaterial->cantidad_usada, 2);
                            $unidad = $reporteMaterial->unidad_medida ?? ($material?->unidad_medida ?? '');
                            $observaciones = $reporteMaterial->observaciones ? " - {$reporteMaterial->observaciones}" : '';
                            
                            $lista[] = "• {$nombreMaterial}: {$cantidad} {$unidad}{$observaciones}";
                        }
                        
                        return implode("\n", $lista);
                    })
                    ->rows(5),
                
                // Información del Registro
                TextInput::make('registrado_por_nombre')
                    ->label('Registrado por')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return '—';
                        }
                        $record->loadMissing('registradoPor');
                        return $record->registradoPor?->nombre_completo ?? '—';
                    }),
                
                TextInput::make('fecha_reporte_2')
                    ->label('Fecha de Registro')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        $fecha = $record->fecha_reporte ?? null;
                        if (!$fecha) {
                            return '—';
                        }
                        if (is_string($fecha)) {
                            try {
                                $fecha = \Carbon\Carbon::parse($fecha);
                            } catch (\Exception $e) {
                                return $fecha;
                            }
                        }
                        return $fecha instanceof \DateTime ? $fecha->format('d/m/Y H:i') : '—';
                    }),
                
                // Aprobación (solo visible si está aprobado/rechazado)
                TextInput::make('aprobado_por_nombre')
                    ->label('Revisado por')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return '—';
                        }
                        $record->loadMissing('aprobadoPor');
                        if (! in_array($record->estado ?? null, ['aprobado', 'rechazado'])) {
                            return '—';
                        }

                        $prefix = $record->estado === 'aprobado' ? 'Aprobado por: ' : 'Rechazado por: ';
                        return $prefix . ($record->aprobadoPor?->nombre_completo ?? '—');
                    })
                    ->visible(fn ($record) => $record && in_array($record->estado ?? '', ['aprobado', 'rechazado'])),
                
                TextInput::make('fecha_aprobacion')
                    ->label('Fecha de Revisión')
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
                    ->visible(fn ($record) => $record && in_array($record->estado ?? '', ['aprobado', 'rechazado'])),
                
                Textarea::make('observaciones_supervisor')
                    ->label('Observaciones del Supervisor')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->rows(3)
                    ->default('—')
                    ->visible(fn ($record) => $record && in_array($record->estado ?? '', ['aprobado', 'rechazado'])),

                Textarea::make('historial_revisiones')
                    ->label('Historial de Revisiones')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->rows(4)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return '—';
                        }
                        $record->loadMissing('historiales.creadoPor');
                        if (! $record->historiales || $record->historiales->isEmpty()) {
                            return '—';
                        }

                        return $record->historiales->map(function ($historial) {
                            $fecha = optional($historial->created_at)->format('d/m/Y H:i');
                            $usuario = $historial->creadoPor?->nombre_completo ?? 'Sistema';
                            $tipo = ucfirst($historial->tipo);
                            $comentario = $historial->comentario ? " - {$historial->comentario}" : '';

                            return "{$fecha} • {$tipo} por {$usuario}{$comentario}";
                        })->implode("\n");
                    })
                    ->visible(fn ($record) => $record && $record->historiales && $record->historiales->isNotEmpty()),
                
                // Evidencias - Imágenes
                ImageGallery::make('images_gallery')
                    ->label('Fotos del Reporte')
                    ->columnSpanFull()
                    ->dehydrated(false)
                    ->visible(fn ($record) => $record && $record->archivos && $record->archivos->where('es_foto', true)->isNotEmpty()),
                
                // Archivos no-foto
                Textarea::make('archivos_list')
                    ->label('Otros Archivos Adjuntos')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record || !$record->archivos) {
                            return 'No hay otros archivos adjuntos';
                        }
                        
                        $archivos = $record->archivos->filter(fn ($archivo) => !$archivo->es_foto);
                        if ($archivos->isEmpty()) {
                            return 'No hay otros archivos adjuntos';
                        }
                        
                        $lista = [];
                        foreach ($archivos as $archivo) {
                            $url = $archivo->url ?? '#';
                            $lista[] = "📄 Archivo: {$archivo->nombre_original} - Ver: {$url}";
                        }
                        
                        return implode("\n", $lista);
                    })
                    ->rows(3)
                    ->visible(fn ($record) => $record && $record->archivos && $record->archivos->where('es_foto', false)->isNotEmpty()),
            ]);
    }
}

