<?php

namespace App\Filament\Resources\Empleados\Schemas;

use App\Models\Role;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
                    ->email()
                    ->required(),
                TextInput::make('telefono')
                    ->tel(),
                DatePicker::make('fecha_ingreso'),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof \App\Filament\Resources\Empleados\Pages\CreateEmpleado)
                    ->helperText('Dejar vacío para mantener la contraseña actual al editar')
                    ->dehydrated(false),
                Select::make('id_role')
                    ->label('Privilegios')
                    ->relationship('role', 'nombre')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn (Role $record): string => $record->nombre)
                    ->helperText('Seleccione los privilegios del empleado'),
                Toggle::make('activo')
                    ->required(),
            ]);
    }
}
