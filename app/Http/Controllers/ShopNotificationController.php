<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopNotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $shopIds = Shop::where('owner_id', $userId)
                ->pluck('id')
                ->toArray();

            $notifications = Notification::where(function ($q) use ($userId, $shopIds) {
                $q->where('owner_id', $userId)->where('owner_type', 'user');
                if (!empty($shopIds)) {
                    $q->orWhereIn('owner_id', $shopIds)->where('owner_type', 'shop');
                }
            })
                ->latest()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $notifications->map(fn($n) => static::format($n)),
                'meta' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('List notifications failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function unreadCount(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $shopIds = Shop::where('owner_id', $userId)
                ->pluck('id')
                ->toArray();

            $count = Notification::where(function ($q) use ($userId, $shopIds) {
                $q->where('owner_id', $userId)->where('owner_type', 'user');
                if (!empty($shopIds)) {
                    $q->orWhereIn('owner_id', $shopIds)->where('owner_type', 'shop');
                }
            })
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'data' => ['unread_count' => $count],
            ]);
        } catch (\Exception $e) {
            Log::error('Unread count failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function markRead(Request $request, $notificationId)
    {
        try {
            $userId = $request->user()->id;

            $shopIds = Shop::where('owner_id', $userId)
                ->pluck('id')
                ->toArray();

            $notification = Notification::where(function ($q) use ($userId, $shopIds) {
                $q->where('owner_id', $userId)->where('owner_type', 'user');
                if (!empty($shopIds)) {
                    $q->orWhereIn('owner_id', $shopIds)->where('owner_type', 'shop');
                }
            })
                ->findOrFail($notificationId);

            $notification->update(['is_read' => true, 'updated_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
            ]);
        } catch (\Exception $e) {
            Log::error('Mark read failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function markAllRead(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $shopIds = Shop::where('owner_id', $userId)
                ->pluck('id')
                ->toArray();

            Notification::where(function ($q) use ($userId, $shopIds) {
                $q->where('owner_id', $userId)->where('owner_type', 'user');
                if (!empty($shopIds)) {
                    $q->orWhereIn('owner_id', $shopIds)->where('owner_type', 'shop');
                }
            })
                ->where('is_read', false)
                ->update(['is_read' => true, 'updated_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.',
            ]);
        } catch (\Exception $e) {
            Log::error('Mark all read failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function destroy(Request $request, $notificationId)
    {
        try {
            $userId = $request->user()->id;

            $shopIds = Shop::where('owner_id', $userId)
                ->pluck('id')
                ->toArray();

            $notification = Notification::where(function ($q) use ($userId, $shopIds) {
                $q->where('owner_id', $userId)->where('owner_type', 'user');
                if (!empty($shopIds)) {
                    $q->orWhereIn('owner_id', $shopIds)->where('owner_type', 'shop');
                }
            })
                ->findOrFail($notificationId);

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted.',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete notification failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public static function format($n): array
    {
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
            'created_at' => $n->created_at->toISOString(),
        ];
    }

    public static function push(
        int $ownerId,
        string $title,
        string $content,
        string $type = 'system',
        ?string $actionType = null,
        ?array $data = null,
        string $sender = 'system',
        string $ownerType = 'user'
    ) {
        return Notification::create([
            'owner_id' => $ownerId,
            'owner_type' => $ownerType,
            'title' => $title,
            'content' => $content,
            'sender' => $sender,
            'type' => $type,
            'action_type' => $actionType,
            'data' => $data,
            'is_read' => false,
        ]);
    }
}
