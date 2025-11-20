<?php

namespace App\Filament\Resources\Hitos;

use App\Filament\Resources\Hitos\Pages\CreateHito;
use App\Filament\Resources\Hitos\Pages\EditHito;
use App\Filament\Resources\Hitos\Pages\ListHitos;
use App\Filament\Resources\Hitos\RelationManagers\TareasRelationManager;
use App\Filament\Resources\Hitos\Schemas\HitoForm;
use App\Filament\Resources\Hitos\Tables\HitosTable;
use App\Models\Hito;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class HitoResource extends Resource
{
    protected static ?string $model = Hito::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Planificación';

    protected static ?string $modelLabel = 'Hito';

    protected static ?string $pluralModelLabel = 'Planificación (Hitos)';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return HitoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HitosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TareasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHitos::route('/'),
            'create' => CreateHito::route('/create'),
            'edit' => EditHito::route('/{record}/edit'),
        ];
    }
}
