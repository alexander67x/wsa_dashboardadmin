<?php

namespace App\Filament\Resources\ReclamosGarantia\Pages;

use App\Filament\Resources\ReclamosGarantia\ReclamoGarantiaResource;
use App\Services\GarantiaClaimService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewReclamoGarantia extends ViewRecord
{
    protected static string $resource = ReclamoGarantiaResource::class;

    public function getTitle(): string
    {
        return 'Reclamo #'.$this->record->id_reclamo;
    }

    public function getBreadcrumb(): string
    {
        return 'Reclamo #'.$this->record->id_reclamo;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $stock = $this->record->stock?->loadMissing(['material:id_material,codigo_producto,nombre_producto', 'almacen:id_almacen,codigo_almacen,nombre']);
        $stockReal = $stock ? ((float) $stock->cantidad_disponible - (float) $stock->cantidad_reservada) : null;

        $data['cantidad_reclamada'] = $this->formatNumber($this->record->cantidad_reclamada);
        $data['material_info'] = trim((string) (($stock?->material?->codigo_producto ?? '').' - '.($stock?->material?->nombre_producto ?? '')));
        $data['almacen_info'] = trim((string) (($stock?->almacen?->codigo_almacen ?? '').' - '.($stock?->almacen?->nombre ?? '')));
        $data['garantia_dias_info'] = (string) ($stock?->garantia_dias ?? 0);
        $data['fecha_registro_info'] = optional($stock?->created_at)->format('d/m/Y');
        $data['fecha_fin_garantia_info'] = $stock?->fecha_fin_garantia ? date('d/m/Y', strtotime($stock->fecha_fin_garantia)) : null;
        $data['stock_real_info'] = $stockReal !== null ? $this->formatNumber($stockReal) : null;

        return $data;
    }

    private function formatNumber(mixed $value): string
    {
        $normalized = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');

        return $normalized === '' ? '0' : $normalized;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];
        $empleadoId = auth()->user()?->empleado?->cod_empleado;

        if (! $empleadoId) {
            return $actions;
        }

        if ($this->record->estado === 'abierto') {
            $actions[] = Action::make('aprobar')
                ->label('Aprobar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Textarea::make('comentario')
                        ->label('Comentario')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data) use ($empleadoId): void {
                    try {
                        app(GarantiaClaimService::class)->aprobarReclamo(
                            reclamoId: (int) $this->record->getKey(),
                            aprobadoPor: (int) $empleadoId,
                            comentario: $data['comentario'] ?? null,
                        );
                        $this->record->refresh();
                        Notification::make()->title('Reclamo aprobado')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                    }
                });

            $actions[] = Action::make('rechazar')
                ->label('Rechazar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('comentario')
                        ->label('Motivo de rechazo')
                        ->rows(3)
                        ->required()
                        ->maxLength(500),
                ])
                ->action(function (array $data) use ($empleadoId): void {
                    try {
                        app(GarantiaClaimService::class)->rechazarReclamo(
                            reclamoId: (int) $this->record->getKey(),
                            rechazadoPor: (int) $empleadoId,
                            comentario: $data['comentario'] ?? null,
                        );
                        $this->record->refresh();
                        Notification::make()->title('Reclamo rechazado')->warning()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                    }
                });
        }

        if ($this->record->estado === 'aprobado') {
            $actions[] = Action::make('finalizar')
                ->label('Finalizar')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->form([
                    Select::make('resultado_final')
                        ->label('Resultado final')
                        ->options([
                            'devuelto_stock' => 'Devuelto a stock',
                            'baja_definitiva' => 'Baja definitiva',
                        ])
                        ->required(),
                    Textarea::make('comentario')
                        ->label('Comentario')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data) use ($empleadoId): void {
                    try {
                        app(GarantiaClaimService::class)->finalizarReclamo(
                            reclamoId: (int) $this->record->getKey(),
                            finalizadoPor: (int) $empleadoId,
                            resultadoFinal: (string) $data['resultado_final'],
                            comentario: $data['comentario'] ?? null,
                        );
                        $this->record->refresh();
                        Notification::make()->title('Reclamo finalizado')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                    }
                });
        }

        return $actions;
    }
}
