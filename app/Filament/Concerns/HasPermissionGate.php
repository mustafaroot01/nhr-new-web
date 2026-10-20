<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Enforces Spatie permissions on a Filament resource.
 *
 * Each resource must declare its permission prefix via permissionResource(),
 * e.g. 'posts' → checks view_posts / create_posts / update_posts / delete_posts.
 * Admins bypass all checks.
 */
trait HasPermissionGate
{
    abstract protected static function permissionResource(): string;

    protected static function userCan(string $ability): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }

        return method_exists($user, 'can')
            && $user->can($ability . '_' . static::permissionResource());
    }

    public static function canViewAny(): bool
    {
        return static::userCan('view');
    }

    public static function canView(Model $record): bool
    {
        return static::userCan('view');
    }

    public static function canCreate(): bool
    {
        return static::userCan('create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::userCan('update');
    }

    public static function canDelete(Model $record): bool
    {
        return static::userCan('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::userCan('delete');
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::userCan('delete');
    }

    public static function canForceDeleteAny(): bool
    {
        return static::userCan('delete');
    }

    public static function canRestore(Model $record): bool
    {
        return static::userCan('delete');
    }

    public static function canRestoreAny(): bool
    {
        return static::userCan('delete');
    }
}
