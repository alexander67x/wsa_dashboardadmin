<?php

namespace App\Filament\Resources\Proyectos;

use App\Filament\Concerns\RequiresPermission;
use App\Filament\Resources\Proyectos\Pages\CreateProyecto;
use App\Filament\Resources\Proyectos\Pages\EditProyecto;
use App\Filament\Resources\Proyectos\Pages\ListProyectos;
use App\Filament\Resources\Proyectos\Pages\ViewProyecto;
use App\Filament\Resources\Proyectos\RelationManagers\ArchivosRelationManager;
use App\Filament\Resources\Proyectos\Schemas\ProyectoForm;
use App\Filament\Resources\Proyectos\Tables\ProyectosTable;
use App\Models\Proyecto;
use App\Services\ProjectAccessService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProyectoResource extends Resource
{
    use RequiresPermission;

    protected static array $requiredPermissions = [
        'projects.manage.structure',
        'projects.detail.view',
        'projects.my.view',
    ];
    protected static ?string $model = Proyecto::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static ?string $recordTitleAttribute = 'cod_proy';

    protected static ?string $navigationLabel = 'Proyectos';

    protected static ?string $modelLabel = 'Proyecto';

    protected static ?string $pluralModelLabel = 'Proyectos';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProyectoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProyectosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ArchivosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProyectos::route('/'),
            'create' => CreateProyecto::route('/create'),
            'view' => ViewProyecto::route('/{record}'),
            'edit' => EditProyecto::route('/{record}/edit'),
        ];
    }

    protected static function applyUserProjectScope(Builder $query): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->empleado?->role?->slug === 'responsable_proyecto') {
            $allowed = ProjectAccessService::allowedProjectIds($user);

            if ($allowed === null) {
                return $query;
            }

            if (empty($allowed)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('cod_proy', $allowed);
        }

        $canManageAll = $user->hasPermission('projects.manage.structure')
            || $user->hasPermission('projects.detail.view');

        if ($canManageAll) {
            return $query;
        }

        if (! $user->hasPermission('projects.my.view')) {
            return $query;
        }

        $empleado = $user->empleado;

        if (! $empleado) {
            return $query;
        }

        $empleadoId = $empleado->cod_empleado;

        return $query->where(function (Builder $subQuery) use ($empleadoId) {
            $subQuery
                ->where('responsable_proyecto', $empleadoId)
                ->orWhere('supervisor_obra', $empleadoId)
                ->orWhereHas('empleados', function (Builder $empleadosQuery) use ($empleadoId) {
                    $empleadosQuery->where('empleados.cod_empleado', $empleadoId);
                });
        });
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        return static::applyUserProjectScope($query);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $query = parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        return static::applyUserProjectScope($query);
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        if ($user->empleado?->role?->slug === 'responsable_proyecto') {
            $allowed = ProjectAccessService::allowedProjectIds($user);
            $query = static::getModel()::query();

            if ($allowed === null) {
                return (string) $query->count();
            }

            if (empty($allowed)) {
                return '0';
            }

            return (string) $query->whereIn('cod_proy', $allowed)->count();
        }

        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
