<?php

namespace App\Filament\Resources\SolicitudesMateriales\Pages;

use App\Filament\Resources\SolicitudesMateriales\SolicitudMaterialResource;
use App\Models\Almacen;
use App\Models\ReporteAvanceTarea;
use App\Models\StockAlmacen;
use Filament\Resources\Pages\ViewRecord;

class TraceabilidadSolicitud extends ViewRecord
{
    protected static string $resource = SolicitudMaterialResource::class;

    protected string $view = 'filament.resources.solicitudes-materiales.pages.traceabilidad-solicitud';

    public function getTitle(): string
    {
        return 'Trazabilidad de materiales';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getViewData(): array
    {
        $this->record->loadMissing([
            'proyecto',
            'tarea',
            'solicitadoPor',
            'aprobadaPor',
            'items.material',
            'deliveries.almacenOrigen',
            'deliveries.almacenDestino',
            'deliveries.entregadoPor',
            'deliveries.recibidoPor',
            'historial',
        ]);

        $deliveries = $this->record->deliveries ?? collect();
        $receipts = $deliveries->filter(fn ($delivery) => (bool) $delivery->fecha_recepcion);
        $almacenProyecto = null;
        $almacenOrigenFallback = null;
        $stockProyecto = collect();
        $reportesUso = collect();

        if ($this->record->cod_proy) {
            $almacenProyecto = Almacen::where('cod_proy', $this->record->cod_proy)
                ->where('activo', true)
                ->first();
        }

        if ($almacenProyecto) {
            if ($almacenProyecto->id_almacen_padre) {
                $almacenOrigenFallback = Almacen::where('id_almacen', $almacenProyecto->id_almacen_padre)->first();
            }

            $materialIds = ($this->record->items ?? collect())
                ->pluck('id_material')
                ->filter()
                ->unique()
                ->values();

            $stockProyecto = StockAlmacen::with('material')
                ->where('id_almacen', $almacenProyecto->id_almacen)
                ->when($materialIds->isNotEmpty(), function ($query) use ($materialIds) {
                    $query->whereIn('id_material', $materialIds);
                })
                ->get();
        }

        if ($this->record->cod_proy) {
            $reportesUso = ReporteAvanceTarea::with(['tarea', 'materiales.material'])
                ->where('cod_proy', $this->record->cod_proy)
                ->where('estado', 'aprobado')
                ->whereHas('tarea', function ($query) {
                    $query->where('estado', 'finalizada');
                })
                ->orderByDesc('fecha_reporte')
                ->get();
        }

        $fotosRecepcion = $receipts
            ->pluck('foto_recepcion_url')
            ->filter()
            ->unique()
            ->values();

        return [
            'record' => $this->record,
            'items' => $this->record->items ?? collect(),
            'deliveries' => $deliveries,
            'receipts' => $receipts,
            'almacenesOrigen' => $deliveries->pluck('almacenOrigen.nombre')->filter()->unique()->values(),
            'almacenesDestino' => $deliveries->pluck('almacenDestino.nombre')->filter()->unique()->values(),
            'historial' => $this->record->historial ?? collect(),
            'almacenProyecto' => $almacenProyecto,
            'almacenOrigenFallback' => $almacenOrigenFallback,
            'stockProyecto' => $stockProyecto,
            'fotosRecepcion' => $fotosRecepcion,
            'reportesUso' => $reportesUso,
        ];
    }
}
