<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Follower;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\View;
use App\Models\Notification;
use App\Models\VerificationPlan;
use App\Models\ShopSubscription;
use App\Models\Report;
use App\Models\Support;
use App\Models\SupportReply;
use App\Models\Phone;
use App\Models\AppSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $shops = Shop::all();
        $products = Product::all();

        if ($users->isEmpty() || $shops->isEmpty()) {
            $this->command->warn('Run DatabaseSeeder first to create users and shops.');
            return;
        }

        $faker = \Faker\Factory::create();

        // ─── FOLLOWERS ─────────────────────────────────────────
        foreach ($shops as $shop) {
            $numFollowers = rand(3, 8);
            $followed = [];
            for ($i = 0; $i < $numFollowers; $i++) {
                $follower = $users->random();
                if ($follower->id === $shop->owner_id) continue;
                $key = $follower->id . '-' . $shop->id;
                if (in_array($key, $followed)) continue;
                $followed[] = $key;
                Follower::firstOrCreate([
                    'follower_id' => $follower->id,
                    'followed_shop_id' => $shop->id,
                ]);
            }
        }

        // ─── USER PHONES ────────────────────────────────────────
        foreach ($users as $user) {
            Phone::firstOrCreate(
                ['table' => 'users', 'owner_id' => $user->id],
                ['number' => '967' . rand(700000001, 799999999)]
            );
        }

        // ─── WISHLIST ───────────────────────────────────────────
        foreach ($users as $user) {
            $wishlistProducts = $products->random(rand(2, 5));
            foreach ($wishlistProducts as $product) {
                DB::table('wishlist')->insert([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ─── VIEWS (page views) ─────────────────────────────────
        $viewTypes = [
            ['viewable_type' => 'App\Models\Shop', 'items' => $shops],
            ['viewable_type' => 'App\Models\Product', 'items' => $products],
        ];
        foreach ($viewTypes as $vt) {
            foreach ($vt['items'] as $item) {
                $numViews = rand(1, 8);
                for ($v = 0; $v < $numViews; $v++) {
                    View::create([
                        'viewable_type' => $vt['viewable_type'],
                        'viewable_id' => $item->id,
                        'ip_address' => $faker->ipv4(),
                        'user_id' => rand(0, 1) ? $users->random()->id : null,
                        'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                    ]);
                }
            }
        }

        // ─── RATERS (ratings) ───────────────────────────────────
        foreach ($shops as $shop) {
            $numRaters = rand(2, 5);
            $rated = [];
            for ($r = 0; $r < $numRaters; $r++) {
                $rater = $users->random();
                if (in_array($rater->id, $rated)) continue;
                $rated[] = $rater->id;
                DB::table('raters')->insert([
                    'rater_id' => $rater->id,
                    'table' => 'shops',
                    'rated_id' => $shop->id,
                    'stars' => rand(3, 5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ─── NOTIFICATIONS ──────────────────────────────────────
        $notificationTypes = [
            ['title' => 'Welcome!', 'content' => 'Welcome to ShopMaps! Start exploring shops near you.'],
            ['title' => 'New Follower', 'content' => 'Someone started following your shop!'],
            ['title' => 'New Comment', 'content' => 'A user commented on your product.'],
            ['title' => 'Order Update', 'content' => 'Your order has been shipped and is on its way.'],
            ['title' => 'Special Offer', 'content' => 'Get 20% off on selected items this weekend!'],
            ['title' => 'Verification Approved', 'content' => 'Your shop has been verified. Congratulations!'],
            ['title' => 'Payment Received', 'content' => 'Payment of $49.99 has been received successfully.'],
            ['title' => 'Account Update', 'content' => 'Your profile information has been updated.'],
        ];
        foreach ($users as $user) {
            $numNotifs = rand(2, 6);
            for ($n = 0; $n < $numNotifs; $n++) {
                $notif = $notificationTypes[array_rand($notificationTypes)];
                Notification::create([
                    'owner_id' => $user->id,
                    'owner_type' => 'user',
                    'title' => $notif['title'],
                    'content' => $notif['content'],
                    'sender' => 'system',
                    'type' => 'system',
                    'is_read' => rand(0, 1),
                    'created_at' => now()->subDays(rand(0, 14))->subHours(rand(0, 23)),
                ]);
            }
        }

        // ─── SHOP SUBSCRIPTIONS ─────────────────────────────────
        $plans = VerificationPlan::all();
        if ($plans->isNotEmpty()) {
            foreach ($shops->random(min(8, $shops->count())) as $shop) {
                $plan = $plans->random();
                ShopSubscription::firstOrCreate(
                    ['shop_id' => $shop->id],
                    [
                        'plan_id' => $plan->id,
                        'status' => 'active',
                        'started_at' => now()->subDays(rand(10, 60)),
                        'expires_at' => now()->addDays(rand(10, 365)),
                    ]
                );
            }
        }

        // ─── SUPPORT TICKETS ────────────────────────────────────
        $ticketTitles = [
            'How to add multiple images?',
            'Payment not working',
            'Can I change my shop name?',
            'Report a bug in the app',
            'Verification document rejected',
            'Need help with analytics',
        ];
        foreach ($users->random(min(4, $users->count())) as $user) {
            $ticket = Support::create([
                'sender_id' => $user->id,
                'title' => $ticketTitles[array_rand($ticketTitles)],
                'content' => $faker->paragraph(),
                'status' => ['open', 'closed', 'in_progress'][array_rand(['open', 'closed', 'in_progress'])],
            ]);

            $numReplies = rand(1, 3);
            for ($r = 0; $r < $numReplies; $r++) {
                SupportReply::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $r === 0 ? $user->id : User::first()->id,
                    'content' => $faker->sentence(),
                    'is_admin' => $r > 0,
                ]);
            }
        }

        // ─── REPORTS ────────────────────────────────────────────
        $reportReasons = ['Spam', 'Inappropriate content', 'Fake shop', 'Misleading info'];
        foreach ($shops->random(min(3, $shops->count())) as $shop) {
            $report = Report::create([
                'reporter_id' => $users->random()->id,
                'reported_id' => $shop->id,
                'report_table' => 'shops',
                'type' => $reportReasons[array_rand($reportReasons)],
                'content' => $faker->sentence(),
                'status' => 'pending',
            ]);

            if (rand(0, 1)) {
                \DB::table('replies')->insert([
                    'report_id' => $report->id,
                    'content' => 'We are reviewing this report. Thank you for flagging.',
                    'status' => 'resolved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ─── APP SETTINGS ───────────────────────────────────────
        $settings = [
            ['key' => 'app_name', 'value' => 'ShopMaps', 'group' => 'general'],
            ['key' => 'app_description', 'value' => 'Discover shops near you', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'support@shopmaps.com', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '+967700000000', 'group' => 'contact'],
            ['key' => 'max_shop_images', 'value' => '10', 'group' => 'limits'],
            ['key' => 'max_product_images', 'value' => '8', 'group' => 'limits'],
            ['key' => 'commission_rate', 'value' => '5', 'group' => 'business'],
            ['key' => 'min_withdrawal', 'value' => '50', 'group' => 'business'],
            ['key' => 'currency', 'value' => 'USD', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'system'],
        ];
        foreach ($settings as $s) {
            AppSetting::updateOrCreate(
                ['key' => $s['key']],
                $s
            );
        }

        // ─── VERIFICATION FILES ─────────────────────────────────
        $approvedShops = $shops->where('verification_status', 'approved');
        foreach ($approvedShops->random(min(4, $approvedShops->count())) as $shop) {
            \DB::table('verification_files')->insert([
                'shop_id' => $shop->id,
                'url' => 'verification/' . $shop->id . '/license.pdf',
                'type' => 'pdf',
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("\nTest data seeded successfully!\n");
        $this->command->info('  Followers: ' . Follower::count());
        $this->command->info('  User Phones: ' . Phone::where('table', 'users')->count());
        $this->command->info('  Wishlist: ' . DB::table('wishlist')->count());
        $this->command->info('  Views: ' . View::count());
        $this->command->info('  Notifications: ' . Notification::count());
        $this->command->info('  Subscriptions: ' . ShopSubscription::count());
        $this->command->info('  Support Tickets: ' . Support::count());
        $this->command->info('  Reports: ' . Report::count());
        $this->command->info('  App Settings: ' . AppSetting::count());
    }
}
