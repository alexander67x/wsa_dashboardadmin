<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

trait RequiresPermission
{
    protected static function requiredPermissions(): array
    {
        return property_exists(static::class, 'requiredPermissions')
            ? array_filter((array) static::$requiredPermissions)
            : [];
    }

    protected static function userHasPermission(): bool
    {
        $permissions = static::requiredPermissions();

        if (empty($permissions)) {
            return true;
        }

        $user = auth()->user();

        if (! $user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public static function canAccess(): bool
    {
        return static::userHasPermission();
    }

    public static function canViewAny(): bool
    {
        return static::userHasPermission();
    }

    public static function canCreate(): bool
    {
        return static::userHasPermission();
    }

    public static function canEdit(Model $record): bool
    {
        return static::userHasPermission();
    }

    public static function canDelete(Model $record): bool
    {
        return static::userHasPermission();
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::userHasPermission();
    }

    public static function canRestore(Model $record): bool
    {
        return static::userHasPermission();
    }
}
