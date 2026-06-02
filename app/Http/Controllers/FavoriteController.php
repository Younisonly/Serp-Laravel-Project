<?php
namespace App\Http\Controllers;

use App\Events\NotificationTriggered;
use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $type = $request->query('type', 'shops');
            $userId = Auth::id();
            if (!$userId) return response()->json([]);

            $cacheKey = "favorites:{$userId}:{$type}";

            $mini = Cache::remember($cacheKey, 300, function () use ($userId, $type) {
                $favIds = Favorite::where('user_id', $userId)
                    ->where('favorable_type', $type === 'shops' ? Shop::class : Product::class)
                    ->pluck('favorable_id');

                if ($type === 'shops') {
                    $models = Shop::whereIn('id', $favIds)->with('images')->get();
                } else {
                    $models = Product::whereIn('id', $favIds)->with('primaryImage')->get();
                }

                return $models->filter(fn($m) => $m !== null)
                    ->map(function ($m) use ($type) {
                        if ($type === 'shops') {
                            return [
                                'id'         => $m->id,
                                'name'       => $m->name,
                                'logo'       => $m->logo,
                                'status'     => $m->status,
                                'stars_rate' => $m->stars_rate ?? 0,
                            ];
                        }

                        $image = $m->primaryImage ? $m->primaryImage->url : null;

                        return [
                            'id'         => $m->id,
                            'name'       => $m->name,
                            'image'      => $image,
                            'price'      => $m->price ?? 0,
                            'stars_rate' => $m->stars_rate ?? 0,
                        ];
                    })->values();
            });

            return response()->json($mini);
        } catch (\Throwable $e) {
            Log::error('Favorites index failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function toggle(Request $request)
    {
        try {
            $request->validate([
                'id'   => 'required|integer',
                'type' => 'required|in:shop,product',
            ]);

            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $class = $request->type === 'shop' ? Shop::class : Product::class;
            $model = $class::findOrFail($request->id);

            $favorite = Favorite::where([
                'user_id'        => $userId,
                'favorable_id'   => $model->id,
                'favorable_type' => $class
            ])->first();

            $typeKey = $request->type === 'shop' ? 'shops' : 'product';
            Cache::forget("favorites:{$userId}:{$typeKey}");

            if ($favorite) {
                $favorite->delete();
                return response()->json([
                    'message' => 'removed',
                    'favorited' => false,
                ]);
            }

            Favorite::create([
                'user_id'        => $userId,
                'favorable_id'   => $model->id,
                'favorable_type' => $class
            ]);

            if ($request->type === 'shop') {
                try {
                    $user = Auth::user();
                    NotificationTriggered::dispatch('new_favorite', [
                        'shop' => $model,
                        'user_name' => $user?->name ?? 'Someone',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Favorite notification skipped: ' . $e->getMessage());
                }
            }

            if ($request->type === 'product') {
                try {
                    $user = Auth::user();
                    NotificationTriggered::dispatch('product_favorited', [
                        'shop_id' => $model->shop_id,
                        'product_id' => $model->id,
                        'product_name' => $model->name,
                        'user_name' => $user?->name ?? 'Someone',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Product favorite notification skipped: ' . $e->getMessage());
                }
            }

            return response()->json([
                'message' => 'added',
                'favorited' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('Favorites toggle failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}
