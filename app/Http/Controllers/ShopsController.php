<?php
namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Comment;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopsController extends Controller
{
    // ✅ Get shop by owner ID (single source of truth)
    public function getByOwner($id)
    {
        try {
            $shop = Shop::where('owner_id', $id)->first();
            if (!$shop) {
                return response()->json(['success' => false, 'message' => 'Shop not found'], 404);
            }
            return response()->json(['success' => true, 'data' => $shop], 200);
        } catch (\Exception $e) {
            Log::error('Get shop by owner failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    // ✅ Update shop status (with auth + validation)
    public function updateStatus(Request $r, $id)
    {
        try {
            $shop = Shop::findOrFail($id);
            if ($shop->owner_id !== auth('sanctum')->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $validated = $r->validate(['status' => 'required|in:open,closed']);
            $shop->update(['status' => $validated['status']]);
            return response()->json(['success' => true, 'data' => $shop], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Update status failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error','error_message' => $e->getMessage()], 500);
        }
    }

    // ✅ Analytics (with safe counts)
    public function getAnalytics($id)
    {
        try {
            $shop = Shop::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'total_views' => $shop->views ?? 0,
                    'total_favorites' => $shop->followers_number ?? 0,
                    'total_comments' => Comment::where('shop_id', $id)->whereNull('parent_id')->count(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get analytics failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error','error_message' => $e->getMessage()], 500);
        }
    }

    // ✅ Comments (paginated, with user data)
    public function getComments($id, Request $r)
    {
        try {
            $limit = min((int)$r->get('limit', 3), 20); // Cap at 20
            $comments = Comment::where('shop_id', $id)
                ->whereNull('parent_id')
                ->with('commenter:id,name,image')
                ->latest()
                ->limit($limit)
                ->get();
            return response()->json(['success' => true, 'data' => $comments], 200);
        } catch (\Exception $e) {
            Log::error('Get comments failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error','error_message' => $e->getMessage()], 500);
        }
    }

    // ✅ User insights (extendable)
    public function getInsights($id)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'favorited_count' => Favorite::where('favorable_type', 'App\Models\Shop')->where('favorable_id', $id)->count(),
                    'viewed_count' => 0, // Extend with shop_views table later
                    'profile_visits' => 0, // Extend with shop_visits table later
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get insights failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error','error_message' => $e->getMessage()], 500);
        }
    }

    // ✅ Reply to comment (with ownership check)
    public function replyToComment(Request $r, $commentId)
    {
        try {
            $parent = Comment::findOrFail($commentId);
            $shop = Shop::findOrFail($r->shop_id);
            if ($shop->owner_id !== auth('sanctum')->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $validated = $r->validate(['content' => 'required|string|max:500']);
            $reply = Comment::create([
                'parent_id' => $commentId,
                'shop_id' => $shop->id,
                'commenter_id' => auth('sanctum')->id(),
                'content' => $validated['content'],
            ]);
            return response()->json(['success' => true, 'data' => $reply->load('commenter:id,name,image')], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Reply to comment failed: ' . $e->getMessage());
            return response()->json(
                ['success' => false, 
                'message' => 'Server error',
                'error_message' => $e->getMessage()
                ], 500);
        }
    }
}