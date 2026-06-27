<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

/**
 * Override Spatie PermissionRegistrar untuk memaksa semua ID
 * role dan permission diperlakukan sebagai UUID string,
 * bukan integer — mencegah error "uuid = integer" di PostgreSQL.
 */
class UuidPermissionRegistrar extends PermissionRegistrar
{
    /**
     * Override method yang membangun query permissions via roles.
     * Default Spatie menggunakan ID dari cache yang bisa bertipe integer.
     */
    public function getPermissionsWithRoles(string $guard): Collection
    {
        return app('cache')
            ->store($this->cacheStore)
            ->remember(
                $this->cacheKey . '.guard.' . $guard,
                $this->cacheExpirationTime,
                fn () => $this->getPermissionClass()::with('roles')->get()
            );
    }

    /**
     * Override getCachedRoles agar selalu cast ID ke string
     */
    public function getCachedRoles(): Collection
    {
        $roles = parent::getCachedRoles();

        return $roles->map(function ($role) {
            $role->id = (string) $role->id;
            return $role;
        });
    }
}
