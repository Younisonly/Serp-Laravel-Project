<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CommentReaction;
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

        $userId = Auth::id();
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
                    // User removes reaction (toggle off)
                    $existing->delete();
                } else {
                    // User switches (like → dislike)
                    $existing->update(['value' => $request->value]);
                }
            } else {
                // New reaction
                CommentReaction::create([
                    'comment_id' => $commentId,
                    'liker_id' => $userId,
                    'value' => $request->value,
                ]);
            }
        }

        return response()->json(['message' => 'تم تحديث التفاعل']);
    }
}