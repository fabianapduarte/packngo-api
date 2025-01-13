<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth-jwt' => \App\Http\Middleware\JwtMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (NotFoundHttpException $exception) {
            return response()->json([
                'message' => 'Resource not found'
            ], 404);
        });

        $exceptions->renderable(function (AccessDeniedHttpException $exception) {
            return response()->json([
                'message' => 'Access denied for this action'
            ], 403);
        });

        $exceptions->renderable(function (JWTException $exception) {
            return response()->json([
                'message' => 'Could not create token'
            ], 500);
        });

        $exceptions->renderable(function (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 500);
        });
    })->create();
