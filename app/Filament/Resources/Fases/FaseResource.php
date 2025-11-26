<?php

namespace App\Filament\Resources\Fases;

use App\Filament\Resources\Fases\Pages\CreateFase;
use App\Filament\Resources\Fases\Pages\EditFase;
use App\Filament\Resources\Fases\Pages\ListFases;
use App\Filament\Resources\Fases\Schemas\FaseForm;
use App\Filament\Resources\Fases\Tables\FasesTable;
use App\Models\Fase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FaseResource extends Resource
{
    protected static ?string $model = Fase::class;

    protected static ?string $navigationLabel = 'Fases';

    protected static ?string $pluralModelLabel = 'Fases';

    protected static ?string $modelLabel = 'Fase';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return FaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FasesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFases::route('/'),
            'create' => CreateFase::route('/create'),
            'edit' => EditFase::route('/{record}/edit'),
        ];
    }
}
