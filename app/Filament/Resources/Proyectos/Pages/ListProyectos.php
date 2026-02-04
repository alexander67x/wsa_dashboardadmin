<?php

namespace App\Filament\Resources\Proyectos\Pages;

use App\Filament\Resources\Proyectos\ProyectoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListProyectos extends ListRecords
{
    protected static string $resource = ProyectoResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        if (in_array($user?->empleado?->role?->slug, ['responsable_proyecto', 'adquisiciones'], true)) {
            return [];
        }

        return [CreateAction::make()->label('Nuevo proyecto')];
    }
}
