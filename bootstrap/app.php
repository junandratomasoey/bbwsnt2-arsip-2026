<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Exceptions\UnauthorizedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            // Spatie Permission
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // Custom WIAKMS
            'user.aktif'         => \App\Http\Middleware\EnsureUserAktif::class,
            'user.not_locked'    => \App\Http\Middleware\EnsureUserNotLocked::class,
        ]);

        // Tambahkan middleware ke semua web request
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Tangani 403 dari Spatie Permission
        $exceptions->renderable(function (UnauthorizedException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk tindakan ini.',
                ], 403);
            }
            return response()->view('errors.403', [
                'message' => $e->getMessage(),
            ], 403);
        });

        // Log semua exception di production
        $exceptions->reportable(function (\Throwable $e) {
            if (app()->environment('production')) {
                logger()->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
        });
    })
    ->create();
