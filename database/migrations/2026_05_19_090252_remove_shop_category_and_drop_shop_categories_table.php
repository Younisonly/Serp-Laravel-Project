<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex('shops_category_index');
            $table->dropColumn('category');
        });

        Schema::dropIfExists('shop_categories');
    }

    public function down(): void
    {
        Schema::create('shop_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->tinyInteger('provides')->default(1);
            $table->timestamps();
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('category')->default(0)->after('name');
            $table->index('category', 'shops_category_index');
        });
    }
};
