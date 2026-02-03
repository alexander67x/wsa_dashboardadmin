<?php

namespace App\Filament\Resources\StockAlmacenes;

use App\Filament\Resources\StockAlmacenes\Pages\CreateStockAlmacen;
use App\Filament\Resources\StockAlmacenes\Pages\EditStockAlmacen;
use App\Filament\Resources\StockAlmacenes\Pages\ListStockAlmacenes;
use App\Filament\Resources\StockAlmacenes\Schemas\StockAlmacenForm;
use App\Filament\Resources\StockAlmacenes\Tables\StockAlmacenesTable;
use App\Models\StockAlmacen;
use App\Services\ProjectAccessService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        if ($user->empleado?->role?->slug === 'responsable_proyecto') {
            $allowed = ProjectAccessService::allowedProjectIds($user, true);
            $query = static::getModel()::query();

            if ($allowed === null) {
                return (string) $query->count();
            }

            if (empty($allowed)) {
                return '0';
            }

            return (string) $query
                ->whereHas('almacen', fn ($almacenQuery) => $almacenQuery->whereIn('cod_proy', $allowed))
                ->count();
        }

        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if ($user->empleado?->role?->slug === 'responsable_proyecto') {
            return true;
        }

        return $user->hasPermission('inventory.view.central')
            || $user->hasPermission('inventory.view.project')
            || $user->hasPermission('inventory.view.subwarehouses');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Gerente General y Responsable de proyecto solo consultan stock, no crean registros
        if (in_array($user->empleado?->role?->slug, ['gerencia', 'responsable_proyecto'], true)) {
            return false;
        }

        return parent::canCreate();
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if ($user->empleado?->role?->slug === 'gerencia') {
            return false;
        }

        return parent::canEdit($record);
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if ($user->empleado?->role?->slug === 'gerencia') {
            return false;
        }

        return parent::canDelete($record);
    }
}
