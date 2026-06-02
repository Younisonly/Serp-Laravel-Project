<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Enhance shops table to support ALL commercial place types:
     * restaurants, cafes, salons, clinics, stores, gyms, etc.
     */
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // Business type classification
            $table->string('business_type', 50)->nullable()->after('category')
                  ->comment('retail, restaurant, cafe, service, healthcare, education, entertainment, fitness, beauty, automotive, professional, grocery, hotel, other');

            // Subcategory for granular classification
            $table->string('subcategory', 100)->nullable()->after('business_type');

            // Price range indicator
            $table->string('price_range', 20)->nullable()->after('subcategory')
                  ->comment('budget, moderate, premium, luxury');

            // Amenities as JSON array
            $table->json('amenities')->nullable()->after('price_range')
                  ->comment('["wifi","parking","delivery","takeout","reservations","outdoor_seating","wheelchair_accessible","pet_friendly"]');

            // Detailed operating hours per day (JSON)
            $table->json('operating_hours')->nullable()->after('days_open')
                  ->comment('{"monday":{"open":"09:00","close":"18:00","is_closed":false},...}');

            // Social media consolidated into JSON
            $table->json('social_media')->nullable()->after('facebook')
                  ->comment('{"instagram":"@handle","twitter":"@handle","facebook":"url","whatsapp":"number","tiktok":"@handle","snapchat":"@handle","youtube":"url","linkedin":"url"}');

            // Verification & compliance
            $table->string('tax_number', 100)->nullable()->after('verification_files');
            $table->string('commercial_registration', 100)->nullable()->after('tax_number');
            $table->string('license_number', 100)->nullable()->after('commercial_registration');

            // Featured status
            $table->boolean('is_featured')->default(false)->after('points');

            // Delivery info
            $table->decimal('delivery_radius_km', 5, 2)->nullable()->after('is_featured')
                  ->comment('Maximum delivery radius in kilometers');
            $table->decimal('min_order_amount', 10, 2)->nullable()->after('delivery_radius_km');
            $table->decimal('delivery_fee', 10, 2)->nullable()->after('min_order_amount');
            $table->integer('estimated_delivery_min')->nullable()->after('delivery_fee')
                  ->comment('Estimated delivery time in minutes');
            $table->integer('estimated_delivery_max')->nullable()->after('estimated_delivery_min');

            // Capacity & seating (for restaurants, cafes, etc.)
            $table->integer('seating_capacity')->nullable()->after('estimated_delivery_max');
            $table->boolean('has_takeout')->default(false)->after('seating_capacity');
            $table->boolean('has_delivery')->default(false)->after('has_takeout');
            $table->boolean('has_reservations')->default(false)->after('has_delivery');
            $table->boolean('has_outdoor_seating')->default(false)->after('has_reservations');

            // Service-specific
            $table->boolean('appointment_required')->default(false)->after('has_outdoor_seating')
                  ->comment('For salons, clinics, etc.');
            $table->integer('service_duration_min')->nullable()->after('appointment_required')
                  ->comment('Average service duration in minutes');

            // Accepts payments
            $table->json('payment_methods')->nullable()->after('service_duration_min')
                  ->comment('["cash","card","apple_pay","google_pay","stc_pay"]');

            // Languages spoken
            $table->json('languages')->nullable()->after('payment_methods')
                  ->comment('["en","ar","fr"]');

            // Tags for search
            $table->json('tags')->nullable()->after('languages')
                  ->comment('Search tags: ["vegan","halal","family_friendly","late_night"]');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'business_type', 'subcategory', 'price_range', 'amenities',
                'operating_hours', 'social_media', 'tax_number',
                'commercial_registration', 'license_number', 'is_featured',
                'delivery_radius_km', 'min_order_amount', 'delivery_fee',
                'estimated_delivery_min', 'estimated_delivery_max',
                'seating_capacity', 'has_takeout', 'has_delivery',
                'has_reservations', 'has_outdoor_seating', 'appointment_required',
                'service_duration_min', 'payment_methods', 'languages', 'tags',
            ]);
        });
    }
};
