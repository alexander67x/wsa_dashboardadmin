<?php

namespace App\Filament\Resources\Fases;

use App\Filament\Concerns\RequiresPermission;
use App\Filament\Resources\Fases\Pages\CreateFase;
use App\Filament\Resources\Fases\Pages\EditFase;
use App\Filament\Resources\Fases\Pages\ListFases;
use App\Filament\Resources\Fases\Schemas\FaseForm;
use App\Filament\Resources\Fases\Tables\FasesTable;
use App\Models\Fase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FaseResource extends Resource
{
    use RequiresPermission;

    protected static array $requiredPermissions = [
        'projects.manage.structure',
    ];

    protected static ?string $model = Fase::class;

    protected static ?string $navigationLabel = 'Fases';

    protected static ?string $pluralModelLabel = 'Fases';

    protected static ?string $modelLabel = 'Fase';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 3;

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
        return FaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FasesTable::configure($table);
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

    public static function getPages(): array
    {
        return [
            'index' => ListFases::route('/'),
            'create' => CreateFase::route('/create'),
            'edit' => EditFase::route('/{record}/edit'),
        ];
    }
}
