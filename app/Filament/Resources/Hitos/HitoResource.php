<?php

namespace App\Filament\Resources\Hitos;

use App\Filament\Concerns\RequiresPermission;
use App\Filament\Resources\Hitos\Pages\CreateHito;
use App\Filament\Resources\Hitos\Pages\EditHito;
use App\Filament\Resources\Hitos\Pages\ListHitos;
use App\Filament\Resources\Hitos\RelationManagers\TareasRelationManager;
use App\Filament\Resources\Hitos\Schemas\HitoForm;
use App\Filament\Resources\Hitos\Tables\HitosTable;
use App\Models\Hito;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class HitoResource extends Resource
{
    use RequiresPermission;

    protected static array $requiredPermissions = [
        'projects.manage.structure',
        'projects.materials.plan',
    ];

    protected static ?string $model = Hito::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Planificación';

    protected static ?string $modelLabel = 'Hito';

    protected static ?string $pluralModelLabel = 'Planificación (Hitos)';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        // Gerencia puede acceder (al menos en modo lectura).
        if ($user->empleado?->role?->slug === 'gerencia') {
            return true;
        }

        return static::userHasPermission();
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return HitoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HitosTable::configure($table);
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->empleado?->role?->slug === 'gerencia') {
            return true;
        }

        return static::userHasPermission();
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->empleado?->role?->slug === 'gerencia') {
            return true;
        }

        return static::userHasPermission();
    }

    public static function getRelations(): array
    {
        return [
            TareasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHitos::route('/'),
            'create' => CreateHito::route('/create'),
            'edit' => EditHito::route('/{record}/edit'),
        ];
    }
}
