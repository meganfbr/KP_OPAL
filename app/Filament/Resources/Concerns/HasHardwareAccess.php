<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait untuk semua resource Data Hardware.
 * Hanya super_admin yang boleh mengakses, melihat, membuat, mengedit, dan menghapus.
 * Laboran (dan role lainnya) tidak bisa akses sama sekali — baik dari sidebar maupun URL langsung.
 */
trait HasHardwareAccess
{
    protected static function canManageHardware(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('super_admin');
    }

    /**
     * Blokir akses langsung via URL untuk non-super_admin.
     * Ini yang menangani: "jangan hanya sembunyikan menu, tapi juga batasi akses URL".
     */
    public static function canAccess(): bool
    {
        return static::canManageHardware();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canManageHardware();
    }

    public static function canViewAny(): bool
    {
        return static::canManageHardware();
    }

    public static function canCreate(): bool
    {
        return static::canManageHardware();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canManageHardware();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canManageHardware();
    }

    public static function canDeleteAny(): bool
    {
        return static::canManageHardware();
    }

    public static function canView(Model $record): bool
    {
        return static::canManageHardware();
    }
}
