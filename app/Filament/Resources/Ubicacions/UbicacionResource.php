<?php

namespace App\Filament\Resources\Ubicacions;

use App\Filament\Resources\Ubicacions\Pages\CreateUbicacion;
use App\Filament\Resources\Ubicacions\Pages\EditUbicacion;
use App\Filament\Resources\Ubicacions\Pages\ListUbicacions;
use App\Filament\Resources\Ubicacions\Schemas\UbicacionForm;
use App\Filament\Resources\Ubicacions\Tables\UbicacionsTable;
use App\Models\Ubicacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UbicacionResource extends Resource
{
    protected static ?string $model = Ubicacion::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $recordTitleAttribute = 'nombre_ubicacion';

    protected static ?string $navigationLabel = 'Almacenes';

    protected static ?string $modelLabel = 'Ubicación';

    protected static ?string $pluralModelLabel = 'Ubicaciones';

    protected static ?int $navigationSort = 999; // Ocultar del navbar

    public static function form(Schema $schema): Schema
    {
        return UbicacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UbicacionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        // Recurso de Ubicaciones deshabilitado: las ubicaciones ahora están integradas
        return [];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
