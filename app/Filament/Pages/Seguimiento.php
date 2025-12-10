<?php

namespace App\Filament\Pages;

use App\Services\SeguimientoService;
use BackedEnum;
use Filament\Pages\Page;

class Seguimiento extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Seguimiento';
    protected static ?string $title = 'Curva de seguimiento';
    protected string $view = 'filament.pages.seguimiento';

    public array $projectOptions = [];
    public array $projectSeries = [];
    public ?string $defaultProject = null;

    public function mount(SeguimientoService $service): void
    {
        $summary = $service->getTrackingSummary();
        $this->projectOptions = $summary['projectOptions'];
        $this->projectSeries = $summary['projectSeries'];
        $this->defaultProject = $summary['defaultProject'];
    }
}
