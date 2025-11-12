<?php

namespace App\Filament\Resources\Reportes\Schemas;

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
                    ->rows(3)
                    ->default('—'),
                
                // Información del Registro
                TextInput::make('registradoPor.nombre_completo')
                    ->label('Registrado por')
                    ->disabled()
                    ->dehydrated(false),
                
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
                TextInput::make('aprobadoPor.nombre_completo')
                    ->label('Aprobado por')
                    ->disabled()
                    ->dehydrated(false)
                    ->default('—')
                    ->visible(fn ($record) => $record && in_array($record->estado ?? '', ['aprobado', 'rechazado'])),
                
                TextInput::make('fecha_aprobacion')
                    ->label('Fecha de Aprobación')
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
                
                // Evidencias
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
                    ->rows(5)
                    ->visible(fn ($record) => $record && $record->archivos && $record->archivos->isNotEmpty()),
            ]);
    }
}

