<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(\Laravel\Sanctum\PersonalAccessToken::class);

        RateLimiter::for('api', function (Request $request) {
            $limit = $request->user() ? 100 : 30;
            return Limit::perMinute($limit)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many requests. Please slow down.',
                    ], 429);
                });
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many authentication attempts. Please try again later.',
                    ], 429);
                });
        });

        RateLimiter::for('favorites', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many requests. Please slow down.',
                    ], 429);
                });
        });

        RateLimiter::for('views', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip() . '|' . $request->userAgent())
                ->response(function () {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many requests. Please slow down.',
                    ], 429);
                });
        });

        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Too many uploads. Please slow down.',
                    ], 429);
                });
        });
    }
}
