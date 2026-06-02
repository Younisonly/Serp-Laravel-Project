<?php

namespace App\Http\Controllers;

use App\Events\NotificationTriggered;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Product;
use App\Models\Report;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'table'        => 'required|string|in:products,shops',
                'commented_id' => 'required|integer',
            ]);

            $perPage = min((int) $request->get('per_page', 20), 50);
            $sort = $request->get('sort', 'recent');

            $query = Comment::with(['commenter', 'reactions'])
                ->where('commentable_type', $request->table)
                ->where('commented_id', $request->commented_id)
                ->where('status', 'active')
                ->topLevel();

            switch ($sort) {
                case 'oldest':
                    $query->oldest();
                    break;
                case 'most_liked':
                    $query->withCount(['reactions as likes_count' => fn($q) => $q->where('value', 'like')])
                        ->orderBy('likes_count', 'desc');
                    break;
                default:
                    $query->latest();
                    break;
            }

            $comments = $query->paginate($perPage);

            $comments->getCollection()->transform(function ($c) {
                return $this->formatComment($c);
            });

            return response()->json($comments);
        } catch (\Throwable $e) {
            Log::error('CommentController index failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'content' => 'required|string|max:5000',
                'table' => 'required|string|in:products,shops',
                'commented_id' => 'required|integer|min:1',
                'parent_id' => 'nullable|integer|exists:comments,id',
            ]);

            $content = strip_tags($request->content);

            if (mb_strlen($content) < 1) {
                return response()->json(['status' => 'error', 'message' => 'Comment cannot be empty.'], 422);
            }

            if ($request->parent_id) {
                $parent = Comment::findOrFail($request->parent_id);
                if ($parent->parent_id !== null) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cannot reply to a reply. Reply to the original comment instead.',
                    ], 422);
                }
                if ($parent->commenter_id === Auth::id()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You cannot reply to your own comment.',
                    ], 422);
                }
            }

            $comment = Comment::create([
                'parent_id' => $request->parent_id,
                'commenter_id' => Auth::id(),
                'commentable_type' => $request->table,
                'commented_id' => $request->commented_id,
                'content' => $content,
                'status' => 'active',
            ]);

            $comment->load('commenter');

            // ─── Fire notifications ────────────────────────────────
            try {
                $commenterName = $comment->commenter?->name ?? 'Someone';
                $snippet = mb_substr(strip_tags($comment->content), 0, 100);

                if ($request->parent_id) {
                    // Reply to a comment
                    $parent = Comment::find($request->parent_id);
                    if ($parent && $parent->commenter_id !== Auth::id()) {
                        $shop = $this->resolveShop($request->table, $request->commented_id);
                        NotificationTriggered::dispatch('reply_received', [
                            'comment' => $parent,
                            'shop' => $shop,
                            'reply_snippet' => $snippet,
                        ]);
                    }
                } else {
                    // New top-level comment
                    $shop = $this->resolveShop($request->table, $request->commented_id);
                    if ($shop && $shop->owner_id !== Auth::id()) {
                        NotificationTriggered::dispatch('new_comment', [
                            'shop' => $shop,
                            'comment_id' => $comment->id,
                            'comment_snippet' => $snippet,
                            'commenter_name' => $commenterName,
                        ]);
                    }
                }
            } catch (\Throwable $notifErr) {
                Log::warning('Comment notification skipped: ' . $notifErr->getMessage());
            }

            return response()->json([
                'message' => 'Comment added successfully',
                'data' => [
                    'id' => $comment->id,
                    'parent_id' => $comment->parent_id,
                    'commenter_id' => $comment->commenter_id,
                    'user_name' => $comment->commenter?->name ?? 'User',
                    'user_image' => $comment->commenter?->image ?? null,
                    'content' => $comment->content,
                    'status' => $comment->status,
                    'created_at' => $comment->created_at->toISOString(),
                    'reactions' => [],
                ]
            ], 201);

        } catch (\Throwable $e) {
            Log::error('CommentController store failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'content' => 'required|string|max:5000',
            ]);

            $comment = Comment::with('commenter')->findOrFail($id);

            if ($comment->commenter_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $comment->update(['content' => strip_tags($request->content)]);

            return response()->json([
                'data' => $this->formatCommentFlat($comment),
            ]);
        } catch (\Throwable $e) {
            Log::error('CommentController update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $comment = Comment::findOrFail($id);

            if ($comment->commenter_id !== Auth::id()) {
                return response()->json([
                    'error' => 'You are not authorized to delete this comment'
                ], 403);
            }

            $comment->update(['status' => 'deleted']);

            return response()->json([
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('CommentController destroy failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    public function reply(Request $request, $commentId)
    {
        return app(ShopsController::class)->replyToComment($request, $commentId);
    }

    private function resolveShop(string $table, int $id): ?Shop
    {
        if ($table === 'shops') {
            return Shop::find($id);
        }
        if ($table === 'products') {
            $product = Product::find($id);
            return $product ? Shop::find($product->shop_id) : null;
        }
        return null;
    }

    private function formatComment($comment)
    {
        $replies = Comment::with(['commenter', 'reactions'])
            ->where('parent_id', $comment->id)
            ->where('status', 'active')
            ->latest()
            ->get()
            ->map(fn($r) => $this->formatCommentFlat($r));

        return array_merge($this->formatCommentFlat($comment), [
            'replies' => $replies,
        ]);
    }

    private function formatCommentFlat($comment)
    {
        return [
            'id' => $comment->id,
            'parent_id' => $comment->parent_id,
            'commenter_id' => $comment->commenter_id,
            'user_name' => $comment->commenter?->name ?? 'User',
            'user_image' => $comment->commenter?->image ?? null,
            'content' => $comment->content,
            'status' => $comment->status,
            'created_at' => $comment->created_at->toISOString(),
            'reactions' => $comment->reactions->map(fn($r) => [
                'id' => $r->id,
                'comment_id' => $r->comment_id,
                'liker_id' => $r->liker_id,
                'value' => $r->value,
            ]),
            'reply' => $comment->reply,
        ];
    }
}
