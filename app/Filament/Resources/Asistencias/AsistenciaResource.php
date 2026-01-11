<?php

namespace App\Filament\Resources\Asistencias;

use App\Filament\Concerns\RequiresPermission;
use App\Filament\Resources\Asistencias\Pages\ListAsistencias;
use App\Filament\Resources\Asistencias\Pages\ViewAsistencia;
use App\Filament\Resources\Asistencias\Schemas\AsistenciaForm;
use App\Filament\Resources\Asistencias\Tables\AsistenciasTable;
use App\Models\AttendanceSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AsistenciaResource extends Resource
{
    use RequiresPermission;

    protected static array $requiredPermissions = ['attendance.view'];
    protected static ?string $model = AttendanceSession::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Asistencia';

    protected static ?string $modelLabel = 'Asistencia';

    protected static ?string $pluralModelLabel = 'Asistencias';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return AsistenciaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AsistenciasTable::configure($table);
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
            'index' => ListAsistencias::route('/'),
            'view' => ViewAsistencia::route('/{record}'),
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
