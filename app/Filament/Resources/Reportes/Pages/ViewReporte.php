<?php

namespace App\Filament\Resources\Reportes\Pages;

use App\Filament\Resources\Reportes\ReporteResource;
use App\Models\Empleado;
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
        $this->record->load(['proyecto', 'tarea', 'registradoPor', 'aprobadoPor', 'archivos']);
        
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

            $this->record->update([
                'estado' => 'aprobado',
                'observaciones_supervisor' => $observaciones,
                'fecha_aprobacion' => now(),
                'aprobado_por' => $empleado->cod_empleado,
            ]);

            DB::commit();

            Notification::make()
                ->title('Reporte Aprobado')
                ->body('El reporte ha sido aprobado exitosamente.')
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

