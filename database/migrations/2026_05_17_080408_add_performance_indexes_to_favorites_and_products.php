<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->index(['user_id', 'favorable_type'], 'idx_favorites_user_type');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('name', 'idx_products_name');
            $table->index('price', 'idx_products_price');
            $table->index('discount', 'idx_products_discount');
            $table->index('stars_rate', 'idx_products_stars_rate');
            $table->index('type_id', 'idx_products_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropIndex('idx_favorites_user_type');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_name');
            $table->dropIndex('idx_products_price');
            $table->dropIndex('idx_products_discount');
            $table->dropIndex('idx_products_stars_rate');
            $table->dropIndex('idx_products_type_id');
        });
    }
};
