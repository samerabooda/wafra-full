<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        api:      __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ── Trust proxies (HTTPS behind Nginx/Apache/CDN) ──────
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_HOST |
                     Request::HEADER_X_FORWARDED_PORT |
                     Request::HEADER_X_FORWARDED_PROTO |
                     Request::HEADER_X_FORWARDED_AWS_ELB
        );

        // ── Sanctum stateful API (session-based auth for SPA) ──
        $middleware->statefulApi();

        // ── Custom middleware aliases ───────────────────────────
        $middleware->alias([
            'role'        => \App\Http\Middleware\RoleMiddleware::class,
            'permission'  => \App\Http\Middleware\PermissionMiddleware::class,
            'active.user' => \App\Http\Middleware\ActiveUserMiddleware::class,
            'force.pwd'   => \App\Http\Middleware\ForcePasswordChangeMiddleware::class,
        ]);

        // ── Append CORS headers to all responses ───────────────
        $middleware->web(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $status = 500;
                if (method_exists($e, 'getStatusCode')) {
                    $status = $e->getStatusCode();
                } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
                    $status = 422;
                } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $status = 401;
                } elseif ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    $status = 403;
                } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $status = 404;
                }

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Server Error',
                    'errors'  => $e instanceof \Illuminate\Validation\ValidationException
                                 ? $e->errors() : null,
                ], $status);
            }
        });
    })->create();
