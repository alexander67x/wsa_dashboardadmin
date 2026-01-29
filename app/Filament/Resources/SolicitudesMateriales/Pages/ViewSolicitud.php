<?php

namespace App\Filament\Resources\SolicitudesMateriales\Pages;

use App\Filament\Resources\SolicitudesMateriales\SolicitudMaterialResource;
use App\Models\Almacen;
use App\Models\Empleado;
use App\Models\SolicitudHistorial;
use App\Models\StockAlmacen;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViewSolicitud extends ViewRecord
{
    protected static string $resource = SolicitudMaterialResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar relaciones necesarias
        $this->record->load([
            'proyecto',
            'tarea',
            'solicitadoPor',
            'aprobadaPor',
            'items.material',
            'items.lote'
        ]);
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];
        $userRole = Auth::user()?->empleado?->role?->slug;
        $canApprove = Auth::user()?->hasPermission('materials.requests.approve') ?? false;

        $actions[] = Action::make('ver_trazabilidad')
            ->label('Ver trazabilidad')
            ->icon('heroicon-o-arrow-path')
            ->url(SolicitudMaterialResource::getUrl('traceabilidad', ['record' => $this->record]));

        // Solo mostrar acciones de aprobar/rechazar si la solicitud está pendiente o en borrador
        if ($canApprove && in_array($this->record->estado, ['borrador', 'pendiente'])) {
            // Cargar items para verificar si requiere compra
            $this->record->loadMissing('items');
            $requiereCompra = $this->record->requiere_compra;
            
            // Si requiere compra, mostrar 3 opciones
            if ($requiereCompra) {
                $actions[] = Action::make('aprobar_con_compra')
                    ->label('Aprobar con Compra')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Solicitud Asumiendo Compra')
                    ->modalDescription('Esta solicitud requiere compra de materiales faltantes. ¿Deseas aprobar asumiendo que se comprarán los materiales faltantes?')
                    ->form([
                        Textarea::make('observaciones')
                            ->label('Observaciones (opcional)')
                            ->placeholder('Agregar comentarios sobre la aprobación...')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (array $data): void {
                        $this->aprobarSolicitudConCompra($data['observaciones'] ?? null);
                    });

                $actions[] = Action::make('aprobar_solo_stock')
                    ->label('Aprobar Solo Stock Disponible')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Solo con Stock Disponible')
                    ->modalDescription('Esta acción aprobará solo la cantidad disponible en el almacén padre, sin comprar materiales faltantes. ¿Estás seguro?')
                    ->form([
                        Textarea::make('observaciones')
                            ->label('Observaciones (opcional)')
                            ->placeholder('Agregar comentarios sobre la aprobación...')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (array $data): void {
                        $this->aprobarSolicitudSoloStock($data['observaciones'] ?? null);
                    });
            } else {
                // Si no requiere compra, mostrar aprobación normal
                $actions[] = Action::make('aprobar')
                    ->label('Aprobar Solicitud')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Solicitud de Materiales')
                    ->modalDescription('¿Estás seguro de que deseas aprobar esta solicitud?')
                    ->form([
                        Textarea::make('observaciones')
                            ->label('Observaciones (opcional)')
                            ->placeholder('Agregar comentarios sobre la aprobación...')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (array $data): void {
                        $this->aprobarSolicitud($data['observaciones'] ?? null);
                    });
            }

            $actions[] = Action::make('rechazar')
                ->label('Rechazar Solicitud')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rechazar Solicitud de Materiales')
                ->modalDescription('¿Estás seguro de que deseas rechazar esta solicitud?')
                ->form([
                    Textarea::make('observaciones')
                        ->label('Observaciones (requerido)')
                        ->placeholder('Explica el motivo del rechazo...')
                        ->rows(3)
                        ->required()
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $this->rechazarSolicitud($data['observaciones']);
                });
        }
        // Permitir a Adquisiciones marcar como enviado cuando ya está aprobada
        if ($this->record->estado === 'aprobada' && $userRole === 'adquisiciones') {
            $actions[] = Action::make('marcar_enviado')
                ->label('Marcar como Enviado')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Confirmar despacho de materiales')
                ->modalDescription('Confirma que los materiales fueron despachados hacia el proyecto. La solicitud pasará a estado "enviado".')
                ->form([
                    Textarea::make('observaciones')
                        ->label('Observaciones (opcional)')
                        ->placeholder('Notas sobre el despacho...')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $this->marcarSolicitudEnviada($data['observaciones'] ?? null);
                });
        }

        return $actions;
    }

    protected function aprobarSolicitud(?string $observaciones): void
    {
        if (! Auth::user()?->hasPermission('materials.requests.approve')) {
            Notification::make()
                ->title('No tienes permisos para aprobar solicitudes.')
                ->danger()
                ->send();
            return;
        }

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

            // Verificar que la solicitud tenga items
            if (!$this->record->items || $this->record->items->isEmpty()) {
                Notification::make()
                    ->title('Error')
                    ->body('La solicitud no tiene materiales asociados. No se puede aprobar.')
                    ->danger()
                    ->send();
                DB::rollBack();
                return;
            }

            // Actualizar estado de la solicitud
            $this->record->update([
                'estado' => 'aprobada',
                'fecha_aprobacion' => now(),
                'aprobada_por' => $empleado->cod_empleado,
                'observaciones' => $observaciones ? ($this->record->observaciones ? $this->record->observaciones . "\n\nAprobación: " . $observaciones : "Aprobación: " . $observaciones) : $this->record->observaciones,
            ]);

            // Si no hay cantidad_aprobada en los items, establecerla igual a cantidad_solicitada
            foreach ($this->record->items as $item) {
                if (!$item->cantidad_aprobada) {
                    $item->update([
                        'cantidad_aprobada' => $item->cantidad_solicitada
                    ]);
                }
            }

            // Registrar evento en historial
            $this->registrarEventoHistorial('aprobada', $observaciones);

            DB::commit();

            Notification::make()
                ->title('Solicitud Aprobada')
                ->body('La solicitud de materiales ha sido aprobada exitosamente.')
                ->success()
                ->send();

            $this->redirect(SolicitudMaterialResource::getUrl('index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al aprobar la solicitud: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function aprobarSolicitudConCompra(?string $observaciones): void
    {
        if (! Auth::user()?->hasPermission('materials.requests.approve')) {
            Notification::make()
                ->title('No tienes permisos para aprobar solicitudes.')
                ->danger()
                ->send();
            return;
        }

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

            // Verificar que la solicitud tenga items
            if (!$this->record->items || $this->record->items->isEmpty()) {
                Notification::make()
                    ->title('Error')
                    ->body('La solicitud no tiene materiales asociados. No se puede aprobar.')
                    ->danger()
                    ->send();
                DB::rollBack();
                return;
            }

            // Actualizar estado de la solicitud
            $accionTexto = 'Aprobada asumiendo compra de materiales faltantes';
            $observacionesTexto = $observaciones 
                ? ($this->record->observaciones ? $this->record->observaciones . "\n\n{$accionTexto}: " . $observaciones : "{$accionTexto}: " . $observaciones)
                : ($this->record->observaciones ? $this->record->observaciones . "\n\n{$accionTexto}" : $accionTexto);

            $this->record->update([
                'estado' => 'aprobada',
                'fecha_aprobacion' => now(),
                'aprobada_por' => $empleado->cod_empleado,
                'observaciones' => $observacionesTexto,
            ]);

            // Aprobar cantidad completa solicitada (asumiendo compra)
            foreach ($this->record->items as $item) {
                $item->update([
                    'cantidad_aprobada' => $item->cantidad_solicitada
                ]);
            }

            // Registrar evento en historial
            $this->registrarEventoHistorial('aprobada_con_compra', $observaciones);

            DB::commit();

            Notification::make()
                ->title('Solicitud Aprobada con Compra')
                ->body('La solicitud ha sido aprobada asumiendo compra de materiales faltantes.')
                ->success()
                ->send();

            $this->redirect(SolicitudMaterialResource::getUrl('index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al aprobar la solicitud: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function aprobarSolicitudSoloStock(?string $observaciones): void
    {
        if (! Auth::user()?->hasPermission('materials.requests.approve')) {
            Notification::make()
                ->title('No tienes permisos para aprobar solicitudes.')
                ->danger()
                ->send();
            return;
        }

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

            // Verificar que la solicitud tenga items
            if (!$this->record->items || $this->record->items->isEmpty()) {
                Notification::make()
                    ->title('Error')
                    ->body('La solicitud no tiene materiales asociados. No se puede aprobar.')
                    ->danger()
                    ->send();
                DB::rollBack();
                return;
            }

            // Actualizar estado de la solicitud
            $accionTexto = 'Aprobada solo con stock disponible en almacén padre';
            $observacionesTexto = $observaciones 
                ? ($this->record->observaciones ? $this->record->observaciones . "\n\n{$accionTexto}: " . $observaciones : "{$accionTexto}: " . $observaciones)
                : ($this->record->observaciones ? $this->record->observaciones . "\n\n{$accionTexto}" : $accionTexto);

            $this->record->update([
                'estado' => 'aprobada',
                'fecha_aprobacion' => now(),
                'aprobada_por' => $empleado->cod_empleado,
                'observaciones' => $observacionesTexto,
            ]);

            // Aprobar solo lo disponible en almacén padre
            foreach ($this->record->items as $item) {
                $cantidadAprobar = $item->cantidad_disponible_padre ?? 0;
                $item->update([
                    'cantidad_aprobada' => $cantidadAprobar
                ]);
            }

            // Registrar evento en historial
            $this->registrarEventoHistorial('aprobada_solo_stock', $observaciones);

            DB::commit();

            Notification::make()
                ->title('Solicitud Aprobada Solo con Stock Disponible')
                ->body('La solicitud ha sido aprobada solo con la cantidad disponible en el almacén padre.')
                ->success()
                ->send();

            $this->redirect(SolicitudMaterialResource::getUrl('index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al aprobar la solicitud: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function rechazarSolicitud(string $observaciones): void
    {
        if (! Auth::user()?->hasPermission('materials.requests.approve')) {
            Notification::make()
                ->title('No tienes permisos para rechazar solicitudes.')
                ->danger()
                ->send();
            return;
        }

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

            // Actualizar estado de la solicitud
            $this->record->update([
                'estado' => 'rechazada',
                'fecha_aprobacion' => now(),
                'aprobada_por' => $empleado->cod_empleado,
                'observaciones' => $this->record->observaciones ? $this->record->observaciones . "\n\nRechazo: " . $observaciones : "Rechazo: " . $observaciones,
            ]);

            // Registrar evento en historial
            $this->registrarEventoHistorial('rechazada', $observaciones);

            DB::commit();

            Notification::make()
                ->title('Solicitud Rechazada')
                ->body('La solicitud de materiales ha sido rechazada.')
                ->warning()
                ->send();

            $this->redirect(SolicitudMaterialResource::getUrl('index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al rechazar la solicitud: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    protected function marcarSolicitudEnviada(?string $observaciones): void
    {
        try {
            DB::beginTransaction();

            if ($this->record->estado !== 'aprobada') {
                Notification::make()
                    ->title('Estado inválido')
                    ->body('Solo se puede despachar una solicitud cuando está aprobada.')
                    ->danger()
                    ->send();
                DB::rollBack();
                return;
            }

            $user = Auth::user();
            $empleado = $user ? Empleado::where('email', $user->email)->first() : null;

            if (! $empleado) {
                Notification::make()
                    ->title('Error')
                    ->body('No se encontró un empleado asociado a tu usuario.')
                    ->danger()
                    ->send();
                DB::rollBack();
                return;
            }

            $this->record->loadMissing(['items.material', 'proyecto']);

            $almacenDestino = Almacen::where('cod_proy', $this->record->cod_proy)
                ->where('activo', true)
                ->first();

            $almacenOrigen = null;
            if ($almacenDestino?->id_almacen_padre) {
                $almacenOrigen = Almacen::where('id_almacen', $almacenDestino->id_almacen_padre)
                    ->where('activo', true)
                    ->first();
            }

            if (! $almacenOrigen) {
                Notification::make()
                    ->title('Error')
                    ->body('No se encontró un almacén origen (almacén padre) para realizar el despacho.')
                    ->danger()
                    ->send();
                DB::rollBack();
                return;
            }

            if (! $this->record->items || $this->record->items->isEmpty()) {
                Notification::make()
                    ->title('Error')
                    ->body('La solicitud no tiene materiales asociados.')
                    ->danger()
                    ->send();
                DB::rollBack();
                return;
            }

            $requiereCompra = (bool) $this->record->requiere_compra;

            foreach ($this->record->items as $item) {
                $cantidadADescontar = $item->cantidad_aprobada ?? $item->cantidad_solicitada ?? 0;

                if ($cantidadADescontar <= 0) {
                    continue;
                }

                $stockRows = StockAlmacen::where('id_almacen', $almacenOrigen->id_almacen)
                    ->where('id_material', $item->id_material)
                    ->orderByDesc('cantidad_disponible')
                    ->lockForUpdate()
                    ->get();

                $disponibleTotal = $stockRows->sum(function ($row) {
                    return max(0, (float) $row->cantidad_disponible - (float) $row->cantidad_reservada);
                });

                if ($disponibleTotal <= 0) {
                    continue;
                }

                if (! $requiereCompra && $disponibleTotal < $cantidadADescontar) {
                    $materialNombre = $item->material?->nombre_producto ?? 'material';
                    throw new \Exception("No hay stock suficiente en el almacén padre para {$materialNombre}. Disponible: {$disponibleTotal}");
                }

                $restante = $requiereCompra
                    ? min($cantidadADescontar, $disponibleTotal)
                    : $cantidadADescontar;
                foreach ($stockRows as $stockRow) {
                    if ($restante <= 0) {
                        break;
                    }

                    $disponible = max(0, (float) $stockRow->cantidad_disponible - (float) $stockRow->cantidad_reservada);
                    if ($disponible <= 0) {
                        continue;
                    }

                    $descontar = min($restante, $disponible);
                    $stockRow->decrement('cantidad_disponible', $descontar);
                    $restante -= $descontar;
                }
            }

            $this->record->update([
                'estado' => 'enviado',
                'observaciones' => $observaciones
                    ? ($this->record->observaciones ? $this->record->observaciones . "\n\nDespacho: " . $observaciones : "Despacho: " . $observaciones)
                    : $this->record->observaciones,
            ]);

            $this->record->deliveries()
                ->where('estado', '!=', 'recibido')
                ->update([
                    'estado' => 'en_transito',
                ]);

            $this->registrarEventoHistorial('enviado', $observaciones);

            DB::commit();

            Notification::make()
                ->title('Solicitud marcada como enviada')
                ->body('El despacho fue registrado y la solicitud pasó a estado enviado.')
                ->success()
                ->send();

            $this->redirect(SolicitudMaterialResource::getUrl('index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al marcar la solicitud como enviada: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Registra un evento en el historial de la solicitud
     */
    protected function registrarEventoHistorial(string $tipoEvento, ?string $observaciones = null): void
    {
        $user = Auth::user();
        $empleado = Empleado::where('email', $user->email)->first();

        if (!$empleado) {
            return;
        }

        $descripciones = [
            'aprobada' => "Solicitud {$this->record->numero_solicitud} aprobada",
            'aprobada_con_compra' => "Solicitud {$this->record->numero_solicitud} aprobada asumiendo compra de materiales",
            'aprobada_solo_stock' => "Solicitud {$this->record->numero_solicitud} aprobada solo con stock disponible",
            'rechazada' => "Solicitud {$this->record->numero_solicitud} rechazada",
            'enviado' => "Solicitud {$this->record->numero_solicitud} marcada como enviada",
        ];

        SolicitudHistorial::create([
            'id_solicitud' => $this->record->id_solicitud,
            'tipo_evento' => $tipoEvento,
            'descripcion' => $descripciones[$tipoEvento] ?? "Evento {$tipoEvento} en solicitud {$this->record->numero_solicitud}",
            'detalles' => json_encode([
                'estado_anterior' => $this->record->getOriginal('estado'),
                'estado_nuevo' => $this->record->estado,
            ]),
            'usuario_id' => $user->id,
            'empleado_id' => $empleado->cod_empleado,
            'fecha_evento' => now(),
            'observaciones' => $observaciones,
        ]);
    }
}
