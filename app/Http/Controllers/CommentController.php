<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Report;
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
    
        $comments = Comment::with('reactions')
            ->where('commentable_type', $request->table)
            ->where('commented_id', $request->commented_id)
            ->where('status', 'active')
            ->latest()
            ->get()
            ->map(fn ($c) => [
                'id'            => $c->id,
                'commenter_id'  => $c->commenter_id,
                'user_name'     => $c->user_name,
                'user_image'    => $c->user_image,
                'content'       => $c->content,
                'status'        => $c->status,
                'created_at'    => $c->created_at->toDateTimeString(),
                'reactions'     => $c->reactions->map(fn ($r) => [
                    'id'         => $r->id,
                    'comment_id' => $r->comment_id,
                    'liker_id'   => $r->liker_id,
                    'value'      => $r->value,
                ]),
                'reply'         => $c->reply
            ]);
    Log::info($comments
        );
        return response()->json($comments);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong while fetching comments.',
            'error_message' => $e->getMessage(),
        ], 500);
    }
}
    
    public function store(Request $request)
    {
       
        try{
        $request->validate([
            'content' => 'required|string|max:1000',
            'table' => 'required|string|in:products,shops',
            'commented_id' => 'required|integer',
        ]);

        $comment = Comment::create([
            'commenter_id' => Auth::id(),
            'commentable_type' => $request->table,
            'commented_id' => $request->commented_id,
            'content' => $request->content,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'تم إضافة التعليق بنجاح',
            'data' => [
                'id' => $comment->id,
                'commenter_id' => $comment->commenter_id,
                'user_name' => $comment->user_name,
                'user_image' => $comment->user_image,
                'content' => $comment->content,
                'status' => $comment->status,
                'created_at' => $comment->created_at,
                'likes' => 0,
                'dislikes' => 0,
            ]
        ], 201);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong while fetching comments.',
            'error_message' => $e->getMessage(),
        ], 500);
    }
    }

    /**
     * Delete a comment (only if it belongs to the authenticated user)
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->commenter_id != Auth::id()) {
            return response()->json([
                'error' => 'ليس لديك صلاحية لحذف هذا التعليق'
            ], 403);
        }

            // mark as deleted instead of deleting
    $comment->update(['status' => 'deleted']);

    return response()->json([
        'message' => 'تم حذف التعليق بنجاح'
    ]);
    }
}