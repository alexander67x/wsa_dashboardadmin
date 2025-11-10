<?php

namespace App\Filament\Resources\StockAlmacenes;

use App\Filament\Resources\StockAlmacenes\Pages\CreateStockAlmacen;
use App\Filament\Resources\StockAlmacenes\Pages\EditStockAlmacen;
use App\Filament\Resources\StockAlmacenes\Pages\ListStockAlmacenes;
use App\Filament\Resources\StockAlmacenes\Schemas\StockAlmacenForm;
use App\Filament\Resources\StockAlmacenes\Tables\StockAlmacenesTable;
use App\Models\StockAlmacen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockAlmacenResource extends Resource
{
    protected static ?string $model = StockAlmacen::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Stock en Almacenes';

    protected static ?string $modelLabel = 'Stock';

    protected static ?string $pluralModelLabel = 'Stock en Almacenes';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return StockAlmacenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockAlmacenesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockAlmacenes::route('/'),
            'create' => CreateStockAlmacen::route('/create'),
            'edit' => EditStockAlmacen::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}

