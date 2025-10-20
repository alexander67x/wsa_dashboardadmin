<?php

namespace App\Filament\Resources\Empleados\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EmpleadoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre_completo')
                    ->required(),
                TextInput::make('cargo'),
                TextInput::make('departamento'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('telefono')
                    ->tel(),
                DatePicker::make('fecha_ingreso'),
                Toggle::make('activo')
                    ->required(),
            ]);
    }
}
