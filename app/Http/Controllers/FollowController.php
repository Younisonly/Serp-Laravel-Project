<?php

namespace App\Http\Controllers;

use App\Events\NotificationTriggered;
use App\Models\Follower;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FollowController extends Controller
{
    public function toggle(Request $request, $shopId)
    {
        try {
            $shopId = (int) $shopId;
            if ($shopId < 1) {
                return response()->json(['success' => false, 'message' => 'Invalid shop ID'], 400);
            }

            $shop = Shop::findOrFail($shopId);
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }

            if ((int) $user->id === (int) $shop->owner_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot follow your own shop.',
                ], 422);
            }

            $follow = Follower::where('follower_id', $user->id)
                ->where('followed_shop_id', $shopId)
                ->first();

            if ($follow) {
                $follow->delete();
                Shop::where('id', $shopId)->decrement('followers_number');
                $isFollowed = false;
            } else {
                Follower::create([
                    'follower_id' => $user->id,
                    'followed_shop_id' => $shopId,
                ]);
                Shop::where('id', $shopId)->increment('followers_number');
                $isFollowed = true;

                NotificationTriggered::dispatch('new_follower', [
                    'shop' => $shop,
                    'follower_name' => $user->name,
                ]);
            }

            return response()->json([
                'success' => true,
                'is_followed' => $isFollowed,
            ]);
        } catch (\Exception $e) {
            Log::error('Follow toggle failed', ['shop_id' => $shopId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function status(Request $request, $shopId)
    {
        try {
            $shopId = (int) $shopId;
            $count = Follower::where('followed_shop_id', $shopId)->count();
            $isFollowed = false;

            $user = Auth::user();
            if ($user) {
                $isFollowed = Follower::where('follower_id', $user->id)
                    ->where('followed_shop_id', $shopId)
                    ->exists();
            }

            return response()->json([
                'success' => true,
                'is_followed' => $isFollowed,
                'followers_count' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Follow status failed', ['shop_id' => $shopId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }
}
