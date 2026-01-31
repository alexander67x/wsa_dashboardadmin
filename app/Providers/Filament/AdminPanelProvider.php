<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use App\Filament\Resources\Proyectos\ProyectoResource;
use App\Http\Middleware\EnsureNotSupervisor;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(fn () => asset('images/wsc-group-logo.png'))
            ->darkModeBrandLogo(fn () => asset('images/wsc-group-logo.png'))
            ->brandLogoHeight('3rem')
            ->brandName('')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->homeUrl(function () {
                $user = auth()->user();
                if ($user?->empleado?->role?->slug === 'responsable_proyecto') {
                    return ProyectoResource::getUrl('index');
                }

                return Dashboard::getUrl();
            })
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                \App\Filament\Widgets\AttendanceWeeklyAverageChart::class,
                \App\Filament\Widgets\LeadTimeByRoleChart::class,
                \App\Filament\Widgets\EmployeeProductivityChart::class,
            ])
            ->plugin(
                FilamentFullCalendarPlugin::make()
                    ->locale(config('app.locale', 'es'))
                    ->config([
                        'initialView' => 'dayGridMonth',
                        'headerToolbar' => [
                            'left' => 'prev,next today',
                            'center' => 'title',
                            'right' => 'dayGridMonth,listWeek',
                        ],
                        'buttonText' => [
                            'today' => 'Hoy',
                            'month' => 'Mes',
                            'day' => 'Día',
                            'listWeek' => 'Agenda',
                        ],
                        'height' => 'auto',
                        'dayMaxEvents' => true,
                    ])
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureNotSupervisor::class,
            ])
            ->renderHook(
                'panels::head.end',
                fn () => view('filament.onesignal'),
            )
            ->renderHook(
                'panels::body.end',
                fn () => view('filament.filepond-locale'),
            );
    }
}
