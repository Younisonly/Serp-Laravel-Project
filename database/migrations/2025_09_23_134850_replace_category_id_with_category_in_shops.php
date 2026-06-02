<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // 1. remove old column (no FK constraint existed on this column)
            $table->dropColumn('category_id');

            // 2. add new flag: 0 = product, 1 = service (or whatever you decide)
            $table->boolean('category')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('category');

            // bring back the old set-up (in case you ever roll back)
            $table->unsignedBigInteger('category_id')->nullable();
        });
    }
};