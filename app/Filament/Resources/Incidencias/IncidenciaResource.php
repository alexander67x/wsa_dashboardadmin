<?php

namespace App\Filament\Resources\Incidencias;

use App\Filament\Concerns\RequiresPermission;
use App\Filament\Resources\Incidencias\Pages\ListIncidencias;
use App\Filament\Resources\Incidencias\Pages\ViewIncidencia;
use App\Filament\Resources\Incidencias\Schemas\IncidenciaForm;
use App\Filament\Resources\Incidencias\Tables\IncidenciasTable;
use App\Models\Incidencia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IncidenciaResource extends Resource
{
    use RequiresPermission;

    protected static array $requiredPermissions = [
        'incidents.view',
        'incidents.review.project',
        'incidents.review.impact',
    ];
    protected static ?string $model = Incidencia::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $recordTitleAttribute = 'titulo';

    protected static ?string $navigationLabel = 'Incidencias';

    protected static ?string $modelLabel = 'Incidencia';

    protected static ?string $pluralModelLabel = 'Incidencias';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return IncidenciaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncidenciasTable::configure($table);
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
            'index' => ListIncidencias::route('/'),
            'view' => ViewIncidencia::route('/{record}'),
        ];
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

