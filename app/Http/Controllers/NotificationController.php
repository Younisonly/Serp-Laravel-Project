<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class notificationController extends Controller
{
    /**
     * Public endpoint: POST /api/send-notification
     */
    public function store(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|integer|exists:users,id',
            'title'    => 'required|string|max:255',
            'content'  => 'required|string|max:5000',
            'sender'   => 'required|string|max:255',
        ]);

        $note = Notification::create($request->only('owner_id', 'title', 'content', 'sender'));

        return response()->json([
            'message' => 'Notification stored',
            'data'    => $note
        ], 201);
    }

    /**
     * Helper: create a notification from anywhere
     */
    public static function push(int $ownerId, string $title, string $content, string $sender = 'system')
    {
        return Notification::create([
            'owner_id' => $ownerId,
            'title'    => $title,
            'content'  => $content,
            'sender'   => $sender,
        ]);
    }
}
