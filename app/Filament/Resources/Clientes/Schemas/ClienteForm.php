<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Forms\Components\FileUpload;
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
                FileUpload::make('documentos')
                    ->label('Documentos del cliente')
                    ->multiple()
                    ->disk('public')
                    ->directory('clientes/documentos')
                    ->preserveFilenames()
                    ->openable()
                    ->downloadable()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->helperText('Anexa contratos, NIT/RUC, certificados, etc.'),
                Toggle::make('activo')
                    ->required(),
            ]);
    }
}
