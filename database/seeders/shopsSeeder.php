<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Image;
use App\Models\Phone;
use App\Models\ProductCategory;

class shopsSeeder extends Seeder
{
    public function run(): void
    {
        $productCategories = ProductCategory::pluck('id', 'name');

        $shopTypes = [
            ['name' => 'Restaurant', 'cat' => 1, 'icon' => 'restaurant'],
            ['name' => 'Cafe', 'cat' => 1, 'icon' => 'cafe'],
            ['name' => 'Supermarket', 'cat' => 1, 'icon' => 'grocery'],
            ['name' => 'Electronics', 'cat' => 0, 'icon' => 'electronics'],
            ['name' => 'Clothing & Fashion', 'cat' => 1, 'icon' => 'fashion'],
            ['name' => 'Pharmacy', 'cat' => 1, 'icon' => 'pharmacy'],
        ];

        // Products per shop type — each shop gets these
        $productTemplates = [
            'Restaurant' => [
                ['name' => 'Mandi Rice', 'price' => 12, 'cat' => 'Food & Beverages'],
                ['name' => 'Grilled Chicken', 'price' => 18, 'cat' => 'Food & Beverages'],
                ['name' => 'Shawarma Plate', 'price' => 8, 'cat' => 'Food & Beverages'],
                ['name' => 'Fresh Juice', 'price' => 5, 'cat' => 'Food & Beverages'],
                ['name' => 'Kabsa', 'price' => 22, 'cat' => 'Food & Beverages'],
                ['name' => 'Mixed Grill', 'price' => 25, 'cat' => 'Food & Beverages'],
                ['name' => 'Hummus & Mutabbal', 'price' => 6, 'cat' => 'Food & Beverages'],
            ],
            'Cafe' => [
                ['name' => 'Arabic Coffee', 'price' => 4, 'cat' => 'Food & Beverages'],
                ['name' => 'Cappuccino', 'price' => 5, 'cat' => 'Food & Beverages'],
                ['name' => 'Mocha Latte', 'price' => 6, 'cat' => 'Food & Beverages'],
                ['name' => 'Cheese Cake', 'price' => 7, 'cat' => 'Food & Beverages'],
                ['name' => 'Chocolate Muffin', 'price' => 4, 'cat' => 'Food & Beverages'],
                ['name' => 'Iced Coffee', 'price' => 5, 'cat' => 'Food & Beverages'],
            ],
            'Supermarket' => [
                ['name' => 'Rice 5kg', 'price' => 15, 'cat' => 'Food & Beverages'],
                ['name' => 'Cooking Oil', 'price' => 8, 'cat' => 'Food & Beverages'],
                ['name' => 'Sugar 2kg', 'price' => 5, 'cat' => 'Food & Beverages'],
                ['name' => 'Mineral Water', 'price' => 3, 'cat' => 'Food & Beverages'],
                ['name' => 'Milk 1L', 'price' => 4, 'cat' => 'Food & Beverages'],
                ['name' => 'Pasta Pack', 'price' => 3, 'cat' => 'Food & Beverages'],
                ['name' => 'Canned Beans', 'price' => 4, 'cat' => 'Food & Beverages'],
                ['name' => 'Bread Pack', 'price' => 3, 'cat' => 'Food & Beverages'],
            ],
            'Electronics' => [
                ['name' => 'Bluetooth Speaker', 'price' => 45, 'cat' => 'Electronics'],
                ['name' => 'Power Bank', 'price' => 30, 'cat' => 'Electronics'],
                ['name' => 'Headphones', 'price' => 25, 'cat' => 'Electronics'],
                ['name' => 'USB Cable', 'price' => 8, 'cat' => 'Electronics'],
                ['name' => 'Phone Charger', 'price' => 12, 'cat' => 'Electronics'],
                ['name' => 'Laptop Stand', 'price' => 35, 'cat' => 'Electronics'],
            ],
            'Clothing & Fashion' => [
                ['name' => 'Cotton T-Shirt', 'price' => 15, 'cat' => 'Clothing'],
                ['name' => 'Summer Dress', 'price' => 35, 'cat' => 'Clothing'],
                ['name' => 'Polo Shirt', 'price' => 20, 'cat' => 'Clothing'],
                ['name' => 'Leather Belt', 'price' => 18, 'cat' => 'Clothing'],
                ['name' => 'Sunglasses', 'price' => 25, 'cat' => 'Clothing'],
                ['name' => 'Casual Sneakers', 'price' => 40, 'cat' => 'Clothing'],
            ],
            'Pharmacy' => [
                ['name' => 'Pain Reliever', 'price' => 5, 'cat' => 'Health & Beauty'],
                ['name' => 'Vitamin C 1000mg', 'price' => 12, 'cat' => 'Health & Beauty'],
                ['name' => 'First Aid Kit', 'price' => 15, 'cat' => 'Health & Beauty'],
                ['name' => 'Hand Sanitizer', 'price' => 4, 'cat' => 'Health & Beauty'],
                ['name' => 'Face Mask Pack', 'price' => 8, 'cat' => 'Health & Beauty'],
                ['name' => 'Cough Syrup', 'price' => 7, 'cat' => 'Health & Beauty'],
            ],
        ];

        $descriptions = [
            'We offer the finest selection with excellent customer service. Visit us today!',
            'Quality products at affordable prices. Your satisfaction is our priority.',
            'Serving our community for over 10 years. We provide the best in class services.',
            'Your trusted partner for quality goods. Wide range of products available.',
        ];

        $imagePaths = [
            'Food & Beverages' => ['/food/1.jpg', '/food/2.jpg', '/food/3.jpg'],
            'Electronics' => ['/electronics/1.jpg', '/electronics/2.jpg'],
            'Clothing' => ['/clothing/1.jpg', '/clothing/2.jpg'],
            'Health & Beauty' => ['/beauty/1.jpg', '/beauty/2.jpg'],
            'Home & Garden' => ['/home/1.jpg'],
            'Books & Stationery' => ['/books/1.jpg'],
            'Sports & Outdoors' => ['/sports/1.jpg'],
            'Automotive' => ['/auto/1.jpg'],
            'Jewelry & Accessories' => ['/jewelry/1.jpg'],
        ];

        for ($i = 1; $i <= 30; $i++) {
            $typeIndex = ($i - 1) % count($shopTypes);
            $type = $shopTypes[$typeIndex];
            $typeName = $type['name'];
            $templates = $productTemplates[$typeName] ?? $productTemplates['Supermarket'];

            $shop = Shop::create([
                'name' => 'test_shop_' . $i,
                'description' => $descriptions[array_rand($descriptions)],
                'backlogo' => null,
                'logo' => null,
                'iconname' => $type['icon'],
                'open_at' => '08:00:00',
                'close_at' => '22:00:00',
                'status' => rand(0, 1) ? 'open' : 'closed',
                'owner_id' => 1,
                'business_type' => $type['icon'],
                'address' => 'Test Street ' . $i . ', Sanaa',
                'lat' => 15.30 + ($i * 0.015) + (rand(-5, 5) * 0.001),
                'lng' => 44.18 + ($i * 0.012) + (rand(-5, 5) * 0.001),
                'followers_number' => rand(10, 300),
                'raters_number' => rand(3, 60),
                'stars_rate' => round(3 + (rand(0, 20) / 10), 1),
                'views' => rand(50, 3000),
                'joined_at' => now()->subDays(rand(10, 365)),
                'days_open' => 'Sat-Thu',
                'points' => rand(5, 200),
            ]);

            Phone::create([
                'table' => 'shops',
                'owner_id' => $shop->id,
                'number' => '9677' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            ]);

            Image::create([
                'table' => 'shops',
                'owner_id' => $shop->id,
                'url' => 'shops/default_logo_' . ($typeIndex + 1) . '.png',
                'is_primary' => true,
            ]);

            // Create 5–10 products per shop
            foreach ($templates as $idx => $tmpl) {
                $hasDiscount = rand(0, 1);
                $discount = $hasDiscount ? rand(5, 35) : 0;
                $catId = $productCategories[$tmpl['cat']] ?? 1;

                $product = Product::create([
                    'name' => $tmpl['name'],
                    'description' => $tmpl['name'] . ' available at ' . $shop->name . '. Fresh and high quality.',
                    'price' => $tmpl['price'] + rand(0, 99) / 100,
                    'shop_id' => $shop->id,
                    'discount' => $discount,
                    'discount_start' => $hasDiscount ? now()->subDays(rand(1, 5)) : null,
                    'discount_end' => $hasDiscount ? now()->addDays(rand(5, 45)) : null,
                    'type_id' => $catId,
                    'barcode' => '627' . str_pad((string) $shop->id, 4, '0', STR_PAD_LEFT)
                                  . str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT),
                    'views' => rand(10, 800),
                    'stars_rate' => round(3 + (rand(0, 20) / 10), 1),
                    'raters_number' => rand(1, 40),
                    'warranty' => in_array($typeName, ['Electronics']) ? 1 : 0,
                    'warranty_start' => in_array($typeName, ['Electronics']) ? now()->subDays(30) : null,
                    'warranty_end' => in_array($typeName, ['Electronics']) ? now()->addMonths(12) : null,
                    'warranty_description' => in_array($typeName, ['Electronics'])
                        ? 'Standard manufacturer warranty covers defects.' : null,
                ]);

                $paths = $imagePaths[$tmpl['cat']] ?? ['/products/general/1.jpg'];
                Image::create([
                    'table' => 'products',
                    'owner_id' => $product->id,
                    'url' => 'products' . $paths[$idx % count($paths)],
                    'is_primary' => true,
                    'status' => 'active',
                ]);
            }
        }

        echo "\n✅ shopsSeeder complete: " . Shop::where('name', 'like', 'test_shop_%')->count()
            . " shops with " . Product::count() . " products total\n";
    }
}
