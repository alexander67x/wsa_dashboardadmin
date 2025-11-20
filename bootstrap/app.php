<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EnsurePermission;
use App\Console\Commands\SendOneSignalTest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        SendOneSignalTest::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => Authenticate::class,
            'permission' => EnsurePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, $request) {
            if ($request->is('admin*') || $request->is('admin')) {
                return redirect()->guest('/admin/login');
            }

            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            if (Route::has('login')) {
                return redirect()->guest(route('login'));
            }

            return response()->json(['message' => 'Unauthenticated.'], 401);
        });

        $exceptions->render(function (Throwable $exception, $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            return response()->json([
                'message' => $status === 500
                    ? 'Ocurrió un error inesperado al procesar la solicitud.'
                    : $exception->getMessage(),
                'error' => $exception->getMessage(),
                'exception' => class_basename($exception::class),
            ], $status);
        });
    })->create();
