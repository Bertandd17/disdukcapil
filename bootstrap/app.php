<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\Handler;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware untuk API requests - XSS Protection
        $middleware->api(append: [
            \App\Http\Middleware\XSSProtectionMiddleware::class,
        ]);

        // XSS Protection middleware alias
        $middleware->alias([
            'xss.protection' => \App\Http\Middleware\XSSProtectionMiddleware::class,
            'rate.limit' => \App\Http\Middleware\RateLimitingMiddleware::class,
            'ip.whitelist' => \App\Http\Middleware\IPWhitelistMiddleware::class,
            'secure.upload' => \App\Http\Middleware\SecureFileUploadMiddleware::class,
            'audit.log' => \App\Http\Middleware\AuditLogMiddleware::class,
            'camera.policy' => \App\Http\Middleware\CameraPermissionPolicy::class,
            // Spatie Permission Middleware
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            // Jika request AJAX atau API
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                    'redirect_url' => route('login')
                ], 419);
            }

            // Jika request web biasa, redirect ke login dengan pesan
            return redirect()->route('login')
                ->with('info', 'Sesi Anda telah berakhir. Silakan login kembali.');
        });
    })->create();
