<?php

namespace App\Filament\Resources\BajasGarantia\Pages;

use App\Filament\Resources\BajasGarantia\BajaGarantiaResource;
use Filament\Resources\Pages\ViewRecord;

class ViewBajaGarantia extends ViewRecord
{
    protected static string $resource = BajaGarantiaResource::class;

    public function getTitle(): string
    {
        return 'Baja #'.$this->record->id_baja;
    }

    public function getBreadcrumb(): string
    {
        return 'Baja #'.$this->record->id_baja;
    }
}
