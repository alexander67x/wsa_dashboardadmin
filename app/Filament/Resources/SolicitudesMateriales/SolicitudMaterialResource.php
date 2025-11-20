<?php

namespace App\Filament\Resources\SolicitudesMateriales;

use App\Filament\Concerns\RequiresPermission;
use App\Filament\Resources\SolicitudesMateriales\Pages\ListSolicitudes;
use App\Filament\Resources\SolicitudesMateriales\Pages\ViewSolicitud;
use App\Filament\Resources\SolicitudesMateriales\RelationManagers\DeliveriesRelationManager;
use App\Filament\Resources\SolicitudesMateriales\Schemas\SolicitudForm;
use App\Filament\Resources\SolicitudesMateriales\Tables\SolicitudesMaterialesTable;
use App\Models\SolicitudMaterial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SolicitudMaterialResource extends Resource
{
    use RequiresPermission;

    protected static array $requiredPermissions = [
        'materials.requests.create',
        'materials.requests.approve',
        'materials.requests.deliver',
        'inventory.view.project',
    ];
    protected static ?string $model = SolicitudMaterial::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $recordTitleAttribute = 'numero_solicitud';

    protected static ?string $navigationLabel = 'Solicitudes de Materiales';

    protected static ?string $modelLabel = 'Solicitud de Materiales';

    protected static ?string $pluralModelLabel = 'Solicitudes de Materiales';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return SolicitudForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SolicitudesMaterialesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DeliveriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSolicitudes::route('/'),
            'view' => ViewSolicitud::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Las solicitudes se crean desde la API
    }

    public static function canEdit($record): bool
    {
        return false; // Las solicitudes se editan desde la API
    }

    public static function canDelete($record): bool
    {
        return false; // Las solicitudes se eliminan desde la API
    }
}

