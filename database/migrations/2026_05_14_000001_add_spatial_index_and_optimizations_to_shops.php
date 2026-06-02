<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // shop_lat_lng_index already added in a previous partial run
        // Spatial index requires POINT/GEOMETRY columns — skipped

        Schema::table('shops', function (Blueprint $table) {
            $table->index('status', 'shops_status_index');
            $table->index('category', 'shops_category_index');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex('shops_lat_lng_index');
            $table->dropIndex('shops_status_index');
            $table->dropIndex('shops_category_index');
        });
        // DB::statement('ALTER TABLE shops DROP INDEX shops_spatial_idx');
    }
};
