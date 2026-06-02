<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_ar')->nullable()->after('name_en');
            $table->string('icon')->nullable()->after('name_ar');
            $table->boolean('is_active')->default(true)->after('icon');
            $table->integer('sort_order')->default(0)->after('is_active');
            $table->index(['is_active', 'sort_order']);
        });

        DB::table('product_categories')->update([
            'name_en' => DB::raw('name'),
            'name_ar' => DB::raw('name'),
        ]);
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'sort_order']);
            $table->dropColumn(['name_en', 'name_ar', 'icon', 'is_active', 'sort_order']);
        });
    }
};
