<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class ViewController extends Controller
{
    private const CACHE_TTL_HOURS = 24;
    private const RATE_LIMIT_ATTEMPTS = 10;
    private const RATE_LIMIT_DECAY_MINUTES = 1;

    public function product(Request $request, $id)
    {
        return $this->recordView($request, Product::class, $id, 'product');
    }

    public function shop(Request $request, $id)
    {
        return $this->recordView($request, Shop::class, $id, 'shop');
    }

    private function recordView(Request $request, string $modelClass, int $id, string $type)
    {
        try {
            $ip = $request->ip();
            $userId = $request->user()?->id;
            $fingerprint = $userId ? "user_{$userId}" : "ip_{$ip}";

            $rateKey = "view_rate_{$type}_{$fingerprint}";
            if (RateLimiter::tooManyAttempts($rateKey, self::RATE_LIMIT_ATTEMPTS)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Already counted.',
                ]);
            }
            RateLimiter::hit($rateKey, self::RATE_LIMIT_DECAY_MINUTES * 60);

            $cacheKey = "viewed_{$type}_{$id}_{$fingerprint}";
            if (Cache::has($cacheKey)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Already counted.',
                ]);
            }

            Cache::put($cacheKey, true, now()->addHours(self::CACHE_TTL_HOURS));

            $modelClass::withoutTimestamps(function () use ($modelClass, $id) {
                $modelClass::where('id', $id)->increment('views');
            });

            View::create([
                'viewable_type' => $modelClass,
                'viewable_id' => $id,
                'ip_address' => $ip,
                'user_id' => $userId,
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            Log::error("View increment failed for {$type} #{$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }
}
