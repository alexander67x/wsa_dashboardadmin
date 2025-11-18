<?php

namespace App\Filament\Resources\SolicitudesMateriales\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SolicitudForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Información General
                TextInput::make('numero_solicitud')
                    ->label('Número de Solicitud')
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
                
                TextInput::make('solicitado_por_nombre')
                    ->label('Solicitado por')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return '—';
                        }
                        $record->loadMissing('solicitadoPor');
                        return $record->solicitadoPor?->nombre_completo ?? '—';
                    }),
                
                TextInput::make('cargo_solicitante')
                    ->label('Cargo del Solicitante')
                    ->disabled()
                    ->dehydrated(false),
                
                TextInput::make('centro_costos')
                    ->label('Centro de Costos')
                    ->disabled()
                    ->dehydrated(false),
                
                TextInput::make('fecha_solicitud')
                    ->label('Fecha de Solicitud')
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
                
                TextInput::make('fecha_requerida')
                    ->label('Fecha Requerida')
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
                
                TextInput::make('estado')
                    ->label('Estado')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aprobada' => 'Aprobada',
                        'rechazada' => 'Rechazada',
                        'pendiente' => 'Pendiente',
                        'enviado' => 'Enviado',
                        'recibida' => 'Recibida',
                        'cancelada' => 'Cancelada',
                        'borrador' => 'Borrador',
                        default => ucfirst($state),
                    }),
                
                TextInput::make('urgente')
                    ->label('Urgente')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),
                
                // Detalles Adicionales
                TextInput::make('motivo')
                    ->label('Motivo')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                
                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->disabled()
                    ->dehydrated(false)
                    ->rows(3)
                    ->columnSpanFull(),
                
                // Materiales Faltantes (si requiere compra)
                Textarea::make('materiales_faltantes')
                    ->label('Materiales que Requieren Compra')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record && $record->requiere_compra)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return 'No hay materiales faltantes';
                        }
                        
                        $record->loadMissing('items.material');
                        
                        $materialesFaltantes = $record->items->filter(function ($item) {
                            return $item->requiere_compra === true;
                        });
                        
                        if ($materialesFaltantes->isEmpty()) {
                            return 'No hay materiales faltantes';
                        }
                        
                        $lista = [];
                        foreach ($materialesFaltantes as $item) {
                            $material = $item->material;
                            $nombreMaterial = $material ? $material->nombre_producto : "Material ID: {$item->id_material}";
                            $cantidadSolicitada = number_format($item->cantidad_solicitada, 2);
                            $cantidadDisponiblePadre = $item->cantidad_disponible_padre !== null ? number_format($item->cantidad_disponible_padre, 2) : '0.00';
                            $cantidadFaltante = number_format($item->cantidad_faltante, 2);
                            $unidad = $item->unidad;
                            
                            $linea = "• {$nombreMaterial}:";
                            $linea .= "\n  - Solicitado: {$cantidadSolicitada} {$unidad}";
                            $linea .= "\n  - Disponible en almacén padre: {$cantidadDisponiblePadre} {$unidad}";
                            $linea .= "\n  - FALTANTE (requiere compra): {$cantidadFaltante} {$unidad}";
                            
                            $lista[] = $linea;
                        }
                        
                        return implode("\n\n", $lista);
                    })
                    ->rows(8),
                
                // Materiales Solicitados
                Textarea::make('items_list')
                    ->label('Materiales Solicitados')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return 'No hay materiales solicitados';
                        }
                        
                        $record->loadMissing('items.material');
                        
                        if (!$record->items || $record->items->isEmpty()) {
                            return 'No hay materiales solicitados';
                        }
                        
                        $lista = [];
                        foreach ($record->items as $item) {
                            $material = $item->material;
                            $nombreMaterial = $material ? $material->nombre_producto : "Material ID: {$item->id_material}";
                            $cantidadSolicitada = number_format($item->cantidad_solicitada, 2);
                            $cantidadAprobada = $item->cantidad_aprobada ? number_format($item->cantidad_aprobada, 2) : '—';
                            $cantidadEntregada = number_format($item->cantidad_entregada, 2);
                            $unidad = $item->unidad;
                            $pendiente = number_format($item->pendiente_entregar, 2);
                            
                            $linea = "• {$nombreMaterial}:";
                            $linea .= "\n  - Solicitado: {$cantidadSolicitada} {$unidad}";
                            
                            // Mostrar información de stock padre si existe
                            if ($item->cantidad_disponible_padre !== null) {
                                $cantidadDisponiblePadre = number_format($item->cantidad_disponible_padre, 2);
                                $linea .= "\n  - Disponible en almacén padre: {$cantidadDisponiblePadre} {$unidad}";
                                if ($item->requiere_compra) {
                                    $cantidadFaltante = number_format($item->cantidad_faltante, 2);
                                    $linea .= "\n  - Faltante: {$cantidadFaltante} {$unidad}";
                                }
                            }
                            
                            $linea .= "\n  - Aprobado: {$cantidadAprobada} {$unidad}";
                            
                            if ($record->estado === 'aprobada' || $record->estado === 'enviado' || $record->estado === 'recibida') {
                                $linea .= "\n  - Entregado: {$cantidadEntregada} {$unidad}";
                                $linea .= "\n  - Pendiente: {$pendiente} {$unidad}";
                                $porcentaje = number_format($item->porcentaje_entregado, 1);
                                $linea .= "\n  - % Entregado: {$porcentaje}%";
                            }
                            
                            if ($item->justificacion) {
                                $linea .= "\n  - Justificación: {$item->justificacion}";
                            }
                            
                            if ($item->observaciones) {
                                $linea .= "\n  - Observaciones: {$item->observaciones}";
                            }
                            
                            $lista[] = $linea;
                        }
                        
                        return implode("\n\n", $lista);
                    })
                    ->rows(10),

                // Trazabilidad de entregas
                Textarea::make('entregas_list')
                    ->label('Entregas de Material')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record && $record->deliveries && $record->deliveries->isNotEmpty())
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record) {
                            return 'No hay entregas registradas';
                        }

                        $record->loadMissing('deliveries.material', 'deliveries.entregadoPor', 'deliveries.recibidoPor');

                        if (! $record->deliveries || $record->deliveries->isEmpty()) {
                            return 'No hay entregas registradas';
                        }

                        $lista = [];

                        foreach ($record->deliveries as $delivery) {
                            $fecha = $delivery->fecha_entrega
                                ? $delivery->fecha_entrega->format('d/m/Y H:i')
                                : '-';

                            $material = $delivery->material;
                            $nombreMaterial = $material ? $material->nombre_producto : "Material ID: {$delivery->id_material}";

                            $cantidad = number_format((float) $delivery->cantidad_entregada, 2);
                            $estadoEntrega = $delivery->estado ?? 'sin estado';

                            $entregadoPor = $delivery->entregadoPor?->nombre_completo;
                            $recibidoPor = $delivery->recibidoPor?->nombre_completo;

                            $linea = "● {$fecha} - {$nombreMaterial}";
                            $linea .= "\n  - Cantidad entregada: {$cantidad}";
                            $linea .= "\n  - Estado entrega: {$estadoEntrega}";

                            if ($entregadoPor) {
                                $linea .= "\n  - Registrado por: {$entregadoPor}";
                            }

                            if ($recibidoPor) {
                                $linea .= "\n  - Recibido por: {$recibidoPor}";
                            }

                            if ($delivery->observaciones) {
                                $linea .= "\n  - Observaciones: {$delivery->observaciones}";
                            }

                            $lista[] = $linea;
                        }

                        return implode("\n\n", $lista);
                    })
                    ->rows(8),
                
                // Información de Aprobación
                TextInput::make('aprobada_por_nombre')
                    ->label('Aprobada por')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record) {
                            return '—';
                        }
                        $record->loadMissing('aprobadaPor');
                        return $record->aprobadaPor?->nombre_completo ?? '—';
                    })
                    ->visible(fn ($record) => $record && in_array($record->estado ?? '', ['aprobada', 'rechazada', 'enviado', 'recibida'])),
                
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
                    ->visible(fn ($record) => $record && in_array($record->estado ?? '', ['aprobada', 'rechazada', 'enviado', 'recibida'])),
            ]);
    }
}
