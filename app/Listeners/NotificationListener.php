<?php

namespace App\Listeners;

use App\Events\NotificationTriggered;
use App\Models\Shop;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class NotificationListener
{
    private NotificationService $notif;

    public function __construct(NotificationService $notif)
    {
        $this->notif = $notif;
    }

    public function handle(NotificationTriggered $event): void
    {
        try {
            match ($event->action) {
                // ─── REPORTS ──────────────────────────────────────────
                'report_comment' => $this->onReportComment($event),
                'report_shop'    => $this->onReportShop($event),
                'report_product' => $this->onReportProduct($event),

                // ─── COMMENTS ─────────────────────────────────────────
                'new_comment'    => $this->onNewComment($event),
                'reply_received' => $this->onReplyReceived($event),
                'comment_liked'      => $this->onCommentLiked($event),

                // ─── ENGAGEMENT ───────────────────────────────────────
                'new_follower'   => $this->onNewFollower($event),
                'shop_liked'     => $this->onShopLiked($event),
                'new_favorite'   => $this->onNewFavorite($event),

                // ─── PRODUCT LIKES ──────────────────────────────────────
                'product_liked'      => $this->onProductLiked($event),
                'product_favorited'  => $this->onProductFavorited($event),

                // ─── PRODUCTS ─────────────────────────────────────────
                'product_added'      => $this->onProductAdded($event),
                'product_out_of_stock' => $this->onProductOutOfStock($event),
                'product_updated'    => $this->onProductUpdated($event),
                'product_deleted'    => $this->onProductDeleted($event),

                // ─── ACCOUNT ──────────────────────────────────────────
                'welcome_user'       => $this->onWelcomeUser($event),
                'shop_registered'    => $this->onShopRegistered($event),
                'profile_updated'    => $this->onProfileUpdated($event),
                'shop_updated'       => $this->onShopUpdated($event),
                'password_changed'   => $this->onPasswordChanged($event),

                // ─── VERIFICATION ─────────────────────────────────────
                'shop_verified'      => $this->onShopVerified($event),
                'shop_rejected'      => $this->onShopRejected($event),
                'shop_suspended'     => $this->onShopSuspended($event),

                // ─── SYSTEM ──────────────────────────────────────────
                'support_ticket_created' => $this->onSupportTicketCreated($event),
                'support_ticket_replied' => $this->onSupportTicketReplied($event),

                default => Log::warning("Unknown notification action: {$event->action}"),
            };
        } catch (\Throwable $e) {
            Log::error("NotificationListener failed for {$event->action}: {$e->getMessage()}");
        }
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────

    private function deliver(int $ownerId, string $templateKey, array $variables, array $options = []): void
    {
        $this->notif->deliver($ownerId, $templateKey, $variables, $options);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  REPORTS — NEVER expose reporter identity
    // ═══════════════════════════════════════════════════════════════════════

    private function onReportComment(NotificationTriggered $event): void
    {
        $comment = $event->data['comment'] ?? null;
        $shop = $event->data['shop'] ?? null;
        $reason = $event->data['reason'] ?? '';
        $commentSnippet = $comment ? mb_substr(strip_tags($comment->content ?? ''), 0, 150) : '';

        // Notify comment author
        if ($comment && isset($comment->user_id)) {
            $this->deliver($comment->user_id, 'comment_reported', [
                'comment_snippet' => $commentSnippet,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => [
                    'comment_id' => $comment->id,
                    'shop_id' => $shop?->id,
                    'report_table' => 'comments',
                ],
            ]);
        }

        // Notify shop owner — WITHOUT reporter info
        if ($shop && $shop->owner_id) {
            $safeData = ['report_table' => 'comments', 'shop_id' => $shop->id, 'comment_id' => $comment?->id];
            $this->deliver($shop->owner_id, 'shop_reported', [
                'shop_name' => $shop->name,
                'reason' => $reason,
                'comment_snippet' => $commentSnippet,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => $safeData,
            ]);
        }
    }

    private function onReportShop(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $reason = $event->data['reason'] ?? '';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'shop_reported', [
                'shop_name' => $shop->name,
                'reason' => $reason,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['report_table' => 'shops', 'shop_id' => $shop->id],
            ]);
        }
    }

    private function onReportProduct(NotificationTriggered $event): void
    {
        $product = $event->data['product'] ?? null;
        $shop = $event->data['shop'] ?? null;
        $reason = $event->data['reason'] ?? '';
        $productName = $product?->name ?? $event->data['product_name'] ?? '';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'product_reported', [
                'product_name' => $productName,
                'shop_name' => $shop->name,
                'reason' => $reason,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['report_table' => 'products', 'shop_id' => $shop->id, 'product_id' => $product?->id],
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  COMMENTS
    // ═══════════════════════════════════════════════════════════════════════

    private function onNewComment(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $commentSnippet = $event->data['comment_snippet'] ?? '';
        $commenterName = $event->data['commenter_name'] ?? 'Someone';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'new_comment', [
                'shop_name' => $shop->name,
                'comment_snippet' => $commentSnippet,
                'user_name' => $commenterName,
            ], [
                'sender' => $commenterName,
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop->id, 'comment_id' => $event->data['comment_id'] ?? null],
            ]);
        }
    }

    private function onReplyReceived(NotificationTriggered $event): void
    {
        $comment = $event->data['comment'] ?? null;
        $shop = $event->data['shop'] ?? null;
        $replySnippet = $event->data['reply_snippet'] ?? '';

        if ($comment && isset($comment->user_id)) {
            $this->deliver($comment->user_id, 'reply_received', [
                'shop_name' => $shop?->name ?? 'Shop',
                'comment_snippet' => $replySnippet,
            ], [
                'sender' => $shop?->name ?? 'Shop Owner',
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop?->id, 'comment_id' => $comment->id],
            ]);
        }
    }

    private function onCommentLiked(NotificationTriggered $event): void
    {
        $userId = $event->data['user_id'] ?? 0;
        $likerName = $event->data['liker_name'] ?? 'Someone';
        $shopName = $event->data['shop_name'] ?? '';

        if ($userId) {
            $this->deliver((int) $userId, 'comment_liked', [
                'user_name' => $likerName,
                'shop_name' => $shopName,
            ], [
                'sender' => $likerName,
                'owner_type' => 'user',
                'data' => ['comment_id' => $event->data['comment_id'] ?? null],
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ENGAGEMENT
    // ═══════════════════════════════════════════════════════════════════════

    private function onNewFollower(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $followerName = $event->data['follower_name'] ?? 'Someone';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'new_follower', [
                'shop_name' => $shop->name,
                'follower_name' => $followerName,
            ], [
                'sender' => $followerName,
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop->id],
            ]);
        }
    }

    private function onShopLiked(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $likerName = $event->data['liker_name'] ?? 'Someone';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'shop_liked', [
                'shop_name' => $shop->name,
                'user_name' => $likerName,
            ], [
                'sender' => $likerName,
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop->id],
            ]);
        }
    }

    private function onNewFavorite(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $userName = $event->data['user_name'] ?? 'Someone';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'new_favorite', [
                'shop_name' => $shop->name,
                'user_name' => $userName,
            ], [
                'sender' => $userName,
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop->id],
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  PRODUCT LIKES
    // ═══════════════════════════════════════════════════════════════════════

    private function onProductLiked(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? Shop::find($event->data['shop_id'] ?? 0);
        $productName = $event->data['product_name'] ?? '';
        $likerName = $event->data['liker_name'] ?? 'Someone';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'product_liked', [
                'product_name' => $productName,
                'shop_name' => $shop->name,
                'user_name' => $likerName,
            ], [
                'sender' => $likerName,
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop->id, 'product_id' => $event->data['product_id'] ?? null],
            ]);
        }
    }

    private function onProductFavorited(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? Shop::find($event->data['shop_id'] ?? 0);
        $productName = $event->data['product_name'] ?? '';
        $userName = $event->data['user_name'] ?? 'Someone';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'product_favorited', [
                'product_name' => $productName,
                'shop_name' => $shop->name,
                'user_name' => $userName,
            ], [
                'sender' => $userName,
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop->id, 'product_id' => $event->data['product_id'] ?? null],
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  PRODUCTS
    // ═══════════════════════════════════════════════════════════════════════

    private function onProductAdded(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $productName = $event->data['product_name'] ?? '';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'new_product_added', [
                'shop_name' => $shop->name,
                'product_name' => $productName,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop->id, 'product_id' => $event->data['product_id'] ?? null],
            ]);
        }
    }

    private function onProductOutOfStock(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $productName = $event->data['product_name'] ?? '';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'product_out_of_stock', [
                'shop_name' => $shop->name,
                'product_name' => $productName,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop->id, 'product_id' => $event->data['product_id'] ?? null],
            ]);
        }
    }

    private function onProductUpdated(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $productName = $event->data['product_name'] ?? '';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'product_updated', [
                'product_name' => $productName,
                'shop_name' => $shop->name,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop->id, 'product_id' => $event->data['product_id'] ?? null],
            ]);
        }
    }

    private function onProductDeleted(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $productName = $event->data['product_name'] ?? '';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'product_deleted', [
                'product_name' => $productName,
                'shop_name' => $shop->name,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop->id],
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ACCOUNT
    // ═══════════════════════════════════════════════════════════════════════

    private function onWelcomeUser(NotificationTriggered $event): void
    {
        $user = $event->data['user'] ?? null;
        $userName = $user?->name ?? ($event->data['user_name'] ?? '');
        $userId = $user?->id ?? $event->data['user_id'] ?? 0;

        if ($userId) {
            $this->deliver((int) $userId, 'welcome_user', [
                'user_name' => $userName,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => [],
            ]);
        }
    }

    private function onShopRegistered(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $ownerId = $event->data['owner_id'] ?? $shop?->owner_id ?? 0;

        if ($ownerId) {
            $this->deliver((int) $ownerId, 'shop_registered', [
                'shop_name' => $shop?->name ?? $event->data['shop_name'] ?? '',
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop?->id],
            ]);
        }
    }

    private function onProfileUpdated(NotificationTriggered $event): void
    {
        $userId = $event->data['user_id'] ?? 0;
        $userName = $event->data['user_name'] ?? '';

        if ($userId) {
            $this->deliver((int) $userId, 'profile_updated', [
                'user_name' => $userName,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => [],
            ]);
        }
    }

    private function onShopUpdated(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $userName = $event->data['user_name'] ?? '';

        if ($shop && $shop->owner_id) {
            $this->deliver($shop->owner_id, 'shop_updated', [
                'shop_name' => $shop->name,
                'user_name' => $userName,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop->id],
            ]);
        }
    }

    private function onPasswordChanged(NotificationTriggered $event): void
    {
        $userId = $event->data['user_id'] ?? $event->data['user']?->id ?? 0;

        if ($userId) {
            $this->deliver((int) $userId, 'account_password_changed', [], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => [],
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  VERIFICATION
    // ═══════════════════════════════════════════════════════════════════════

    private function onShopVerified(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $ownerId = $shop?->owner_id ?? $event->data['owner_id'] ?? 0;

        if ($ownerId) {
            $this->deliver((int) $ownerId, 'shop_verified', [
                'shop_name' => $shop?->name ?? '',
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop?->id],
            ]);
        }
    }

    private function onShopRejected(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $ownerId = $shop?->owner_id ?? $event->data['owner_id'] ?? 0;
        $reason = $event->data['reason'] ?? '';

        if ($ownerId) {
            $this->deliver((int) $ownerId, 'shop_rejected', [
                'shop_name' => $shop?->name ?? '',
                'reason' => $reason,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop?->id],
            ]);
        }
    }

    private function onShopSuspended(NotificationTriggered $event): void
    {
        $shop = $event->data['shop'] ?? null;
        $ownerId = $shop?->owner_id ?? $event->data['owner_id'] ?? 0;
        $reason = $event->data['reason'] ?? '';

        if ($ownerId) {
            $this->deliver((int) $ownerId, 'shop_suspended', [
                'shop_name' => $shop?->name ?? '',
                'reason' => $reason,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['shop_id' => $shop?->id],
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SYSTEM
    // ═══════════════════════════════════════════════════════════════════════

    private function onSupportTicketCreated(NotificationTriggered $event): void
    {
        $userId = $event->data['user_id'] ?? 0;
        $userName = $event->data['user_name'] ?? '';
        $ticketSubject = $event->data['ticket_subject'] ?? '';

        if ($userId) {
            $this->deliver((int) $userId, 'support_ticket_created', [
                'user_name' => $userName,
                'ticket_subject' => $ticketSubject,
            ], [
                'sender' => 'system',
                'owner_type' => 'user',
                'data' => ['ticket_id' => $event->data['ticket_id'] ?? null],
            ]);
        }
    }

    private function onSupportTicketReplied(NotificationTriggered $event): void
    {
        $userId = $event->data['user_id'] ?? 0;
        $ticketSubject = $event->data['ticket_subject'] ?? '';
        $replyPreview = $event->data['reply_preview'] ?? '';

        if ($userId) {
            $this->deliver((int) $userId, 'support_ticket_replied', [
                'ticket_subject' => $ticketSubject,
                'reply_preview' => $replyPreview,
            ], [
                'sender' => 'Support Team',
                'owner_type' => 'user',
                'data' => ['ticket_id' => $event->data['ticket_id'] ?? null],
            ]);
        }
    }
}
