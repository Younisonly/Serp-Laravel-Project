<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        AuthenticationException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        NotFoundHttpException::class,
        MethodNotAllowedHttpException::class,
        ThrottleRequestsException::class,
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'token',
        'api_token',
    ];

    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {

            if ($request->expectsJson() || $request->is('api/*')) {

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
                        'message' => 'Method not allowed.',
                    ], 405);
                }

                // 429 Too Many Requests
                if ($e instanceof ThrottleRequestsException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many requests. Please slow down.',
                        'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                    ], 429);
                }

                // 413 Payload Too Large
                if ($e instanceof PostTooLargeException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Upload size exceeds the maximum allowed.',
                    ], 413);
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

                // SECURITY: Log full details for admin, NEVER expose to user
                Log::error('Internal server error', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user_id' => $request->user()?->id,
                    'trace' => $e->getTraceAsString(),
                ]);

                // 500 - Never expose internal details to client
                return response()->json([
                    'status' => 'error',
                    'message' => 'An internal server error occurred. Our team has been notified.',
                    'code' => method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500,
                ], 500);
            }

            return null;
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated. Invalid or missing token.',
            ], 401);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated.',
        ], 401);
    }
}
