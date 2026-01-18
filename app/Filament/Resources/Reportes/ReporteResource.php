<?php

namespace App\Filament\Resources\Reportes;

use App\Filament\Concerns\RequiresPermission;
use App\Filament\Resources\Reportes\Pages\ListReportes;
use App\Filament\Resources\Reportes\Pages\ViewReporte;
use App\Filament\Resources\Reportes\Schemas\ReporteForm;
use App\Filament\Resources\Reportes\Tables\ReportesTable;
use App\Models\ReporteAvanceTarea;
use App\Services\ProjectAccessService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReporteResource extends Resource
{
    use RequiresPermission;

    protected static array $requiredPermissions = [
        'reports.view',
        'reports.approve',
    ];
    protected static ?string $model = ReporteAvanceTarea::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'titulo';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $modelLabel = 'Reporte';

    protected static ?string $pluralModelLabel = 'Reportes';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return ReporteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportesTable::configure($table);
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
            'index' => ListReportes::route('/'),
            'view' => ViewReporte::route('/{record}'),
        ];
    }

    protected static function applyUserProjectScope(Builder $query): Builder
    {
        $user = Auth::user();
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (in_array($user->empleado?->role?->slug, ['responsable_proyecto', 'supervisor'], true)) {
            $allowed = ProjectAccessService::allowedProjectIds($user);

            if ($allowed === null) {
                return $query;
            }

            if (empty($allowed)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('cod_proy', $allowed);
        }

        return $query;
    }

    public static function getEloquentQuery(): Builder
    {
        return static::applyUserProjectScope(parent::getEloquentQuery());
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::applyUserProjectScope(parent::getRecordRouteBindingEloquentQuery());
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

