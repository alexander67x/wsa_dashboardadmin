<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Get;
use Filament\Forms\Set;

class MapPicker extends Field
{
    protected string $view = 'filament.components.map-picker';

    protected string $viewIdentifier = 'map-picker';

    protected bool $onlyEditableOnEdit = false;

    public static function make(?string $name = null): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function onlyEditableOnEdit(bool $condition = true): static
    {
        $this->onlyEditableOnEdit = $condition;

        return $this;
    }

    public function getViewData(): array
    {
        $state = $this->getState();
        // Coordenadas por defecto: Santa Cruz de la Sierra, Bolivia
        $defaultLatitude = -17.7833;
        $defaultLongitude = -63.1821;

        $latitude = is_array($state) ? ($state['latitude'] ?? $defaultLatitude) : $defaultLatitude;
        $longitude = is_array($state) ? ($state['longitude'] ?? $defaultLongitude) : $defaultLongitude;

        $livewire = method_exists($this, 'getLivewire') ? $this->getLivewire() : null;

        // Por defecto el mapa es editable, excepto en páginas específicas
        $isEditable = true;

        if ($livewire instanceof \App\Filament\Resources\Proyectos\Pages\CreateProyecto
            || $livewire instanceof \App\Filament\Resources\Proyectos\Pages\ViewProyecto) {
            // En proyectos, solo permitimos seleccionar ubicación en la página de edición
            $isEditable = false;
        }
        
        return [
            'field' => $this,
            'statePath' => $this->getStatePath(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            // Zoom más cercano a ciudad
            'zoom' => 13,
            // Centrar el mapa en las coordenadas actuales, o en Santa Cruz por defecto
            'center' => [$latitude ?? $defaultLatitude, $longitude ?? $defaultLongitude],
            'isEditable' => $isEditable,
        ];
    }
}
