<?php

namespace App\Filament\Resources\Incidencias\Pages;

use App\Filament\Resources\Incidencias\IncidenciaResource;
use App\Models\Empleado;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViewIncidencia extends ViewRecord
{
    protected static string $resource = IncidenciaResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar relaciones necesarias
        $this->record->load(['proyecto', 'tarea', 'reportadoPor', 'asignadoA', 'archivos', 'historial.usuarioCambio']);
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];
        $estadoActual = $this->record->estado;

        // Abierta → En Proceso
        if ($estadoActual === 'abierta' || $estadoActual === 'reabierta') {
            $actions[] = Action::make('en_proceso')
                ->label('Poner en Proceso')
                ->icon('heroicon-o-arrow-right')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Poner Incidencia en Proceso')
                ->modalDescription('¿Estás seguro de que deseas poner esta incidencia en proceso?')
                ->form([
                    Select::make('asignado_a')
                        ->label('Asignar a')
                        ->options(fn () => Empleado::where('activo', true)
                            ->orderBy('nombre_completo')
                            ->pluck('nombre_completo', 'cod_empleado')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Selecciona el responsable que trabajará en esta incidencia'),
                    Textarea::make('comentario')
                        ->label('Comentario (opcional)')
                        ->placeholder('Agregar comentarios...')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $this->cambiarEstado('en_proceso', $data['comentario'] ?? null, $data['asignado_a']);
                });
        }

        // En Proceso → Resuelta
        if ($estadoActual === 'en_proceso') {
            $actions[] = Action::make('resuelta')
                ->label('Marcar como Resuelta')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Marcar Incidencia como Resuelta')
                ->modalDescription('¿Estás seguro de que esta incidencia ha sido resuelta?')
                ->form([
                    Textarea::make('solucion_implementada')
                        ->label('Solución Implementada')
                        ->placeholder('Describe la solución implementada...')
                        ->rows(4)
                        ->required()
                        ->maxLength(1000),
                    Textarea::make('comentario')
                        ->label('Comentario (opcional)')
                        ->placeholder('Agregar comentarios adicionales...')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $this->cambiarEstado('resuelta', $data['comentario'] ?? null, null, $data['solucion_implementada']);
                });
        }

        // Resuelta → Verificación
        if ($estadoActual === 'resuelta') {
            $actions[] = Action::make('verificacion')
                ->label('Enviar a Verificación')
                ->icon('heroicon-o-magnifying-glass')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Enviar Incidencia a Verificación')
                ->modalDescription('¿Estás seguro de que deseas enviar esta incidencia a verificación?')
                ->form([
                    Textarea::make('comentario')
                        ->label('Comentario (opcional)')
                        ->placeholder('Agregar comentarios...')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $this->cambiarEstado('verificacion', $data['comentario'] ?? null);
                });
        }

        // Verificación → Cerrada
        if ($estadoActual === 'verificacion') {
            $actions[] = Action::make('cerrada')
                ->label('Cerrar Incidencia')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Cerrar Incidencia')
                ->modalDescription('¿Estás seguro de que deseas cerrar esta incidencia?')
                ->form([
                    Textarea::make('comentario')
                        ->label('Comentario (opcional)')
                        ->placeholder('Agregar comentarios sobre el cierre...')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $this->cambiarEstado('cerrada', $data['comentario'] ?? null);
                });

            $actions[] = Action::make('reabierta')
                ->label('Reabrir Incidencia')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reabrir Incidencia')
                ->modalDescription('¿Estás seguro de que deseas reabrir esta incidencia? Se requiere una explicación del problema.')
                ->form([
                    Textarea::make('comentario')
                        ->label('Motivo de Reapertura (requerido)')
                        ->placeholder('Explica el motivo de la reapertura...')
                        ->rows(4)
                        ->required()
                        ->maxLength(1000),
                    Select::make('asignado_a')
                        ->label('Reasignar a')
                        ->options(fn () => Empleado::where('activo', true)
                            ->orderBy('nombre_completo')
                            ->pluck('nombre_completo', 'cod_empleado')
                            ->toArray())
                        ->searchable()
                        ->preload()
                        ->helperText('Selecciona el responsable que trabajará en la resolución'),
                ])
                ->action(function (array $data): void {
                    $this->cambiarEstado('reabierta', $data['comentario'], $data['asignado_a'] ?? null);
                });
        }


        return $actions;
    }

    protected function cambiarEstado(
        string $nuevoEstado,
        ?string $comentario = null,
        ?int $asignadoA = null,
        ?string $solucionImplementada = null
    ): void {
        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            
            // Buscar el empleado asociado al usuario
            $empleado = Empleado::where('email', $user->email)->first();
            
            if (!$empleado) {
                Notification::make()
                    ->title('Error')
                    ->body('No se encontró un empleado asociado a tu usuario.')
                    ->danger()
                    ->send();
                DB::rollBack();
                return;
            }

            $estadoAnterior = $this->record->estado;

            // Preparar datos de actualización
            $datosActualizacion = [
                'estado' => $nuevoEstado,
            ];

            if ($asignadoA) {
                $datosActualizacion['asignado_a'] = $asignadoA;
            }

            if ($solucionImplementada && $nuevoEstado === 'resuelta') {
                $datosActualizacion['solucion_implementada'] = $solucionImplementada;
                $datosActualizacion['fecha_resolucion'] = now();
            }

            // Actualizar estado de la incidencia
            $this->record->update($datosActualizacion);

            // Crear registro en historial
            \App\Models\IncidenciaHistorial::create([
                'id_incidencia' => $this->record->getKey(),
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'comentario' => $comentario,
                'accion_tomada' => $this->obtenerAccionTomada($nuevoEstado),
                'usuario_cambio' => $empleado->cod_empleado,
                'fecha_cambio' => now(),
            ]);

            DB::commit();

            $mensajes = [
                'en_proceso' => 'La incidencia ha sido puesta en proceso.',
                'resuelta' => 'La incidencia ha sido marcada como resuelta.',
                'verificacion' => 'La incidencia ha sido enviada a verificación.',
                'cerrada' => 'La incidencia ha sido cerrada.',
                'reabierta' => 'La incidencia ha sido reabierta.',
            ];

            Notification::make()
                ->title('Estado Actualizado')
                ->body($mensajes[$nuevoEstado] ?? 'El estado ha sido actualizado.')
                ->success()
                ->send();

            $this->redirect(IncidenciaResource::getUrl('index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al cambiar el estado: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function obtenerAccionTomada(string $estado): string
    {
        return match ($estado) {
            'en_proceso' => 'Incidencia puesta en proceso',
            'resuelta' => 'Incidencia marcada como resuelta',
            'verificacion' => 'Incidencia enviada a verificación',
            'cerrada' => 'Incidencia cerrada',
            'reabierta' => 'Incidencia reabierta',
            default => 'Cambio de estado',
        };
    }
}

