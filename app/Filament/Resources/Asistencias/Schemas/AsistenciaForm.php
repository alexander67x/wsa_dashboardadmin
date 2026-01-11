<?php

namespace App\Filament\Resources\Asistencias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class AsistenciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('empleado')
                    ->label('Empleado')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record) {
                            return '—';
                        }

                        $record->loadMissing('user.empleado');

                        return $record->user?->empleado?->nombre_completo
                            ?? $record->user?->name
                            ?? '—';
                    }),
                TextInput::make('estado')
                    ->label('Estado')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn ($state, $record) => $record?->status ? ucfirst($record->status) : '—'),
                TextInput::make('check_in_at')
                    ->label('Check-in')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn ($state, $record) => self::formatDateTime($record?->check_in_at)),
                TextInput::make('check_out_at')
                    ->label('Check-out')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn ($state, $record) => self::formatDateTime($record?->check_out_at)),
                Textarea::make('proyectos')
                    ->label('Proyecto(s)')
                    ->disabled()
                    ->dehydrated(false)
                    ->rows(3)
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record) {
                            return '—';
                        }

                        $record->loadMissing('user.empleado.proyectosBasicos');
                        $proyectos = $record->user?->empleado?->proyectosBasicos;

                        if (! $proyectos || $proyectos->isEmpty()) {
                            return '—';
                        }

                        return $proyectos
                            ->map(fn ($proyecto) => $proyecto->cod_proy . ' — ' . ($proyecto->nombre_ubicacion ?? ''))
                            ->filter()
                            ->implode(PHP_EOL);
                    }),
                TextInput::make('hours_worked')
                    ->label('Horas trabajadas')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn ($state, $record) => $record?->hours_worked !== null
                        ? number_format($record->hours_worked, 2)
                        : '—'),
                TextInput::make('check_in_location_label')
                    ->label('Ubicación check-in')
                    ->disabled()
                    ->dehydrated(false)
                    ->default('—'),
                TextInput::make('check_out_location_label')
                    ->label('Ubicación check-out')
                    ->disabled()
                    ->dehydrated(false)
                    ->default('—'),
                TextInput::make('check_in_coords')
                    ->label('Coordenadas check-in')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record || $record->check_in_lat === null || $record->check_in_lng === null) {
                            return '—';
                        }

                        return $record->check_in_lat . ', ' . $record->check_in_lng;
                    }),
                TextInput::make('check_out_coords')
                    ->label('Coordenadas check-out')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record || $record->check_out_lat === null || $record->check_out_lng === null) {
                            return '—';
                        }

                        return $record->check_out_lat . ', ' . $record->check_out_lng;
                    }),
                ViewField::make('attendance_map')
                    ->label('Mapa')
                    ->columnSpanFull()
                    ->view('filament.components.attendance-map')
                    ->viewData(fn ($record) => [
                        'recordId' => $record?->id,
                        'checkInLat' => $record?->check_in_lat,
                        'checkInLng' => $record?->check_in_lng,
                        'checkInLabel' => $record?->check_in_location_label,
                        'checkOutLat' => $record?->check_out_lat,
                        'checkOutLng' => $record?->check_out_lng,
                        'checkOutLabel' => $record?->check_out_location_label,
                    ]),
            ]);
    }

    private static function formatDateTime($value): string
    {
        if (! $value) {
            return '—';
        }

        $date = $value instanceof Carbon ? $value : Carbon::parse($value);

        return $date->format('d/m/Y H:i');
    }
}
