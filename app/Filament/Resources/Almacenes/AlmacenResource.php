<?php

namespace App\Filament\Resources\Almacenes;

use App\Filament\Concerns\RequiresPermission;
use App\Filament\Resources\Almacenes\Pages\CreateAlmacen;
use App\Filament\Resources\Almacenes\Pages\EditAlmacen;
use App\Filament\Resources\Almacenes\Pages\ListAlmacenes;
use App\Filament\Resources\Almacenes\RelationManagers\StockRelationManager;
use App\Filament\Resources\Almacenes\Schemas\AlmacenForm;
use App\Filament\Resources\Almacenes\Tables\AlmacenesTable;
use App\Models\Almacen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AlmacenResource extends Resource
{
    use RequiresPermission;

    protected static array $requiredPermissions = ['inventory.view.central', 'inventory.view.project'];
    protected static ?string $model = Almacen::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?string $navigationLabel = 'Almacenes';

    protected static ?string $modelLabel = 'Almacén';

    protected static ?string $pluralModelLabel = 'Almacenes';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return AlmacenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlmacenesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StockRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAlmacenes::route('/'),
            'create' => CreateAlmacen::route('/create'),
            'edit' => EditAlmacen::route('/{record}/edit'),
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
