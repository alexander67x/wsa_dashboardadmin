<?php

namespace App\Filament\Resources\Incidencias\Pages;

use App\Filament\Resources\Incidencias\IncidenciaResource;
use App\Models\Empleado;
use App\Models\IncidenciaHistorial;
use Filament\Actions\Action;
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

        if (! $this->canResolveIncidencia()) {
            return $actions;
        }

        if (in_array($this->record->estado, ['registrada', 'cerrada'], true)) {
            return $actions;
        }

        $actions[] = Action::make('marcar_registrada')
            ->label('Marcar como registrada')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Marcar incidencia como registrada')
            ->modalDescription('¿Estás seguro de que deseas marcar esta incidencia como registrada?')
            ->action(function (): void {
                $this->marcarIncidenciaRegistrada();
            });

        return $actions;
    }

    protected function marcarIncidenciaRegistrada(): void
    {
        if (! $this->canResolveIncidencia()) {
            Notification::make()
                ->title('No tienes permisos para registrar incidencias.')
                ->danger()
                ->send();
            return;
        }

        if (in_array($this->record->estado, ['registrada', 'cerrada'], true)) {
            Notification::make()
                ->title('La incidencia ya está registrada o cerrada.')
                ->warning()
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $empleado = $user?->empleado ?? ($user ? Empleado::where('email', $user->email)->first() : null);

            if (! $empleado) {
                Notification::make()
                    ->title('Error')
                    ->body('No se encontró un empleado asociado a tu usuario.')
                    ->danger()
                    ->send();
                DB::rollBack();
                return;
            }

            $estadoAnterior = $this->record->estado;

            $this->record->update([
                'estado' => 'registrada',
                'fecha_resolucion' => now(),
            ]);

            IncidenciaHistorial::create([
                'id_incidencia' => $this->record->getKey(),
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => 'registrada',
                'comentario' => 'Incidencia marcada como registrada desde el panel.',
                'accion_tomada' => 'Incidencia marcada como registrada',
                'usuario_cambio' => $empleado->cod_empleado,
                'fecha_cambio' => now(),
            ]);

            DB::commit();

            $this->record->refresh();

            Notification::make()
                ->title('Incidencia registrada')
                ->body('La incidencia fue marcada como registrada.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error')
                ->body('No se pudo marcar la incidencia como registrada: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function canResolveIncidencia(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasPermission('incidents.review.impact')
            || $user->hasPermission('incidents.review.project');
    }
}

