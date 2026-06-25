<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── Superadmin bypass semua Gate & Permission check ────────────
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('superadmin')) {
                return true;
            }
        });

        // ── Carbon locale Bahasa Indonesia ─────────────────────────────
        Carbon::setLocale('id');

        // ── Force HTTPS di production ──────────────────────────────────
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // ── Log slow queries (> 1 detik) di non-production ────────────
        if (!app()->environment('production')) {
            DB::listen(function ($query) {
                if ($query->time > 1000) {
                    logger()->warning('Slow query: ' . $query->sql, [
                        'time'     => $query->time . 'ms',
                        'bindings' => $query->bindings,
                    ]);
                }
            });
        }

        // ── Refresh materialized view setiap jam via scheduler ─────────
        // (Didaftarkan di routes/console.php)
    }
}
