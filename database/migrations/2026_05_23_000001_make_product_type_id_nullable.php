<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->unsignedBigInteger('type_id')->nullable()->default(null)->change();
            $table->foreign('type_id')->references('id')->on('product_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->unsignedBigInteger('type_id')->default(0)->change();
            $table->foreign('type_id')->references('id')->on('product_categories')->onDelete('cascade');
        });
    }
};
