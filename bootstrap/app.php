<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware untuk OTP verification
        $middleware->alias([
            'ensure.teacher.otp' => \App\Http\Middleware\EnsureTeacherOtpVerified::class,
        ]);
    })
    ->withExceptions(function (\Illuminate\Foundation\Configuration\Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return \App\Helpers\ApiResponse::error(
                    'Data yang diberikan tidak valid.',
                    $e->errors(),
                    422
                );
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return \App\Helpers\ApiResponse::error(
                    'Resource atau endpoint tidak ditemukan.',
                    null,
                    404
                );
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return \App\Helpers\ApiResponse::error(
                    'Sesi Anda telah berakhir atau Anda belum login.',
                    null,
                    401
                );
            }
        });
    })->create();
