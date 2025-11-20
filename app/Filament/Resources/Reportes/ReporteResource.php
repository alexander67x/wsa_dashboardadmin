<?php

namespace App\Filament\Resources\Reportes;

use App\Filament\Concerns\RequiresPermission;
use App\Filament\Resources\Reportes\Pages\ListReportes;
use App\Filament\Resources\Reportes\Pages\ViewReporte;
use App\Filament\Resources\Reportes\Schemas\ReporteForm;
use App\Filament\Resources\Reportes\Tables\ReportesTable;
use App\Models\ReporteAvanceTarea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

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

