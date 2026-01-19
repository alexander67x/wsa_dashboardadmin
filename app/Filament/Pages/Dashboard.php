<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Proyectos\ProyectoResource;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static function shouldHideFromProjectManager(): bool
    {
        return auth()->user()?->empleado?->role?->slug === 'responsable_proyecto';
    }

    public static function canAccess(): bool
    {
        if (static::shouldHideFromProjectManager()) {
            return true;
        }

        return parent::canAccess();
    }

    public function mount(): void
    {
        if (static::shouldHideFromProjectManager()) {
            $this->redirect(ProyectoResource::getUrl('index'));
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (static::shouldHideFromProjectManager()) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }
}
