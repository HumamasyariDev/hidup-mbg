<?php

use App\Http\Middleware\DeviceFingerprintMiddleware;
use App\Http\Middleware\GeofencingMiddleware;
use App\Http\Middleware\RequestSanitizerMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\ZkpAuthMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // --- Global middleware (runs on EVERY request) ---
        $middleware->prepend(SecurityHeadersMiddleware::class);
        $middleware->append(RequestSanitizerMiddleware::class);

        // --- Route middleware aliases ---
        $middleware->alias([
            'geofencing'         => GeofencingMiddleware::class,
            'zkp.auth'           => ZkpAuthMiddleware::class,
            'device.fingerprint' => DeviceFingerprintMiddleware::class,
        ]);

        // --- API middleware group additions ---
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // --- Sanctum stateful domains ---
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Never leak stack traces in production API responses
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'Resource tidak ditemukan.',
                ], 404);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'too_many_requests',
                    'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                ], 429);
            }
        });

        // Catch all other exceptions for API to prevent info leakage
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') && app()->isProduction()) {
                \Illuminate\Support\Facades\Log::error('Unhandled API exception', [
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'url'       => $request->fullUrl(),
                    'ip'        => $request->ip(),
                ]);

                return response()->json([
                    'error' => 'server_error',
                    'message' => 'Terjadi kesalahan internal. Silakan coba lagi.',
                ], 500);
            }
        });
    })->create();
