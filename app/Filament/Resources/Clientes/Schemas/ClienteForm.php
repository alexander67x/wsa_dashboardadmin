<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre_cliente')
                    ->required(),
                TextInput::make('industria'),
                TextInput::make('contacto_principal'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('telefono')
                    ->tel(),
                Textarea::make('direccion')
                    ->columnSpanFull(),
                Toggle::make('activo')
                    ->required(),
            ]);
    }
}
