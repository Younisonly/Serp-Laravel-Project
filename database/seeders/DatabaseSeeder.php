<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Country;
use App\Models\cities;
use App\Models\ProductCategory;
use App\Models\ImageSetting;
use App\Models\Image;
use App\Models\Phone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ========================
        // 1. COUNTRIES
        // ========================
        $countries = [
            ['name' => 'Yemen'],
            ['name' => 'Saudi Arabia'],
            ['name' => 'United Arab Emirates'],
        ];
        foreach ($countries as $c) {
            Country::firstOrCreate(['name' => $c['name']]);
        }

        // ========================
        // 2. CITIES
        // ========================
        $citiesData = [
            ['name' => 'Sanaa', 'country_id' => 1],
            ['name' => 'Aden', 'country_id' => 1],
            ['name' => 'Taiz', 'country_id' => 1],
            ['name' => 'Hodeidah', 'country_id' => 1],
            ['name' => 'Ibb', 'country_id' => 1],
            ['name' => 'Riyadh', 'country_id' => 2],
            ['name' => 'Jeddah', 'country_id' => 2],
            ['name' => 'Mecca', 'country_id' => 2],
            ['name' => 'Dubai', 'country_id' => 3],
            ['name' => 'Abu Dhabi', 'country_id' => 3],
        ];
        foreach ($citiesData as $ct) {
            cities::firstOrCreate(['name' => $ct['name'], 'country_id' => $ct['country_id']]);
        }

        // ========================
        // 3. PRODUCT CATEGORIES
        // ========================
        $productCategories = [
            ['name' => 'Food & Beverages', 'name_en' => 'Food & Beverages', 'name_ar' => 'طعام ومشروبات', 'icon' => 'restaurant', 'sort_order' => 1],
            ['name' => 'Electronics', 'name_en' => 'Electronics', 'name_ar' => 'إلكترونيات', 'icon' => 'devices', 'sort_order' => 2],
            ['name' => 'Clothing', 'name_en' => 'Clothing', 'name_ar' => 'ملابس', 'icon' => 'checkroom', 'sort_order' => 3],
            ['name' => 'Health & Beauty', 'name_en' => 'Health & Beauty', 'name_ar' => 'صحة وجمال', 'icon' => 'spa', 'sort_order' => 4],
            ['name' => 'Home & Garden', 'name_en' => 'Home & Garden', 'name_ar' => 'منزل وحديقة', 'icon' => 'home', 'sort_order' => 5],
            ['name' => 'Books & Stationery', 'name_en' => 'Books & Stationery', 'name_ar' => 'كتب وقرطاسية', 'icon' => 'menu_book', 'sort_order' => 6],
            ['name' => 'Sports & Outdoors', 'name_en' => 'Sports & Outdoors', 'name_ar' => 'رياضة وخارجيات', 'icon' => 'fitness_center', 'sort_order' => 7],
            ['name' => 'Toys & Games', 'name_en' => 'Toys & Games', 'name_ar' => 'ألعاب', 'icon' => 'toys', 'sort_order' => 8],
            ['name' => 'Automotive', 'name_en' => 'Automotive', 'name_ar' => 'سيارات', 'icon' => 'directions_car', 'sort_order' => 9],
            ['name' => 'Jewelry & Accessories', 'name_en' => 'Jewelry & Accessories', 'name_ar' => 'مجوهرات وإكسسوارات', 'icon' => 'diamond', 'sort_order' => 10],
        ];
        foreach ($productCategories as $pc) {
            ProductCategory::firstOrCreate(
                ['name' => $pc['name']],
                [
                    'name_en' => $pc['name_en'],
                    'name_ar' => $pc['name_ar'],
                    'icon' => $pc['icon'],
                    'sort_order' => $pc['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // ========================
        // 5. IMAGE SETTINGS
        // ========================
        $imageSettings = [
            ['type' => 'avatar', 'width' => 400, 'height' => 400, 'max_size_mb' => 2, 'watermark' => 'watermarks/download.png', 'watermark_opacity' => 50, 'quality' => 85],
            ['type' => 'shop_logo', 'width' => 600, 'height' => 600, 'max_size_mb' => 3, 'watermark' => null, 'quality' => 85],
            ['type' => 'product', 'width' => 800, 'height' => 800, 'max_size_mb' => 3, 'watermark' => 'watermarks/download.png', 'watermark_opacity' => 60, 'quality' => 80],
            ['type' => 'banner', 'width' => 1920, 'height' => 600, 'max_size_mb' => 5, 'watermark' => 'watermarks/download.png', 'watermark_opacity' => 40, 'quality' => 80],
        ];
        foreach ($imageSettings as $is) {
            ImageSetting::updateOrCreate(
                ['type' => $is['type']],
                $is
            );
        }

        // ========================
        // 6. USERS
        // ========================
        $users = [
            [
                'name' => 'Ahmed Al-Yamani',
                'email' => 'ahmed@example.com',
                'password' => Hash::make('password'),
                'phone' => '777000001',
                'status' => 'active',
                'image' => null,
                'type' => 'user',
                'country' => 'Yemen',
                'city' => 'Sanaa',
                'gender' => 'male',
            ],
            [
                'name' => 'Fatima Ali',
                'email' => 'fatima@example.com',
                'password' => Hash::make('password'),
                'phone' => '777000002',
                'status' => 'active',
                'image' => null,
                'type' => 'user',
                'country' => 'Yemen',
                'city' => 'Aden',
                'gender' => 'female',
            ],
            [
                'name' => 'Mohammed Al-Saud',
                'email' => 'mohammed@example.com',
                'password' => Hash::make('password'),
                'phone' => '777000003',
                'status' => 'active',
                'image' => null,
                'type' => 'servicePro',
                'country' => 'Saudi Arabia',
                'city' => 'Riyadh',
                'gender' => 'male',
            ],
            [
                'name' => 'Layla Hassan',
                'email' => 'layla@example.com',
                'password' => Hash::make('password'),
                'phone' => '777000004',
                'status' => 'active',
                'image' => null,
                'type' => 'servicePro',
                'country' => 'UAE',
                'city' => 'Dubai',
                'gender' => 'female',
            ],
            [
                'name' => 'Omar Abdulaziz',
                'email' => 'omar@example.com',
                'password' => Hash::make('password'),
                'phone' => '777000005',
                'status' => 'active',
                'image' => null,
                'type' => 'user',
                'country' => 'Yemen',
                'city' => 'Taiz',
                'gender' => 'male',
            ],
            [
                'name' => 'Noor Al-Tamimi',
                'email' => 'noor@example.com',
                'password' => Hash::make('password'),
                'phone' => '777000006',
                'status' => 'active',
                'image' => null,
                'type' => 'user',
                'country' => 'Saudi Arabia',
                'city' => 'Jeddah',
                'gender' => 'female',
            ],
        ];
        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                $u
            );
        }

        // ========================
        // 7. SHOPS
        // ========================
        $shopNames = [
            ['name' => 'Sanaa Grill House', 'type' => 1, 'icon' => 'restaurant'],
            ['name' => 'Mocha Cafe & Roastery', 'type' => 2, 'icon' => 'cafe'],
            ['name' => 'Yemen Fresh Supermarket', 'type' => 3, 'icon' => 'grocery'],
            ['name' => 'Tech World Sanaa', 'type' => 4, 'icon' => 'electronics'],
            ['name' => 'Al-Mutawakkil Fashion', 'type' => 5, 'icon' => 'fashion'],
            ['name' => 'Sanaa Pharmacy', 'type' => 6, 'icon' => 'pharmacy'],
            ['name' => 'Queen Beauty Salon', 'type' => 7, 'icon' => 'beauty'],
            ['name' => 'Honey Bakery & Sweets', 'type' => 8, 'icon' => 'bakery'],
            ['name' => 'Arabian Furniture House', 'type' => 9, 'icon' => 'furniture'],
            ['name' => 'Knowledge Bookstore', 'type' => 10, 'icon' => 'book'],
            ['name' => 'Rose Garden Florist', 'type' => 11, 'icon' => 'flowers'],
            ['name' => 'Iron Horse Gym', 'type' => 12, 'icon' => 'gym'],
            ['name' => 'Al-Fara Auto Repair', 'type' => 13, 'icon' => 'car'],
            ['name' => 'Golden Diamond Jewelry', 'type' => 14, 'icon' => 'jewelry'],
            ['name' => 'Bab Al-Yemen Restaurant', 'type' => 1, 'icon' => 'restaurant'],
            ['name' => 'Qahwa Arabic Cafe', 'type' => 2, 'icon' => 'cafe'],
            ['name' => 'Modern Electronics', 'type' => 4, 'icon' => 'electronics'],
            ['name' => 'Silk Road Tailoring', 'type' => 5, 'icon' => 'fashion'],
        ];

        $descriptions = [
            'We offer the finest selection with excellent customer service. Visit us today!',
            'Quality products at affordable prices. Your satisfaction is our priority.',
            'Serving our community for over 10 years. We provide the best in class services.',
            'Your trusted partner for quality goods. Wide range of products available.',
            'Experience the best shopping experience with our carefully curated collection.',
        ];

        foreach ($shopNames as $i => $s) {
            $shop = Shop::create([
                'name' => $s['name'],
                'description' => $descriptions[array_rand($descriptions)],
                'logo' => 'shops/' . $s['icon'] . '_logo.png',
                'iconname' => $s['icon'],
                'open_at' => '08:00:00',
                'close_at' => '22:00:00',
                'status' => rand(0, 1) ? 'open' : 'closed',
                'owner_id' => rand(1, 6),
                'business_type' => $s['icon'],
                'address' => match ($s['type']) {
                    1, 2 => 'Street ' . ($i + 1) . ', Sanaa',
                    default => 'Area ' . chr(65 + $i) . ', Main Road',
                },
                'lat' => 15.35 + (($i % 10) * 0.015) + (rand(-10, 10) * 0.001),
                'lng' => 44.20 + (($i % 8) * 0.012) + (rand(-10, 10) * 0.001),
                'followers_number' => rand(20, 500),
                'raters_number' => rand(5, 100),
                'stars_rate' => round(3 + (rand(0, 20) / 10), 1),
                'views' => rand(100, 5000),
                'joined_at' => now()->subDays(rand(30, 600)),
                'days_open' => 'Sat-Thu',
                'points' => rand(10, 500),
                'contact_email' => strtolower(str_replace(' ', '', $s['name'])) . '@example.com',
                'website' => 'https://' . strtolower(str_replace(' ', '', $s['name'])) . '.com',
                'instagram' => strtolower(str_replace(' ', '_', $s['name'])),
                'whatsapp' => '9677' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            ]);

            // Add phone for shop
            Phone::create([
                'table' => 'shops',
                'owner_id' => $shop->id,
                'number' => '967' . rand(700000001, 799999999),
            ]);

            // Add placeholder image record for shop logo
            Image::create([
                'table' => 'shops',
                'owner_id' => $shop->id,
                'url' => 'shops/default_logo.png',
                'is_primary' => true,
            ]);

            // ========================
            // 8. PRODUCTS for each shop
            // ========================
            $productNames = [
                'Restaurant' => ['Mandi Rice', 'Grilled Chicken', 'Fattah', 'Shawarma Plate', 'Fresh Juice', 'Kabsa', 'Soup of the Day', 'Mixed Grill', 'Hummus & Mutabbal', 'Baklava'],
                'Cafe' => ['Arabic Coffee', 'Cappuccino', 'Mocha Latte', 'Green Tea', 'Fresh Croissant', 'Cheese Cake', 'Chocolate Muffin', 'Iced Coffee', 'Turkish Coffee', 'Fruit Smoothie'],
                'Supermarket' => ['Rice 5kg', 'Cooking Oil', 'Sugar 2kg', 'Flour 1kg', 'Canned Beans', 'Pasta Pack', 'Tomato Sauce', 'Mineral Water', 'Milk 1L', 'Bread Pack'],
                'Electronics' => ['Phone Charger', 'USB Cable', 'Bluetooth Speaker', 'HDMI Cable', 'Power Bank', 'Headphones', 'Mouse Pad', 'Keyboard', 'Webcam', 'Laptop Stand'],
                'Clothing' => ['Cotton T-Shirt', 'Denim Jacket', 'Summer Dress', 'Polo Shirt', 'Leather Belt', 'Wool Scarf', 'Casual Sneakers', 'Handbag', 'Sunglasses', 'Watch'],
                'Pharmacy' => ['Vitamin C 1000mg', 'Pain Reliever', 'First Aid Kit', 'Hand Sanitizer', 'Face Mask Pack', 'Omega-3', 'Cough Syrup', 'Antiseptic Cream', 'Eye Drops', 'Multivitamin'],
                'Beauty' => ['Hair Oil Treatment', 'Facial Cream', 'Nail Polish Set', 'Body Lotion', 'Perfume', 'Lip Balm', 'Shampoo', 'Conditioner', 'Face Mask', 'Sunscreen'],
                'Bakery' => ['French Baguette', 'Chocolate Croissant', 'Whole Wheat Bread', 'Cheese Pastry', 'Cinnamon Roll', 'Brioche', 'Sourdough', 'Biscotti', 'Danish Pastry', 'Breadsticks'],
                'Furniture' => ['Wooden Chair', 'Coffee Table', 'Bookshelf', 'Desk Lamp', 'Floor Rug', 'Cushion Set', 'Wardrobe', 'Bed Frame', 'Nightstand', 'Wall Shelf'],
                'Bookstore' => ['Arabic Novel', 'English Dictionary', 'Children Book', 'Poetry Collection', 'History Book', 'Science Textbook', 'Art Magazine', 'Quran', 'Cookbook', 'Travel Guide'],
                'Flower Shop' => ['Rose Bouquet', 'Tulip Bunch', 'Mixed Flowers', 'Lily Arrangement', 'Orchid Plant', 'Sunflower', 'Dried Flowers', 'Flower Basket', 'Wedding Bouquet', 'Potted Plant'],
                'Gym' => ['Protein Powder', 'Weight Loss Plan', 'Yoga Session', 'Personal Training', 'Gym Membership', 'Boxing Class', 'Swimming Session', 'Nutrition Plan', 'Group Class', 'Sauna Access'],
                'Car Repair' => ['Oil Change', 'Brake Inspection', 'Tire Rotation', 'Battery Check', 'Engine Tune-up', 'AC Service', 'Transmission Fix', 'Alignment', 'Car Wash', 'Diagnostic Scan'],
                'Jewelry' => ['Gold Ring', 'Silver Necklace', 'Diamond Earrings', 'Pearl Bracelet', 'Gold Chain', 'Gemstone Ring', 'Silver Anklet', 'Gold Earrings', 'Platinum Band', 'Custom Pendant'],
            ];

            $categoryMap = [
                1 => 'Food & Beverages', 2 => 'Food & Beverages', 3 => 'Food & Beverages',
                4 => 'Electronics', 5 => 'Clothing', 6 => 'Health & Beauty',
                7 => 'Health & Beauty', 8 => 'Food & Beverages', 9 => 'Home & Garden',
                10 => 'Books & Stationery', 11 => 'Home & Garden', 12 => 'Sports & Outdoors',
                13 => 'Automotive', 14 => 'Jewelry & Accessories',
            ];

            $typeName = $shopCategories[$s['type'] - 1]['name'];
            $productList = $productNames[$typeName] ?? $productNames['Supermarket'];
            $categoryName = $categoryMap[$s['type']] ?? 'General';
            $productCat = ProductCategory::where('name', $categoryName)->first();

            foreach ($productList as $j => $pName) {
                $price = match ($s['type']) {
                    4 => rand(5, 200),
                    5 => rand(10, 300),
                    9 => rand(50, 1000),
                    14 => rand(50, 2000),
                    12 => rand(20, 500),
                    13 => rand(30, 300),
                    default => rand(1, 50),
                };

                $hasDiscount = rand(0, 1);
                $discount = $hasDiscount ? rand(5, 40) : 0;
                $hasWarranty = in_array($s['type'], [4, 9, 13]);
                $deliveryOptions = [
                    'Free delivery within Sanaa. Delivery within 24 hours.',
                    'Standard delivery 2-5 business days. Express delivery available.',
                    'Free shipping on orders over $25. Same-day delivery in some areas.',
                    'Delivery within 3-7 business days. Track your order online.',
                    'We deliver to your doorstep. Cash on delivery available.',
                ];
                $notesOptions = [
                    'Available in multiple colors — contact shop for options.',
                    'Limited stock remaining. Order now to avoid disappointment.',
                    'This item comes with a gift box included. Perfect for gifting.',
                    'Handcrafted with care. Each piece may vary slightly in appearance.',
                    'Imported directly from the manufacturer. Authentic product guaranteed.',
                    'Bulk purchase discounts available. Contact shop for wholesale pricing.',
                    'Product images are for illustration only. Actual product may vary.',
                    'Eco-friendly materials. 100% recyclable packaging.',
                ];

                $product = Product::create([
                    'name' => $pName,
                    'description' => 'High quality ' . $pName . ' available now at ' . $shop->name . '. Fresh and authentic.',
                    'price' => $price,
                    'shop_id' => $shop->id,
                    'discount' => $hasDiscount ? $discount : 0,
                    'discount_start' => $hasDiscount ? now()->subDays(rand(1, 10)) : null,
                    'discount_end' => $hasDiscount ? now()->addDays(rand(10, 60)) : null,
                    'type_id' => $productCat?->id ?? 1,
                    'barcode' => '627' . str_pad((string) $shop->id, 4, '0', STR_PAD_LEFT) . str_pad((string) ($j + 1), 3, '0', STR_PAD_LEFT),
                    'views' => rand(10, 500),
                    'stars_rate' => round(3 + (rand(0, 20) / 10), 1),
                    'raters_number' => rand(1, 30),
                    'warranty' => $hasWarranty ? 1 : 0,
                    'warranty_start' => $hasWarranty ? now()->subDays(rand(1, 30)) : null,
                    'warranty_end' => $hasWarranty ? now()->addMonths(rand(1, 12)) : null,
                    'warranty_description' => $hasWarranty ? 'Standard manufacturer warranty covers defects in materials and workmanship.' : null,
                    'model_number' => strtoupper(substr($pName, 0, 3)) . '-' . $shop->id . '-' . str_pad((string) ($j + 1), 3, '0', STR_PAD_LEFT),
                    'delivery_info' => $deliveryOptions[array_rand($deliveryOptions)],
                    'notes' => $notesOptions[array_rand($notesOptions)],
                ]);

                // Add product image record
                $categoryImages = [
                    'Food & Beverages' => 'products/food',
                    'Electronics' => 'products/electronics',
                    'Clothing' => 'products/clothing',
                    'Health & Beauty' => 'products/beauty',
                    'Home & Garden' => 'products/home',
                    'Books & Stationery' => 'products/books',
                    'Sports & Outdoors' => 'products/sports',
                    'Automotive' => 'products/auto',
                    'Jewelry & Accessories' => 'products/jewelry',
                ];

                Image::create([
                    'table' => 'products',
                    'owner_id' => $product->id,
                    'url' => ($categoryImages[$categoryName] ?? 'products/general') . '/' . ($j + 1) . '.jpg',
                    'is_primary' => true,
                    'status' => 'active',
                ]);
            }
        }

        // ========================
        // 9. COMMENTS
        // ========================
        $commentTexts = [
            'Great shop! Highly recommended.',
            'Good products, fair prices.',
            'Fast delivery and excellent service.',
            'The quality exceeded my expectations.',
            'I have been a customer for years. Never disappointed.',
            'Average experience, could be better.',
            'Amazing selection of products!',
            'Very helpful staff. Will visit again.',
            'Good quality but a bit expensive.',
            'Best shop in the area!',
            'Prompt service and friendly staff.',
            'The packaging was perfect. Thank you!',
        ];

        $shops = Shop::all();
        foreach ($shops as $shop) {
            $numComments = rand(1, 4);
            for ($c = 0; $c < $numComments; $c++) {
                $commenterId = rand(1, 6);
                $comment = Comment::create([
                    'commenter_id' => $commenterId,
                    'commentable_type' => 'App\Models\Shop',
                    'commented_id' => $shop->id,
                    'content' => $commentTexts[array_rand($commentTexts)],
                    'status' => 'active',
                ]);

                // Add some reactions
                $numReactions = rand(0, 3);
                for ($r = 0; $r < $numReactions; $r++) {
                    $reactorId = rand(1, 6);
                    if ($reactorId !== $commenterId) {
                        CommentReaction::create([
                            'comment_id' => $comment->id,
                            'liker_id' => $reactorId,
                            'value' => rand(0, 1) ? 'like' : 'dislike',
                        ]);
                    }
                }
            }
        }

        // ========================
        // 10. FAVORITES
        // ========================
        foreach ($shops as $shop) {
            if (rand(0, 1)) {
                Favorite::create([
                    'user_id' => rand(1, 6),
                    'favorable_id' => $shop->id,
                    'favorable_type' => 'App\Models\Shop',
                ]);
            }
        }

        // Add some product favorites
        $products = Product::all();
        foreach ($products->random(min(20, $products->count())) as $product) {
            Favorite::create([
                'user_id' => rand(1, 6),
                'favorable_id' => $product->id,
                'favorable_type' => 'App\Models\Product',
            ]);
        }

        // ========================
        // 11. LIKES (polymorphic)
        // ========================
        foreach ($shops as $shop) {
            if (rand(0, 1)) {
                Like::create([
                    'user_id'       => rand(1, 6),
                    'likeable_id'   => $shop->id,
                    'likeable_type' => 'App\Models\Shop',
                ]);
            }
        }
        foreach ($products->random(min(30, $products->count())) as $product) {
            if (rand(0, 1)) {
                Like::create([
                    'user_id'       => rand(1, 6),
                    'likeable_id'   => $product->id,
                    'likeable_type' => 'App\Models\Product',
                ]);
            }
        }

        $this->call(ProductSeeder::class);

        $this->call(NotificationFormSeeder::class);

        $this->call(VerificationPlansSeeder::class);

        $this->call(NotificationTemplateSeeder::class);

        echo "\n✅ Seeding complete!\n";
        echo "   Countries: " . Country::count() . "\n";
        echo "   Cities: " . cities::count() . "\n";
        echo "   Product Categories: " . ProductCategory::count() . "\n";
        echo "   Users: " . User::count() . "\n";
        echo "   Shops: " . Shop::count() . "\n";
        echo "   Products: " . Product::count() . "\n";
        echo "   Comments: " . Comment::count() . "\n";
        echo "   Favorites: " . Favorite::count() . "\n";
        echo "   Notification Templates: " . \App\Models\NotificationTemplate::count() . "\n";
    }
}
