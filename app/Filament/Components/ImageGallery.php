<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Field;

class ImageGallery extends Field
{
    protected string $view = 'filament.resources.reportes.images-gallery';

    public static function make(?string $name = null): static
    {
        $static = app(static::class, ['name' => $name ?? 'images_gallery']);
        $static->configure();

        return $static;
    }

    public function getViewData(): array
    {
        $record = $this->getRecord();
        $archivos = $record && $record->archivos 
            ? $record->archivos->filter(fn ($archivo) => $archivo->es_foto) 
            : collect();

        return [
            'field' => $this,
            'archivos' => $archivos,
        ];
    }
}

