<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $statements = [
            "ALTER TABLE `comments` ADD INDEX `comments_lookup`(`commentable_type`, `commented_id`, `status`)",
            "ALTER TABLE `favorites` ADD INDEX `favorites_user_lookup`(`user_id`, `favorable_type`, `favorable_id`)",
            "ALTER TABLE `likes` ADD INDEX `likes_user_lookup`(`user_id`, `likeable_type`, `likeable_id`)",
            "ALTER TABLE `images` ADD INDEX `images_owner_lookup`(`table`, `owner_id`, `is_primary`)",
            "ALTER TABLE `products` ADD INDEX `products_shop_lookup`(`shop_id`, `status`)",
            "ALTER TABLE `products` ADD INDEX `products_category_lookup`(`type_id`)",
            "ALTER TABLE `notifications` ADD INDEX `notifications_owner_lookup`(`owner_id`, `created_at`)",
            "ALTER TABLE `followers` ADD INDEX `followers_pair_lookup`(`follower_id`, `followed_shop_id`)",
            "ALTER TABLE `reports` ADD INDEX `reports_reporter_lookup`(`reporter_id`, `status`)",
            "ALTER TABLE `reports` ADD INDEX `reports_target_lookup`(`report_table`, `reported_id`)",
            "ALTER TABLE `shops` ADD INDEX `shops_location`(`lat`, `lng`)",
            "ALTER TABLE `shops` ADD INDEX `shops_status_owner`(`status`, `owner_id`)",
            "ALTER TABLE `shops` ADD INDEX `shops_business_type`(`business_type`)",
            "ALTER TABLE `raters` ADD INDEX `raters_analytics_lookup`(`table`, `rated_id`, `created_at`)",
            "ALTER TABLE `sessions` ADD INDEX `sessions_cleanup`(`last_activity`)",
        ];

        foreach ($statements as $sql) {
            try {
                DB::statement($sql);
            } catch (\Exception $e) {
                // Index may already exist — skip
            }
        }
    }

    public function down(): void
    {
        $indexes = [
            'comments' => 'comments_lookup',
            'favorites' => 'favorites_user_lookup',
            'likes' => 'likes_user_lookup',
            'images' => 'images_owner_lookup',
            'products' => ['products_shop_lookup', 'products_category_lookup'],
            'notifications' => 'notifications_owner_lookup',
            'followers' => 'followers_pair_lookup',
            'reports' => ['reports_reporter_lookup', 'reports_target_lookup'],
            'shops' => ['shops_location', 'shops_status_owner', 'shops_business_type'],
            'raters' => 'raters_analytics_lookup',
            'sessions' => 'sessions_cleanup',
        ];

        foreach ($indexes as $table => $names) {
            foreach ((array) $names as $name) {
                try {
                    Schema::table($table, fn(Blueprint $t) => $t->dropIndex($name));
                } catch (\Exception $e) {
                    // May not exist
                }
            }
        }
    }
};
