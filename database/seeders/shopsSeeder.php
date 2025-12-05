<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shop;

class shopsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

for ($i = 1; $i <= 30; $i++) {
    Shop::create([
        'name' => 'test_shop_' . $i,   // 👈 سهلة التعرف والحذف
        'description' => 'This is a test shop number ' . $i,
        'backlogo' => 'backlogo_' . $i . '.png',
        'logo' => 'logo_' . $i . '.png',
        'iconname' => 'icon_' . $i,
        'open_at' => '08:00:00',
        'close_at' => '22:00:00',
        'status' => 'open',
        'owner_id' => 1,
        'category_id' => 1,
        'address' => 'Test Address ' . $i,
        'lat' => 15.3 + ($i * 0.01),
        'lng' => 44.2 + ($i * 0.01),
        'followers_number' => rand(0, 100),
        'raters_number' => rand(0, 50),
        'stars_rate' => rand(1, 5),
        'views' => rand(10, 500),
        'joined_at' => now(),
        'days_open' => 'Mon-Fri',
        'points' => rand(1, 100),
    ]);
}

    }
}
