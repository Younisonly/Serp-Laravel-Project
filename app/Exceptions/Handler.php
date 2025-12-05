<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {

            // Always respond in JSON for API calls
            if ($request->expectsJson()) {

                // 401 Unauthenticated
                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthenticated. Invalid or missing token.',
                    ], 401);
                }

                // 404 Not Found
                if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Resource not found.',
                    ], 404);
                }

                // 405 Method Not Allowed
                if ($e instanceof MethodNotAllowedHttpException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'HTTP method not allowed for this route.',
                    ], 405);
                }

                // 422 Validation Error
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validation failed.',
                        'errors' => $e->errors(),
                    ], 422);
                }

                // 403 Forbidden
                if ($e instanceof HttpException && $e->getStatusCode() === 403) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You do not have permission to perform this action.',
                    ], 403);
                }

                // 500+ Other Exceptions
                return response()->json([
                    'status' => 'error',
                    'message' => 'Server error: ' . $e->getMessage(),
                ], 500);
            }

            // Default HTML rendering
            return null;
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated. Invalid or missing token.',
            ], 401);
        }

        // Prevent the old redirect to login route crash
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated (login route not used for API).',
        ], 401);
    }
}
