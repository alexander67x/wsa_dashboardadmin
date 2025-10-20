<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Get;
use Filament\Forms\Set;

class MapPicker extends Field
{
    protected string $view = 'filament.components.map-picker';

    protected string $viewIdentifier = 'map-picker';

    public static function make(?string $name = null): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function getViewData(): array
    {
        $state = $this->getState();
        $latitude = is_array($state) ? ($state['latitude'] ?? -16.2902) : -16.2902;
        $longitude = is_array($state) ? ($state['longitude'] ?? -63.5887) : -63.5887;
        
        return [
            'field' => $this,
            'statePath' => $this->getStatePath(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => 6,
            'center' => [-16.2902, -63.5887], // Bolivia coordinates
        ];
    }
}
