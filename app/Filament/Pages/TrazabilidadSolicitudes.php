<?php

namespace App\Filament\Pages;

use App\Models\SolicitudMaterial;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class TrazabilidadSolicitudes extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Trazabilidad';

    protected static ?string $title = 'Trazabilidad de solicitudes';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static string|UnitEnum|null $navigationGroup = 'Admin';

    protected static ?int $navigationSort = 50;

    protected string $view = 'filament.pages.trazabilidad-solicitudes';

    public ?int $solicitudId = null;

    public ?SolicitudMaterial $solicitud = null;

    public function mount(): void
    {
        if ($this->solicitudId) {
            $this->loadSolicitud();
        }
    }

    public function updatedSolicitudId(): void
    {
        $this->loadSolicitud();
    }

    protected function loadSolicitud(): void
    {
        $this->solicitud = SolicitudMaterial::with(['items', 'deliveries', 'historial'])
            ->find($this->solicitudId);
    }

    /**
     * @return Collection<int, SolicitudMaterial>
     */
    public function getSolicitudesProperty(): Collection
    {
        return SolicitudMaterial::orderByDesc('fecha_solicitud')
            ->limit(50)
            ->get(['id_solicitud', 'numero_solicitud']);
    }
}
