<?php

namespace App\Filament\Resources\ReclamosGarantia\Pages;

use App\Filament\Resources\ReclamosGarantia\ReclamoGarantiaResource;
use App\Models\ReclamoGarantiaMaterial;
use App\Models\StockAlmacen;
use App\Services\GarantiaClaimService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateReclamoGarantia extends CreateRecord
{
    protected static string $resource = ReclamoGarantiaResource::class;

    public function getTitle(): string
    {
        return 'Crear Reclamo de Garantía';
    }

    public function mount(): void
    {
        parent::mount();

        $stockId = request()->get('id_stock');
        if ($stockId) {
            $stock = StockAlmacen::query()
                ->with(['material:id_material,codigo_producto,nombre_producto', 'almacen:id_almacen,codigo_almacen,nombre'])
                ->find((int) $stockId);

            $stockReal = $stock ? ((float) $stock->cantidad_disponible - (float) $stock->cantidad_reservada) : null;

            $this->form->fill([
                'id_stock' => (int) $stockId,
                'material_info' => $stock ? trim((string) ($stock->material?->codigo_producto.' - '.$stock->material?->nombre_producto)) : null,
                'almacen_info' => $stock ? trim((string) ($stock->almacen?->codigo_almacen.' - '.$stock->almacen?->nombre)) : null,
                'garantia_dias_info' => $stock ? (string) ($stock->garantia_dias ?? 0) : null,
                'fecha_registro_info' => $stock ? optional($stock->created_at)->format('d/m/Y') : null,
                'fecha_fin_garantia_info' => $stock?->fecha_fin_garantia ? date('d/m/Y', strtotime($stock->fecha_fin_garantia)) : null,
                'stock_real_info' => $stockReal !== null ? number_format($stockReal, 2) : null,
            ]);
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $empleadoId = auth()->user()?->empleado?->cod_empleado;
        if (! $empleadoId) {
            throw ValidationException::withMessages([
                'id_stock' => 'No se encontró un empleado asociado al usuario autenticado.',
            ]);
        }

        $stock = StockAlmacen::query()->find((int) $data['id_stock']);
        $cantidadReclamada = (float) ($data['cantidad_reclamada'] ?? 0);
        $stockDisponible = $stock
            ? max(0, (float) $stock->cantidad_disponible - (float) $stock->cantidad_reservada)
            : 0.0;

        if ($cantidadReclamada > $stockDisponible) {
            $message = sprintf(
                'No se puede crear la orden de garantía: la cantidad solicitada (%.2f) supera el stock disponible (%.2f).',
                $cantidadReclamada,
                $stockDisponible,
            );

            Notification::make()
                ->title('Cantidad inválida')
                ->body($message)
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'cantidad_reclamada' => $message,
            ]);
        }

        try {
            return app(GarantiaClaimService::class)->crearReclamo(
                stockId: (int) $data['id_stock'],
                cantidadReclamada: $cantidadReclamada,
                motivoDano: (string) ($data['motivo_dano'] ?? ''),
                observaciones: $data['observaciones'] ?? null,
                creadoPor: (int) $empleadoId,
            );
        } catch (\Throwable $e) {
            Notification::make()
                ->title('No se pudo crear la orden de garantía')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'cantidad_reclamada' => $e->getMessage(),
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        if ($this->record instanceof ReclamoGarantiaMaterial) {
            return static::getResource()::getUrl('view', ['record' => $this->record]);
        }

        return static::getResource()::getUrl('index');
    }
}
