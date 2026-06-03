<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $page = $request->integer('page', 1);

            $cacheKey = "notifications:user:{$userId}:page:{$page}";
            $result = Cache::remember($cacheKey, 60, function () use ($userId) {
                $notifications = Notification::forUser($userId)
                    ->latestFirst()
                    ->paginate(20);

                $data = $notifications->map(fn($n) => $this->format($n));

                return [
                    'data' => $data,
                    'meta' => [
                        'current_page' => $notifications->currentPage(),
                        'last_page' => $notifications->lastPage(),
                        'per_page' => $notifications->perPage(),
                        'total' => $notifications->total(),
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'meta' => $result['meta'],
            ]);
        } catch (\Throwable $e) {
            Log::error('List notifications failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function unreadCount(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $cacheKey = "notifications:unread:{$userId}";
            $count = Cache::remember($cacheKey, 60, function () use ($userId) {
                return Notification::forUser($userId)
                    ->unread()
                    ->count();
            });

            return response()->json([
                'success' => true,
                'data' => ['unread_count' => $count],
            ]);
        } catch (\Throwable $e) {
            Log::error('Unread count failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function markRead(Request $request, $notificationId)
    {
        try {
            $userId = $request->user()->id;

            $notification = $this->findOwned($notificationId, $userId);
            $notification->markAsRead();

            $this->clearUserCache($userId);

            return response()->json(['success' => true, 'message' => 'Marked as read.']);
        } catch (\Throwable $e) {
            Log::error('Mark read failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }
    }

    public function markAllRead(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $count = Notification::forUser($userId)
                ->unread()
                ->update(['is_read' => true, 'updated_at' => now()]);

            $this->clearUserCache($userId);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.',
                'data' => ['updated_count' => $count],
            ]);
        } catch (\Throwable $e) {
            Log::error('Mark all read failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function destroy(Request $request, $notificationId)
    {
        try {
            $userId = $request->user()->id;

            $notification = $this->findOwned($notificationId, $userId);
            $notification->delete();

            $this->clearUserCache($userId);

            return response()->json(['success' => true, 'message' => 'Notification deleted.']);
        } catch (\Throwable $e) {
            Log::error('Delete notification failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────

    private function clearUserCache(int $userId): void
    {
        $keys = [
            "notifications:unread:{$userId}",
        ];
        Cache::forget("notifications:user:{$userId}:page:1");
        Cache::forget("notifications:user:{$userId}:page:2");
        Cache::forget("notifications:user:{$userId}:page:3");
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    private function findOwned(int $id, int $userId): Notification
    {
        return Notification::forUser($userId)->findOrFail($id);
    }

    private function format(Notification $n): array
    {
        $deepLink = null;
        if ($n->data && isset($n->data['deep_link'])) {
            $deepLink = $n->data['deep_link'];
        }

        $titleI18n = $n->data['title_i18n'] ?? null;
        $contentI18n = $n->data['content_i18n'] ?? null;
        $templateKey = $n->data['template_key'] ?? null;
        $templateVariables = $n->data['vars'] ?? null;

        return [
            'id' => $n->id,
            'title' => $n->title,
            'content' => $n->content,
            'title_i18n' => $titleI18n,
            'content_i18n' => $contentI18n,
            'template_key' => $templateKey,
            'template_variables' => $templateVariables,
            'sender' => $n->sender,
            'type' => $n->type ?? 'system',
            'action_type' => $n->action_type,
            'data' => $n->data,
            'is_read' => (bool) $n->is_read,
            'owner_type' => $n->owner_type ?? 'user',
            'deep_link' => $deepLink,
            'target_type' => $n->target_type,
            'target_id' => $n->target_id ? (int) $n->target_id : null,
            'navigation_type' => $n->navigation_type,
            'metadata' => $n->metadata,
            'created_at' => $n->created_at->toISOString(),
        ];
    }
}
