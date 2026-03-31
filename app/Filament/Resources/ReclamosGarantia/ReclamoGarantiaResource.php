<?php

namespace App\Filament\Resources\ReclamosGarantia;

use App\Filament\Resources\ReclamosGarantia\Pages\CreateReclamoGarantia;
use App\Filament\Resources\ReclamosGarantia\Pages\ListReclamoGarantias;
use App\Filament\Resources\ReclamosGarantia\Pages\ViewReclamoGarantia;
use App\Filament\Resources\ReclamosGarantia\RelationManagers\HistorialRelationManager;
use App\Filament\Resources\ReclamosGarantia\Schemas\ReclamoGarantiaForm;
use App\Filament\Resources\ReclamosGarantia\Tables\ReclamosGarantiaTable;
use App\Models\ReclamoGarantiaMaterial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ReclamoGarantiaResource extends Resource
{
    protected static ?string $model = ReclamoGarantiaMaterial::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $recordTitleAttribute = 'id_reclamo';

    protected static ?string $navigationLabel = 'Reclamos de Garantía';

    protected static ?string $modelLabel = 'Reclamo de Garantía';

    protected static ?string $pluralModelLabel = 'Reclamos de Garantía';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return ReclamoGarantiaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReclamosGarantiaTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            HistorialRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReclamoGarantias::route('/'),
            'create' => CreateReclamoGarantia::route('/create'),
            'view' => ViewReclamoGarantia::route('/{record}'),
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
        return static::canAccess();
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
