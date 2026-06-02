<?php

namespace App\Http\Controllers\Api;

use App\Events\NotificationTriggered;
use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|in:product,shop,user'
        ]);

        $class = match ($request->type) {
            'product' => Product::class,
            'shop'    => Shop::class,
            'user'    => User::class,
        };

        $model = $class::findOrFail($request->id);
        $user  = Auth::user();

        $like = Like::where([
            'user_id'        => $user->id,
            'likeable_id'    => $model->id,
            'likeable_type'  => $class,
        ])->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            Like::create([
                'user_id'       => $user->id,
                'likeable_id'   => $model->id,
                'likeable_type' => $class,
            ]);
            $liked = true;

            if ($request->type === 'shop') {
                try {
                    NotificationTriggered::dispatch('shop_liked', [
                        'shop' => $model,
                        'liker_name' => $user->name ?? 'Someone',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Shop like notification skipped: ' . $e->getMessage());
                }
            }

            if ($request->type === 'product') {
                try {
                    NotificationTriggered::dispatch('product_liked', [
                        'shop_id' => $model->shop_id,
                        'product_id' => $model->id,
                        'product_name' => $model->name,
                        'liker_name' => $user->name ?? 'Someone',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Product like notification skipped: ' . $e->getMessage());
                }
            }
        }

        $count = Like::where('likeable_id', $model->id)
            ->where('likeable_type', $class)
            ->count();

        return response()->json([
            'liked' => $liked,
            'count' => $count,
        ]);
    }

    public function count(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|in:product,shop,user'
        ]);

        $class = match ($request->type) {
            'product' => Product::class,
            'shop'    => Shop::class,
            'user'    => User::class,
        };

        $count = Like::where('likeable_id', $request->id)
            ->where('likeable_type', $class)
            ->count();

        $liked = false;
        $user = $request->user('sanctum');
        if ($user) {
            $liked = Like::where([
                'user_id'       => $user->id,
                'likeable_id'   => $request->id,
                'likeable_type' => $class,
            ])->exists();
        }

        return response()->json([
            'count' => $count,
            'liked' => $liked,
        ]);
    }
}
