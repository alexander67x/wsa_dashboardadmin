<?php

namespace App\Filament\Resources\Reportes\Pages;

use App\Filament\Resources\Reportes\ReporteResource;
use App\Models\Almacen;
use App\Models\Empleado;
use App\Models\StockAlmacen;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViewReporte extends ViewRecord
{
    protected static string $resource = ReporteResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar relaciones necesarias
        $this->record->load(['proyecto', 'tarea', 'registradoPor', 'aprobadoPor', 'archivos', 'materiales.material']);
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Solo mostrar acciones de aprobar/rechazar si el reporte está pendiente
        if (in_array($this->record->estado, ['enviado', 'borrador'])) {
            $actions[] = Action::make('aprobar')
                ->label('Aprobar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Aprobar Reporte')
                ->modalDescription('¿Estás seguro de que deseas aprobar este reporte?')
                ->form([
                    Textarea::make('observaciones')
                        ->label('Observaciones (opcional)')
                        ->placeholder('Agregar comentarios sobre la aprobación...')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $this->aprobarReporte($data['observaciones'] ?? null);
                });

            $actions[] = Action::make('rechazar')
                ->label('Rechazar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rechazar Reporte')
                ->modalDescription('¿Estás seguro de que deseas rechazar este reporte?')
                ->form([
                    Textarea::make('observaciones')
                        ->label('Observaciones (requerido)')
                        ->placeholder('Explica el motivo del rechazo...')
                        ->rows(3)
                        ->required()
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $this->rechazarReporte($data['observaciones']);
                });
        }

        return $actions;
    }

    protected function aprobarReporte(?string $observaciones): void
    {
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

            // Actualizar estado del reporte
            $this->record->update([
                'estado' => 'aprobado',
                'observaciones_supervisor' => $observaciones,
                'fecha_aprobacion' => now(),
                'aprobado_por' => $empleado->cod_empleado,
            ]);

            // Restar materiales del stock si el reporte tiene materiales
            $materialesUsados = $this->record->materiales;
            
            if ($materialesUsados->isNotEmpty()) {
                // Buscar almacén asociado al proyecto (debe existir ya que la API solo muestra materiales de este almacén)
                $almacen = Almacen::where('cod_proy', $this->record->cod_proy)
                    ->where('activo', true)
                    ->first();

                if (!$almacen) {
                    DB::rollBack();
                    Notification::make()
                        ->title('Error')
                        ->body('No se encontró un almacén activo asociado al proyecto. El reporte no puede ser aprobado.')
                        ->danger()
                        ->send();
                    return;
                }

                $errores = [];
                
                foreach ($materialesUsados as $reporteMaterial) {
                    // Cargar información del material para mensajes más descriptivos
                    $material = \App\Models\Material::find($reporteMaterial->id_material);
                    $nombreMaterial = $material ? $material->nombre_producto : "ID {$reporteMaterial->id_material}";
                    
                    // Buscar el stock en el almacén del proyecto
                    $stock = StockAlmacen::where('id_almacen', $almacen->id_almacen)
                        ->where('id_material', $reporteMaterial->id_material)
                        ->first();

                    if (!$stock) {
                        // El material debería existir en el almacén ya que la API solo muestra materiales disponibles
                        $errores[] = "Material '{$nombreMaterial}' (ID: {$reporteMaterial->id_material}) no encontrado en el almacén del proyecto '{$almacen->nombre}' (ID: {$almacen->id_almacen}). Verifique que el material esté correctamente registrado en el almacén.";
                        continue;
                    }

                    $cantidadDisponible = (float) $stock->cantidad_disponible;
                    $cantidadUsada = (float) $reporteMaterial->cantidad_usada;

                    if ($cantidadDisponible < $cantidadUsada) {
                        $errores[] = "Stock insuficiente para '{$nombreMaterial}'. Disponible: {$cantidadDisponible}, Requerido: {$cantidadUsada}";
                        continue;
                    }

                    // Restar del stock disponible
                    $stock->decrement('cantidad_disponible', $cantidadUsada);
                }

                if (!empty($errores)) {
                    DB::rollBack();
                    Notification::make()
                        ->title('Error al procesar materiales')
                        ->body('El reporte no pudo ser aprobado: ' . implode('; ', $errores))
                        ->danger()
                        ->send();
                    return;
                }
            }

            DB::commit();

            Notification::make()
                ->title('Reporte Aprobado')
                ->body('El reporte ha sido aprobado exitosamente' . ($materialesUsados->isNotEmpty() ? ' y los materiales han sido descontados del stock.' : '.'))
                ->success()
                ->send();

            $this->redirect(ReporteResource::getUrl('index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al aprobar el reporte: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function rechazarReporte(string $observaciones): void
    {
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

            $this->record->update([
                'estado' => 'rechazado',
                'observaciones_supervisor' => $observaciones,
                'fecha_aprobacion' => now(),
                'aprobado_por' => $empleado->cod_empleado,
            ]);

            DB::commit();

            Notification::make()
                ->title('Reporte Rechazado')
                ->body('El reporte ha sido rechazado.')
                ->warning()
                ->send();

            $this->redirect(ReporteResource::getUrl('index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al rechazar el reporte: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}

