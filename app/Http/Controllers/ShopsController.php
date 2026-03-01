<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Comment;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

    // ✅ Analytics (fixed: no parent_id, uses your schema)
    public function getAnalytics($id)
    {
        try {
            $shop = Shop::findOrFail($id);
            
            // ✅ Count comments: your schema has NO parent_id column
            $totalComments = Comment::where('commentable_type', Shop::class)
                ->where('commented_id', $id)
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

    // ✅ Comments (fixed: no parent_id, returns embedded replies)
    public function getComments($id, Request $r)
    {
        try {
            $limit = min((int)$r->get('limit', 3), 20);
            
            // ✅ Your schema: NO parent_id column → remove that filter
            $comments = Comment::where('commentable_type', Shop::class)
                ->where('commented_id', $id)
                ->with('commenter:id,name,image')
                ->latest()
                ->limit($limit)
                ->get()
                // ✅ Append embedded reply data using your accessor
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'created_at' => $comment->created_at,
                        'commenter' => $comment->commenter ? [
                            'id' => $comment->commenter->id,
                            'name' => $comment->commenter->name,
                            'image' => $comment->commenter->image ?? '',
                        ] : null,
                        // ✅ Your embedded reply structure
                        'reply' => $comment->reply_content ? [
                            'content' => $comment->reply_content,
                            'replyer' => $comment->replyer_type ?? 'admin',
                            'created_at' => $comment->replied_at ?? $comment->updated_at,
                        ] : null,
                    ];
                });
                
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

    // ✅ Reply to comment (fixed: UPDATE existing comment, not CREATE new)
    public function replyToComment(Request $r, $commentId)
    {
        try {
            // ✅ Get the parent comment
            $comment = Comment::findOrFail($commentId);
            
            // ✅ Verify it belongs to a shop the user owns
            if ($comment->commentable_type !== Shop::class) {
                return response()->json(['success' => false, 'message' => 'Invalid comment type'], 400);
            }
            
            $shop = Shop::findOrFail($comment->commented_id);
            
            // ✅ Check ownership
            if ($shop->owner_id !== auth('sanctum')->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            // ✅ Validate reply content
            $validated = $r->validate([
                'content' => 'required|string|max:500',
            ]);
            
            // ✅ UPDATE the comment with embedded reply (your schema)
            $comment->update([
                'reply_content' => $validated['content'],
                'replyer_type' => 'shop_owner', // or 'admin'
                'replied_at' => now(),
            ]);
            
            // ✅ Reload with commenter data
            $comment->load('commenter:id,name,image');
            
            // ✅ Return structured response matching your accessor
            return response()->json([
                'success' => true, 
                'data' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'commenter' => $comment->commenter ? [
                        'id' => $comment->commenter->id,
                        'name' => $comment->commenter->name,
                        'image' => $comment->commenter->image ?? '',
                    ] : null,
                    'reply' => [
                        'content' => $comment->reply_content,
                        'replyer' => $comment->replyer_type,
                        'created_at' => $comment->replied_at,
                    ],
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Reply to comment failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}