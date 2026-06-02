<?php

namespace App\Http\Controllers;

use App\Events\NotificationTriggered;
use App\Models\Shop;
use App\Models\Comment;
use App\Models\Favorite;
use App\Http\Resources\ShopResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class ShopsController extends Controller
{
    public function getShopById($id)
    {
        try {
            $shop = Shop::with('phones', 'images', 'primaryImage', 'owner:id,name,image')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $shop->toArray()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get shop failed', ['shop_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Shop not found'], 404);
        }
    }

    public function getByOwner($id)
    {
        try {
            $authenticatedUserId = auth('sanctum')->id();
            if ((int) $authenticatedUserId !== (int) $id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $shop = Shop::with('phones', 'images', 'primaryImage')->where('owner_id', $id)->first();
            if (!$shop) {
                return response()->json(['success' => false, 'message' => 'Shop not found'], 404);
            }
            return response()->json(['success' => true, 'data' => $shop->toArray()], 200);
        } catch (\Exception $e) {
            Log::error('Get shop by owner failed', ['owner_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

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
            Log::error('Update status failed', ['shop_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function getAnalytics($id)
    {
        try {
            $shop = Shop::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'total_views' => $shop->views ?? 0,
                    'total_favorites' => $shop->followers_number ?? 0,
                    'total_comments' => Comment::where('commentable_type', Shop::class)
                        ->where('commented_id', $id)->count(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get analytics failed', ['shop_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function getComments($id, Request $r)
    {
        try {
            $limit = min((int)$r->get('limit', 10), 50);
            $page = max((int)$r->get('page', 1), 1);
            $sort = $r->get('sort', 'recent');
            $filter = $r->get('filter', 'all');
            $shop = Shop::find($id);
            $ownerId = $shop ? $shop->owner_id : null;

            $query = Comment::where('commentable_type', 'shops')
                ->where('commented_id', $id)
                ->where('status', 'active')
                ->with(['commenter:id,name,image', 'reactions'])
                ->withCount(['reactions as likes_count' => fn($q) => $q->where('value', 'like')])
                ->withCount(['reactions as dislikes_count' => fn($q) => $q->where('value', 'dislike')])
                ->withCount('replies')
                ->topLevel();

            switch ($filter) {
                case 'unreplied':
                    $query->whereNull('reply_content');
                    break;
                case 'replied':
                    $query->whereNotNull('reply_content');
                    break;
                case 'reported':
                    $query->where('reports_count', '>', 0);
                    break;
                case 'most_liked':
                    $query->orderBy('likes_count', 'desc');
                    break;
                case 'most_disliked':
                    $query->orderBy('dislikes_count', 'desc');
                    break;
                case 'oldest':
                    $query->oldest();
                    break;
                default:
                    $query->latest();
                    break;
            }

            if (!in_array($filter, ['most_liked', 'most_disliked', 'oldest'])) {
                $query->latest();
            }

            $totalQuery = clone $query;
            $total = $totalQuery->count();

            $comments = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get()
                ->map(function ($comment) use ($ownerId) {
                    return [
                        'id' => $comment->id,
                        'commenter_id' => $comment->commenter_id,
                        'user_name' => $comment->commenter?->name ?? 'User',
                        'user_image' => $comment->commenter?->image ?? '',
                        'content' => $comment->content,
                        'status' => $comment->status,
                        'created_at' => $comment->created_at,
                        'reply' => $comment->reply_content ? [
                            'content' => $comment->reply_content,
                            'replyer' => $comment->replyer_type ?? 'admin',
                            'created_at' => $comment->replied_at ?? $comment->updated_at,
                        ] : null,
                        'is_shop_owner' => $comment->commenter_id === $ownerId,
                        'likes' => (int) $comment->likes_count,
                        'dislikes' => (int) $comment->dislikes_count,
                        'reply_count' => (int) $comment->replies_count,
                        'reported' => (int) $comment->reports_count > 0,
                        'reactions' => $comment->reactions->map(fn($r) => [
                            'id' => $r->id,
                            'comment_id' => $r->comment_id,
                            'liker_id' => $r->liker_id,
                            'value' => $r->value,
                        ])->values()->toArray(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $comments,
                'total' => $total,
                'page' => $page,
                'last_page' => (int) ceil($total / $limit),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get comments failed', ['shop_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function getInsights($id)
    {
        try {
            $favoritedCount = Favorite::where('favorable_type', Shop::class)
                ->where('favorable_id', $id)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'favorited_count' => $favoritedCount,
                    'viewed_count' => 0,
                    'profile_visits' => 0,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get insights failed', ['shop_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function replyToComment(Request $r, $commentId)
    {
        try {
            $comment = Comment::findOrFail($commentId);

            if ($comment->commentable_type !== 'shops') {
                return response()->json(['success' => false, 'message' => 'Invalid comment type'], 400);
            }

            $shop = Shop::findOrFail($comment->commented_id);

            if ($shop->owner_id !== auth('sanctum')->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $r->validate([
                'content' => 'required|string|max:5000',
            ]);

            $comment->update([
                'reply_content' => $validated['content'],
                'replyer_type' => 'shop_owner',
                'replied_at' => now(),
            ]);

            $comment->load('commenter:id,name,image');

            try {
                NotificationTriggered::dispatch('reply_received', [
                    'comment' => $comment,
                    'shop' => $shop,
                    'reply_snippet' => mb_substr(strip_tags($validated['content']), 0, 100),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Reply notification skipped: ' . $e->getMessage());
            }

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
            Log::error('Reply to comment failed', ['comment_id' => $commentId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function updateReply(Request $r, $commentId)
    {
        try {
            $comment = Comment::findOrFail($commentId);

            if ($comment->commentable_type !== 'shops') {
                return response()->json(['success' => false, 'message' => 'Invalid comment type'], 400);
            }

            $shop = Shop::findOrFail($comment->commented_id);

            if ($shop->owner_id !== auth('sanctum')->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $r->validate([
                'content' => 'required|string|max:5000',
            ]);

            $comment->update([
                'reply_content' => $validated['content'],
                'replied_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $comment->fresh()->reply_content,
                    'created_at' => $comment->fresh()->replied_at,
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Update reply failed', ['comment_id' => $commentId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function deleteReply($commentId)
    {
        try {
            $comment = Comment::findOrFail($commentId);

            if ($comment->commentable_type !== 'shops') {
                return response()->json(['success' => false, 'message' => 'Invalid comment type'], 400);
            }

            $shop = Shop::findOrFail($comment->commented_id);

            if ($shop->owner_id !== auth('sanctum')->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $comment->update([
                'reply_content' => null,
                'replyer_type' => null,
                'replied_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reply deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Delete reply failed', ['comment_id' => $commentId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}