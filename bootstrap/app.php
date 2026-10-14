<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        channels: __DIR__.'/../routes/channels.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'admin'])
                ->group(base_path('routes/backend.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            // Log the real exception internally
            Log::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Decide whether to expose the real message
            $exposeDetails = app()->isLocal();

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                    'errors' => null,
                ], 404);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                    'errors' => null,
                ], 401);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                    'errors' => null,
                ], 404);
            }

            if ($e instanceof MethodNotAllowedHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'HTTP method not allowed.',
                    'errors' => null,
                ], 405);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.',
                    'errors' => null,
                ], 403);
            }

            if ($e instanceof ThrottleRequestsException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                    'errors' => null,
                ], 429);
            }

            // Dynamic status code detection for Symfony HttpExceptions (abort() calls)
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            // Map status codes to safe messages
            $safeMessage = match (true) {
                $statusCode === 400 => 'Invalid request.',
                $statusCode === 401 => 'Authentication required.',
                $statusCode === 403 => 'You do not have permission to perform this action.',
                $statusCode === 404 => 'Resource not found.',
                $statusCode === 405 => 'HTTP method not allowed.',
                $statusCode === 409 => 'Resource already exists.',
                $statusCode === 410 => 'Resource is no longer available.',
                $statusCode === 415 => 'Unsupported media type.',
                $statusCode === 422 => 'Validation failed.',
                $statusCode === 429 => 'Too many requests. Please try again later.',
                $statusCode === 500 => 'An unexpected server error occurred.',
                $statusCode === 502 => 'Bad gateway.',
                $statusCode === 503 => 'Service is temporarily unavailable.',
                $statusCode === 504 => 'Gateway timeout.',
                default => 'An unexpected server error occurred.',
            };

            return response()->json([
                'success' => false,
                'message' => $exposeDetails ? $e->getMessage() : $safeMessage,
                'errors' => null,
            ], $statusCode);
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
