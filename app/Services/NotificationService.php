<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationForm;
use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private const PLACEHOLDER_PATTERN = '/\{(\w+)\}/';

    // ─── TEMPLATE FETCHING ────────────────────────────────────────────────

    public function getTemplate(string $key): ?NotificationForm
    {
        return NotificationForm::findByKey($key);
    }

    public function allActiveTemplates(): Collection
    {
        return NotificationForm::allActive();
    }

    // ─── RENDERING ────────────────────────────────────────────────────────

    public function render(string $templateKey, array $variables = [], string $locale = 'en'): ?array
    {
        // For Arabic, try NotificationTemplate (bilingual) first
        if ($locale === 'ar') {
            $tpl = NotificationTemplate::active()->where('key', $templateKey)->first();
            if ($tpl) {
                $title = $this->safeReplace($tpl->title_ar ?: $tpl->title_en, $variables);
                $body = $this->safeReplace($tpl->content_ar ?: $tpl->content_en, $variables);
                $form = $this->getTemplate($templateKey);
                return [
                    'title' => $title,
                    'body' => $body,
                    'type' => $form?->key ?? $templateKey,
                    'action_type' => $form?->action_type ?? null,
                    'category' => $form?->category ?? 'system',
                    'icon' => $form?->icon ?? null,
                    'priority' => $form?->priority ?? 'normal',
                    'owner_type' => $form?->owner_type ?? 'user',
                ];
            }
        }

        $form = $this->getTemplate($templateKey);
        if (!$form) {
            Log::warning("Notification template not found: {$templateKey} (locale: {$locale})");
            return null;
        }

        if (!$form->is_active) {
            $title = $this->safeReplace($form->default_title ?? $form->title_template, $variables);
            $body = $this->safeReplace($form->default_body ?? $form->body_template, $variables);
        } else {
            $title = $this->safeReplace($form->title_template, $variables);
            $body = $this->safeReplace($form->body_template, $variables);
        }

        return [
            'title' => $title,
            'body' => $body,
            'type' => $form->key,
            'action_type' => $form->action_type,
            'category' => $form->category,
            'icon' => $form->icon,
            'priority' => $form->priority,
            'owner_type' => $form->owner_type,
        ];
    }

    public function safeRender(string $templateKey, array $variables = []): array
    {
        $result = $this->render($templateKey, $variables);
        if ($result === null) {
            return [
                'title' => 'System Notification',
                'body' => '',
                'type' => 'system',
                'action_type' => null,
                'category' => 'system',
                'icon' => null,
                'priority' => 'normal',
                'owner_type' => 'user',
            ];
        }
        return $result;
    }

    // ─── DELIVERY ─────────────────────────────────────────────────────────

    public function deliver(
        int $ownerId,
        string $templateKey,
        array $variables = [],
        array $options = []
    ): ?Notification {
        $rendered = $this->render($templateKey, $variables);
        if ($rendered === null) {
            Log::error("Cannot create notification: template '{$templateKey}' not found.");
            return null;
        }

        $sender = $options['sender'] ?? 'system';
        $ownerType = $options['owner_type'] ?? $rendered['owner_type'] ?? 'user';
        $extraData = $options['data'] ?? [];

        $data = $extraData;
        if (!empty($variables)) {
            $data['vars'] = $this->sanitizeVariables($variables);
        }
        $data['template_key'] = $templateKey;

        $arRendered = $this->render($templateKey, $variables, 'ar');
        $data['title_i18n'] = [
            'en' => $rendered['title'],
            'ar' => $arRendered ? ($arRendered['title'] ?? $rendered['title']) : $rendered['title'],
        ];
        $data['content_i18n'] = [
            'en' => $rendered['body'],
            'ar' => $arRendered ? ($arRendered['body'] ?? $rendered['body']) : $rendered['body'],
        ];

        $navigation = $this->resolveNavigation($templateKey, $data, $variables);
        $deepLink = $navigation['deep_link'] ?? null;
        if ($deepLink !== null) {
            $data['deep_link'] = $deepLink;
        }

        $dedupKey = "notif_dedup:{$ownerId}:{$templateKey}";
        if (Cache::has($dedupKey)) {
            Log::debug("Dedup: notification '{$templateKey}' for {$ownerType}:{$ownerId} skipped (already sent within window)");
            return null;
        }
        Cache::put($dedupKey, true, 5);

        try {
            return Notification::create([
                'owner_id' => $ownerId,
                'owner_type' => $ownerType,
                'title' => $rendered['title'],
                'content' => $rendered['body'],
                'sender' => $sender,
                'type' => $rendered['type'],
                'action_type' => $rendered['action_type'],
                'data' => $data,
                'is_read' => false,
                'target_type' => $navigation['target_type'] ?? null,
                'target_id' => $navigation['target_id'] ?? null,
                'navigation_type' => $navigation['navigation_type'] ?? null,
                'metadata' => $navigation['metadata'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to create notification: {$e->getMessage()}");
            return null;
        }
    }

    public function deliverMany(
        array $recipients,
        string $templateKey,
        array $variables = [],
        array $options = []
    ): Collection {
        $notifications = new Collection();
        foreach ($recipients as $recipient) {
            $ownerId = $recipient['owner_id'] ?? $recipient;
            $ownerType = $recipient['owner_type'] ?? $options['owner_type'] ?? 'user';

            $notif = $this->deliver($ownerId, $templateKey, $variables, array_merge($options, [
                'owner_type' => $ownerType,
            ]));
            if ($notif !== null) {
                $notifications->push($notif);
            }
        }
        return $notifications;
    }

    // ─── NAVIGATION RESOLUTION ────────────────────────────────────────────

    public function resolveNavigation(string $templateKey, array $data, array $variables): array
    {
        $result = [
            'target_type' => null,
            'target_id' => null,
            'navigation_type' => null,
            'metadata' => null,
            'deep_link' => null,
        ];

        $shopId = $data['shop_id'] ?? $variables['shop_id'] ?? null;
        $productId = $data['product_id'] ?? $variables['product_id'] ?? null;
        $commentId = $data['comment_id'] ?? $variables['comment_id'] ?? null;
        $userId = $data['user_id'] ?? $variables['user_id'] ?? null;

        $result = match ($templateKey) {
            // ─── Followers ────────────────────────────────────────────────
            'new_follower' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'analytics_followers',
                'metadata' => ['section' => 'followers'],
                'deep_link' => "/shop-management/analytics/{$shopId}?focus=followers",
            ] : $result,

            // ─── Shop Likes ───────────────────────────────────────────────
            'shop_liked' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'analytics_likes',
                'metadata' => ['section' => 'likes'],
                'deep_link' => "/shop-management/analytics/{$shopId}?focus=likes",
            ] : $result,

            // ─── Favorites ────────────────────────────────────────────────
            'new_favorite' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'analytics_likes',
                'metadata' => ['section' => 'likes'],
                'deep_link' => "/shop-management/analytics/{$shopId}?focus=likes",
            ] : $result,

            // ─── New Comment ──────────────────────────────────────────────
            'new_comment' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'comments_focus',
                'metadata' => ['section' => 'comments', 'comment_id' => $commentId],
                'deep_link' => $commentId
                    ? "/shop-management/comments/{$shopId}?focusCommentId={$commentId}"
                    : "/shop-management/comments/{$shopId}",
            ] : $result,

            // ─── Reply Received ───────────────────────────────────────────
            'reply_received' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'comments_focus',
                'metadata' => ['section' => 'comments', 'comment_id' => $commentId, 'focus_reply' => true],
                'deep_link' => $commentId
                    ? "/shop-management/comments/{$shopId}?focusCommentId={$commentId}"
                    : "/shop-management/comments/{$shopId}",
            ] : $result,

            // ─── Rating / Review ──────────────────────────────────────────
            'new_rating', 'rating_milestone' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'analytics_ratings',
                'metadata' => ['section' => 'ratings'],
                'deep_link' => "/shop-management/analytics/{$shopId}?focus=ratings",
            ] : $result,

            // ─── Growth / Engagement / Views ──────────────────────────────
            'engagement_milestone', 'growth_milestone' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'analytics_overview',
                'metadata' => ['section' => 'overview'],
                'deep_link' => "/shop-management/analytics/{$shopId}",
            ] : $result,

            'traffic_increase', 'view_increase', 'view_milestone' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'analytics_views',
                'metadata' => ['section' => 'views'],
                'deep_link' => "/shop-management/analytics/{$shopId}?focus=views",
            ] : $result,

            // ─── Reports ──────────────────────────────────────────────────
            'comment_reported' => $shopId ? [
                'target_type' => 'comment', 'target_id' => $commentId,
                'navigation_type' => 'comment_reported',
                'metadata' => ['shop_id' => $shopId, 'comment_id' => $commentId],
                'deep_link' => $commentId
                    ? "/shop-management/comments/{$shopId}?focusCommentId={$commentId}&report=true"
                    : "/shop-management/comments/{$shopId}",
            ] : $result,

            'shop_reported' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'shop_reported',
                'metadata' => ['shop_id' => $shopId],
                'deep_link' => "/shop-management?focus=reports",
            ] : $result,

            'product_reported' => ($shopId && $productId) ? [
                'target_type' => 'product', 'target_id' => $productId,
                'navigation_type' => 'product_reported',
                'metadata' => ['shop_id' => $shopId, 'product_id' => $productId],
                'deep_link' => "/shop-management/products/stats/{$productId}?report=true",
            ] : $result,

            'report_resolved' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'report_resolved',
                'metadata' => ['shop_id' => $shopId],
                'deep_link' => "/shop-management",
            ] : $result,

            // ─── Products ─────────────────────────────────────────────────
            'product_liked', 'product_favorited' => $productId ? [
                'target_type' => 'product', 'target_id' => $productId,
                'navigation_type' => 'product_engagement',
                'metadata' => ['product_id' => $productId, 'shop_id' => $shopId],
                'deep_link' => $shopId
                    ? "/shop-management/products/stats/{$productId}?shopId={$shopId}"
                    : "/product/{$productId}",
            ] : $result,

            'product_comment' => ($shopId && $productId) ? [
                'target_type' => 'product', 'target_id' => $productId,
                'navigation_type' => 'product_comments',
                'metadata' => ['product_id' => $productId, 'shop_id' => $shopId, 'comment_id' => $commentId],
                'deep_link' => "/shop-management/products/stats/{$productId}?shopId={$shopId}",
            ] : $result,

            'product_performance' => ($shopId && $productId) ? [
                'target_type' => 'product', 'target_id' => $productId,
                'navigation_type' => 'product_performance',
                'metadata' => ['product_id' => $productId, 'shop_id' => $shopId],
                'deep_link' => "/shop-management/products/stats/{$productId}?shopId={$shopId}",
            ] : $result,

            'new_product_added' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'product_list',
                'metadata' => ['shop_id' => $shopId],
                'deep_link' => "/shop-management/products/{$shopId}",
            ] : $result,

            'product_out_of_stock' => $shopId ? [
                'target_type' => 'product', 'target_id' => $productId,
                'navigation_type' => 'product_out_of_stock',
                'metadata' => ['shop_id' => $shopId, 'product_id' => $productId],
                'deep_link' => $productId
                    ? "/shop-management/products/stats/{$productId}?shopId={$shopId}"
                    : "/shop-management/products/{$shopId}",
            ] : $result,

            'product_discount_added' => ($shopId && $productId) ? [
                'target_type' => 'product', 'target_id' => $productId,
                'navigation_type' => 'product_discount',
                'metadata' => ['shop_id' => $shopId, 'product_id' => $productId],
                'deep_link' => "/shop-management/products/stats/{$productId}?shopId={$shopId}",
            ] : $result,

            'product_updated', 'product_deleted' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'product_list',
                'metadata' => ['shop_id' => $shopId],
                'deep_link' => "/shop-management/products/{$shopId}",
            ] : $result,

            // ─── Verification ─────────────────────────────────────────────
            'shop_verified' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'verification_status',
                'metadata' => ['status' => 'approved'],
                'deep_link' => "/shop-management/verification/{$shopId}",
            ] : $result,

            'shop_rejected' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'verification_status',
                'metadata' => ['status' => 'rejected'],
                'deep_link' => "/shop-management/verification/{$shopId}",
            ] : $result,

            // ─── Shop Status ──────────────────────────────────────────────
            'shop_suspended', 'shop_status_changed' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'shop_status',
                'metadata' => ['shop_id' => $shopId],
                'deep_link' => "/shop-management",
            ] : $result,

            'shop_registered' => [
                'target_type' => null, 'target_id' => null,
                'navigation_type' => 'dashboard',
                'metadata' => null,
                'deep_link' => "/shop-management",
            ],

            // ─── Profile & Account ────────────────────────────────────────
            'profile_updated' => [
                'target_type' => null, 'target_id' => null,
                'navigation_type' => 'profile',
                'metadata' => null,
                'deep_link' => "/edit-profile",
            ],

            'account_password_changed' => [
                'target_type' => null, 'target_id' => null,
                'navigation_type' => 'settings',
                'metadata' => null,
                'deep_link' => "/settings",
            ],

            'login_from_new_device' => [
                'target_type' => null, 'target_id' => null,
                'navigation_type' => 'settings_security',
                'metadata' => null,
                'deep_link' => "/settings/security",
            ],

            'account_suspended' => [
                'target_type' => null, 'target_id' => null,
                'navigation_type' => 'support',
                'metadata' => null,
                'deep_link' => "/support",
            ],

            // ─── Support ──────────────────────────────────────────────────
            'support_ticket_created', 'support_ticket_replied', 'support_ticket_update' => [
                'target_type' => null, 'target_id' => null,
                'navigation_type' => 'support',
                'metadata' => null,
                'deep_link' => "/shop-management/support",
            ],

            // ─── Welcome ──────────────────────────────────────────────────
            'welcome_user' => [
                'target_type' => null, 'target_id' => null,
                'navigation_type' => 'map',
                'metadata' => null,
                'deep_link' => "/map",
            ],

            // ─── System ────────────────────────────────────────────────────
            'system_announcement' => [
                'target_type' => null, 'target_id' => null,
                'navigation_type' => 'system_announcement',
                'metadata' => null,
                'deep_link' => null,
            ],

            'your_report_received', 'report_status_updated' => [
                'target_type' => null, 'target_id' => null,
                'navigation_type' => 'system_info',
                'metadata' => null,
                'deep_link' => null,
            ],

            // ─── Comment Like ─────────────────────────────────────────────
            'comment_liked' => $shopId ? [
                'target_type' => 'shop', 'target_id' => $shopId,
                'navigation_type' => 'comments_focus',
                'metadata' => ['comment_id' => $commentId],
                'deep_link' => $commentId
                    ? "/shop-management/comments/{$shopId}?focusCommentId={$commentId}"
                    : "/shop-management/comments/{$shopId}",
            ] : $result,

            default => $result,
        };

        return $result;
    }

    /**
     * @deprecated Use resolveNavigation() instead.
     */
    public function buildDeepLink(string $templateKey, array $data, array $variables): ?string
    {
        $nav = $this->resolveNavigation($templateKey, $data, $variables);
        return $nav['deep_link'] ?? null;
    }

    // ─── PLACEHOLDER VALIDATION ──────────────────────────────────────────

    public function getPlaceholders(string $template): array
    {
        preg_match_all(self::PLACEHOLDER_PATTERN, $template, $matches);
        return array_unique($matches[1] ?? []);
    }

    public function validatePlaceholders(string $templateKey, array $variables): array
    {
        $form = $this->getTemplate($templateKey);
        if (!$form) return ['_template_not_found'];

        $templates = [$form->title_template, $form->body_template];
        if (!$form->is_active) {
            $templates = [
                $form->default_title ?? $form->title_template,
                $form->default_body ?? $form->body_template,
            ];
        }

        $missing = [];
        foreach ($templates as $template) {
            preg_match_all(self::PLACEHOLDER_PATTERN, $template, $matches);
            $used = array_unique($matches[1] ?? []);
            foreach ($used as $placeholder) {
                if (!array_key_exists($placeholder, $variables)) {
                    $missing[$placeholder] = $placeholder;
                }
            }
        }
        return array_values($missing);
    }

    // ─── SANITIZATION ────────────────────────────────────────────────────

    public function sanitize(string $value): string
    {
        $value = strip_tags($value);
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($value);
    }

    public function sanitizeVariables(array $variables): array
    {
        $safe = [];
        foreach ($variables as $key => $value) {
            $safe[$key] = $this->sanitize((string) $value);
        }
        return $safe;
    }

    public function stripReporterIdentity(array $data): array
    {
        unset($data['reporter_id'], $data['reporter_name'], $data['reporter_email']);
        if (isset($data['vars'])) {
            unset($data['vars']['reporter_name'], $data['vars']['reporter_email']);
        }
        return $data;
    }

    // ─── SAFE REPLACE ────────────────────────────────────────────────────

    public function safeReplace(string $template, array $variables): string
    {
        $replacements = [];
        foreach ($variables as $key => $value) {
            $replacements['{' . $key . '}'] = $this->sanitize((string) $value);
        }
        $result = strtr($template, $replacements);
        $result = preg_replace(self::PLACEHOLDER_PATTERN, '', $result);
        return $result;
    }

    public static function getAllowedPlaceholders(): array
    {
        return [
            'user_name', 'shop_name', 'comment_snippet', 'product_name',
            'report_reason', 'reporter_name', 'follower_name', 'count',
            'date', 'time', 'target_name', 'action', 'status', 'reason',
            'moderation_note', 'ticket_subject', 'login_location',
            'device_name', 'discount_percent', 'product_id',
        ];
    }
}
