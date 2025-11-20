<?php

namespace App\Filament\Resources\Empleados\Schemas;

use App\Models\Role;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
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

                ViewField::make('role_privileges_preview')
                    ->label('Privilegios del rol seleccionado')
                    ->columnSpanFull()
                    ->visible(fn ($get) => (bool) $get('id_role'))
                    ->view('filament.components.role-permissions')
                    ->viewData(fn ($get) => [
                        'role' => $get('id_role')
                            ? Role::with('permissions')->find($get('id_role'))
                            : null,
                    ]),
                Toggle::make('activo')
                    ->required(),
            ]);
    }
}
