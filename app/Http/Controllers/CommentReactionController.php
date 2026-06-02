<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Events\NotificationTriggered;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentReactionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'comment_id' => 'required|exists:comments,id',
            'value' => 'required|string|in:like,dislike,remove',
        ]);

        $userId = $request->input('liker_id', Auth::id());
        $commentId = $request->comment_id;

        // Check if user already reacted
        $existing = CommentReaction::where('comment_id', $commentId)
            ->where('liker_id', $userId)
            ->first();

        if ($request->value === 'remove') {
            if ($existing) {
                $existing->delete();
            }
        } else {
            if ($existing) {
                if ($existing->value === $request->value) {
                    $existing->delete();
                } else {
                    $existing->update(['value' => $request->value]);
                }
            } else {
                CommentReaction::create([
                    'comment_id' => $commentId,
                    'liker_id' => $userId,
                    'value' => $request->value,
                ]);

                if ($request->value === 'like') {
                    try {
                        $comment = Comment::find($commentId);
                        if ($comment && $comment->user_id != $userId) {
                            $shop = Shop::find($comment->commented_id);
                            NotificationTriggered::dispatch('comment_liked', [
                                'comment_id' => $commentId,
                                'user_id' => $comment->user_id,
                                'liker_name' => $request->user()->name ?? 'Someone',
                                'shop_name' => $shop?->name ?? 'a shop',
                            ]);
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Comment like notification skipped: ' . $e->getMessage());
                    }
                }
            }
        }

        $reactions = CommentReaction::where('comment_id', $commentId)->get();

        return response()->json([
            'data' => [
                'reactions' => $reactions->map(fn ($r) => [
                    'id' => $r->id,
                    'comment_id' => $r->comment_id,
                    'liker_id' => $r->liker_id,
                    'value' => $r->value,
                ]),
            ]
        ]);
    }
}