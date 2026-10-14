<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ApiResponse
{
    /**
     * Standardized success response.
     */
    public function success(string $message = 'Request completed successfully.', mixed $data = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Alias for success (backward compatibility).
     */
    public function ok(string $message = 'Request completed successfully.', mixed $data = null): JsonResponse
    {
        return $this->success($message, $data);
    }

    /**
     * Standardized error response.
     *
     * @param  array|null  $errors  Field-specific errors (e.g. validation)
     */
    public function error(string $message = 'Invalid request.', int $code = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => ! empty($errors) ? $errors : null,
        ];

        return response()->json($response, $code);
    }

    /**
     * Standardized paginated response.
     */
    public function pagination(string $message = 'Request completed successfully.', mixed $data = null, ?Paginator $paginateData = null, int $statusCode = 200): JsonResponse
    {
        $pagination = $paginateData ?? $data;

        if ($pagination instanceof Paginator) {
            $response = [
                'success' => true,
                'message' => $message,
                'data' => $paginateData ? $data : $pagination->items(),
                'pagination' => [
                    'current_page' => $pagination->currentPage(),
                    'last_page' => $pagination->lastPage(),
                    'per_page' => $pagination->perPage(),
                    'total' => $pagination->total(),
                    'from' => $pagination->firstItem(),
                    'to' => $pagination->lastItem(),
                    'next_page_url' => $pagination->nextPageUrl(),
                    'prev_page_url' => $pagination->previousPageUrl(),
                    'path' => $pagination->path(),
                ],
            ];
        } else {
            $response = [
                'success' => true,
                'message' => $message,
            ];
            if ($data !== null) {
                $response['data'] = $data;
            }
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Centralized safe error handler — logs the real exception and returns
     * a sanitized, user-safe error response.
     *
     * @param  string  $userMessage  Safe message for the user
     */
    public function safeError(\Throwable $e, string $userMessage = 'An unexpected server error occurred.', int $code = 500, array $errors = []): JsonResponse
    {
        Log::error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        if ($userMessage === 'An unexpected server error occurred.' && $code === 500) {
            if ($e instanceof AuthorizationException) {
                return $this->error('You do not have permission to perform this action.', 403, $errors);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return $this->error('Resource not found.', 404, $errors);
            }

            if ($e instanceof AuthenticationException) {
                return $this->error('Authentication required.', 401, $errors);
            }

            if ($e instanceof ValidationException) {
                return $this->error('Validation failed.', 422, $e->errors());
            }

            if (method_exists($e, 'getStatusCode')) {
                $code = $e->getStatusCode();
            }
        }

        return $this->error($userMessage, $code, $errors);
    }
}
