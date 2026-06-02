<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessTypesAndAmenitiesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Business Types
        $businessTypes = [
            ['slug' => 'restaurant', 'icon' => 'restaurant', 'name_en' => 'Restaurant', 'name_ar' => 'مطعم', 'description_en' => 'Dining establishment serving food and beverages', 'description_ar' => 'مؤسسة طعام تقدم الوجبات والمشروبات', 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'cafe', 'icon' => 'local_cafe', 'name_en' => 'Café', 'name_ar' => 'مقهى', 'description_en' => 'Coffee shop and casual dining', 'description_ar' => 'مقهى وجلسات', 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'retail', 'icon' => 'storefront', 'name_en' => 'Retail Store', 'name_ar' => 'متجر', 'description_en' => 'General retail and shopping', 'description_ar' => 'متجر تجزئة', 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'healthcare', 'icon' => 'local_hospital', 'name_en' => 'Healthcare', 'name_ar' => 'رعاية صحية', 'description_en' => 'Medical and health services', 'description_ar' => 'خدمات طبية وصحية', 'is_active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'beauty', 'icon' => 'spa', 'name_en' => 'Beauty & Spa', 'name_ar' => 'تجميل وسبا', 'description_en' => 'Beauty salon and spa services', 'description_ar' => 'صالون تجميل وسبا', 'is_active' => true, 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'fitness', 'icon' => 'fitness_center', 'name_en' => 'Fitness', 'name_ar' => 'لياقة بدنية', 'description_en' => 'Gym and fitness center', 'description_ar' => 'نادي رياضي ولياقة', 'is_active' => true, 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'education', 'icon' => 'school', 'name_en' => 'Education', 'name_ar' => 'تعليم', 'description_en' => 'Educational institution or training center', 'description_ar' => 'مؤسسة تعليمية أو مركز تدريب', 'is_active' => true, 'sort_order' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'service', 'icon' => 'build', 'name_en' => 'Service', 'name_ar' => 'خدمة', 'description_en' => 'Professional services', 'description_ar' => 'خدمات مهنية', 'is_active' => true, 'sort_order' => 8, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'grocery', 'icon' => 'shopping_cart', 'name_en' => 'Grocery', 'name_ar' => 'بقالة', 'description_en' => 'Supermarket and grocery store', 'description_ar' => 'سوبرماركت وبقالة', 'is_active' => true, 'sort_order' => 9, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'electronics', 'icon' => 'devices', 'name_en' => 'Electronics', 'name_ar' => 'إلكترونيات', 'description_en' => 'Electronics and tech store', 'description_ar' => 'متجر إلكترونيات وتقنية', 'is_active' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'clothing', 'icon' => 'checkroom', 'name_en' => 'Clothing', 'name_ar' => 'ملابس', 'description_en' => 'Fashion and apparel store', 'description_ar' => 'متجر أزياء وملابس', 'is_active' => true, 'sort_order' => 11, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'pharmacy', 'icon' => 'medication', 'name_en' => 'Pharmacy', 'name_ar' => 'صيدلية', 'description_en' => 'Pharmacy and drugstore', 'description_ar' => 'صيدلية', 'is_active' => true, 'sort_order' => 12, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'bakery', 'icon' => 'cake', 'name_en' => 'Bakery', 'name_ar' => 'مخبز', 'description_en' => 'Bakery and pastry shop', 'description_ar' => 'مخبز وحلويات', 'is_active' => true, 'sort_order' => 13, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'other', 'icon' => 'business_center', 'name_en' => 'Other', 'name_ar' => 'أخرى', 'description_en' => 'Other business type', 'description_ar' => 'نوع آخر', 'is_active' => true, 'sort_order' => 99, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('business_types')->insert($businessTypes);

        // Amenities
        $amenities = [
            ['slug' => 'wifi', 'icon' => 'wifi', 'name_en' => 'Free WiFi', 'name_ar' => 'واي فاي مجاني', 'category' => 'facility', 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'parking', 'icon' => 'local_parking', 'name_en' => 'Parking', 'name_ar' => 'موقف سيارات', 'category' => 'facility', 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'wheelchair', 'icon' => 'accessible', 'name_en' => 'Wheelchair Access', 'name_ar' => 'دخول ذوي الاحتياجات', 'category' => 'accessibility', 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'delivery', 'icon' => 'delivery_dining', 'name_en' => 'Delivery', 'name_ar' => 'توصيل', 'category' => 'service', 'is_active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'takeout', 'icon' => 'shopping_bag', 'name_en' => 'Takeout', 'name_ar' => 'طلب خارجي', 'category' => 'service', 'is_active' => true, 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'reservations', 'icon' => 'event_note', 'name_en' => 'Reservations', 'name_ar' => 'حجز مسبق', 'category' => 'service', 'is_active' => true, 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'outdoor_seating', 'icon' => 'park', 'name_en' => 'Outdoor Seating', 'name_ar' => 'جلسات خارجية', 'category' => 'facility', 'is_active' => true, 'sort_order' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'private_rooms', 'icon' => 'meeting_room', 'name_en' => 'Private Rooms', 'name_ar' => 'غرف خاصة', 'category' => 'facility', 'is_active' => true, 'sort_order' => 8, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'kids_area', 'icon' => 'child_friendly', 'name_en' => 'Kids Area', 'name_ar' => 'منطقة أطفال', 'category' => 'facility', 'is_active' => true, 'sort_order' => 9, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'pet_friendly', 'icon' => 'pets', 'name_en' => 'Pet Friendly', 'name_ar' => 'مسموح بالحيوانات', 'category' => 'facility', 'is_active' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'credit_cards', 'icon' => 'credit_card', 'name_en' => 'Credit Cards', 'name_ar' => 'بطاقات ائتمان', 'category' => 'service', 'is_active' => true, 'sort_order' => 11, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'atm', 'icon' => 'atm', 'name_en' => 'ATM', 'name_ar' => 'صراف آلي', 'category' => 'facility', 'is_active' => true, 'sort_order' => 12, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'prayer_room', 'icon' => 'mosque', 'name_en' => 'Prayer Room', 'name_ar' => 'غرفة صلاة', 'category' => 'facility', 'is_active' => true, 'sort_order' => 13, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'family_section', 'icon' => 'family_restroom', 'name_en' => 'Family Section', 'name_ar' => 'قسم عائلي', 'category' => 'facility', 'is_active' => true, 'sort_order' => 14, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'valet', 'icon' => 'car_rental', 'name_en' => 'Valet Parking', 'name_ar' => 'خدمة صف السيارات', 'category' => 'service', 'is_active' => true, 'sort_order' => 15, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'smoking_area', 'icon' => 'smoking_rooms', 'name_en' => 'Smoking Area', 'name_ar' => 'منطقة تدخين', 'category' => 'facility', 'is_active' => true, 'sort_order' => 16, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('amenities')->insert($amenities);
    }
}
