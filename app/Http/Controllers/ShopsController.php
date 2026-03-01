<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Comment;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopsController extends Controller
{
    // ✅ Get shop by owner ID
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

    // ✅ Update shop status
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
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    // ✅ Analytics (fixed: polymorphic comments)
    public function getAnalytics($id)
    {
        try {
            $shop = Shop::findOrFail($id);
            
            // ✅ Count comments using polymorphic fields
            $totalComments = Comment::where('commentable_type', Shop::class)
                ->where('commented_id', $id)
                ->whereNull('parent_id')
                ->count();
                
            return response()->json([
                'success' => true,
                'data' => [
                    'total_views' => $shop->views ?? 0,
                    'total_favorites' => $shop->followers_number ?? 0,
                    'total_comments' => $totalComments,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get analytics failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    // ✅ Comments (fixed: polymorphic query)
    public function getComments($id, Request $r)
    {
        try {
            $limit = min((int)$r->get('limit', 3), 20);
            
            $comments = Comment::where('commentable_type', Shop::class)
                ->where('commented_id', $id)
                ->whereNull('parent_id')
                ->with('commenter:id,name,image')
                ->latest()
                ->limit($limit)
                ->get();
                
            return response()->json(['success' => true, 'data' => $comments], 200);
        } catch (\Exception $e) {
            Log::error('Get comments failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    // ✅ User insights (fixed: polymorphic favorites)
    public function getInsights($id)
    {
        try {
            $favoritedCount = Favorite::where('favorable_type', Shop::class)
                ->where('favorable_id', $id)
                ->count();
                
            return response()->json([
                'success' => true,
                'data' => [
                    'favorited_count' => $favoritedCount,
                    'viewed_count' => 0,
                    'profile_visits' => 0,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get insights failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    // ✅ Reply to comment (fixed: polymorphic create)
    public function replyToComment(Request $r, $commentId)
    {
        try {
            $parent = Comment::findOrFail($commentId);
            
            $validated = $r->validate([
                'shop_id' => 'required|exists:shops,id',
                'content' => 'required|string|max:500',
            ]);
            
            $shop = Shop::findOrFail($validated['shop_id']);
            
            if ($shop->owner_id !== auth('sanctum')->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $reply = Comment::create([
                'parent_id' => $commentId,
                'commentable_type' => Shop::class,
                'commented_id' => $shop->id,
                'commenter_id' => auth('sanctum')->id(),
                'content' => $validated['content'],
            ]);
            
            return response()->json(['success' => true, 'data' => $reply->load('commenter:id,name,image')], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Reply to comment failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}