<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Gate::before — cek role langsung dari DB, tidak lewat Spatie cache
        Gate::before(function ($user, $ability) {
            // Query langsung ke tabel tanpa trigger Spatie cache
            $isSuperAdmin = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_uuid', $user->id)
                ->where('model_has_roles.model_type', get_class($user))
                ->where('roles.name', 'superadmin')
                ->exists();

            if ($isSuperAdmin) return true;
        });

        Carbon::setLocale('id');

        // Reset Spatie permission cache
        try {
            app(\Spatie\Permission\PermissionRegistrar::class)
                ->forgetCachedPermissions();
        } catch (\Throwable $e) {
            // Abaikan saat migrate
        }
    }
}
