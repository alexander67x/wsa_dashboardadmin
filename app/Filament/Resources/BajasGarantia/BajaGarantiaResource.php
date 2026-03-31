<?php

namespace App\Filament\Resources\BajasGarantia;

use App\Filament\Resources\BajasGarantia\Pages\ListBajasGarantia;
use App\Filament\Resources\BajasGarantia\Pages\ViewBajaGarantia;
use App\Filament\Resources\BajasGarantia\Schemas\BajaGarantiaForm;
use App\Filament\Resources\BajasGarantia\Tables\BajasGarantiaTable;
use App\Models\BajaGarantiaMaterial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BajaGarantiaResource extends Resource
{
    protected static ?string $model = BajaGarantiaMaterial::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box-x-mark';

    protected static ?string $recordTitleAttribute = 'id_baja';

    protected static ?string $navigationLabel = 'Bajas por Garantía';

    protected static ?string $modelLabel = 'Baja por Garantía';

    protected static ?string $pluralModelLabel = 'Bajas por Garantía';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return BajaGarantiaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BajasGarantiaTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBajasGarantia::route('/'),
            'view' => ViewBajaGarantia::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canAccess(): bool
    {
        $role = auth()->user()?->empleado?->role?->slug;

        return in_array($role, ['adquisiciones', 'gerencia'], true);
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}

