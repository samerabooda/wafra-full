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
        $middleware->statefulApi();

        // Always revalidate HTML pages so a deploy is never hidden by browser cache.
        // Prepend so this middleware processes the response LAST and its header wins
        // over the session middleware's default "no-cache, private".
        $middleware->web(prepend: [\App\Http\Middleware\NoCacheHtml::class]);

        // Redirect unauthenticated users to the correct named login route
        $middleware->redirectGuestsTo(fn() => route('auth.login'));

        $middleware->alias([
            'role'        => \App\Http\Middleware\RoleMiddleware::class,
            'permission'  => \App\Http\Middleware\PermissionMiddleware::class,
            'active.user' => \App\Http\Middleware\ActiveUserMiddleware::class,
            'force.pwd'   => \App\Http\Middleware\ForcePasswordChangeMiddleware::class,
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
