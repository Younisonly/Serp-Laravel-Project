<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('highlights')->nullable()->after('description');
            $table->json('specifications')->nullable()->after('highlights');
            $table->json('tags')->nullable()->after('specifications');
            $table->string('brand', 100)->nullable()->after('tags');
            $table->string('model_number', 100)->nullable()->after('brand');
            $table->string('color', 50)->nullable()->after('model_number');
            $table->string('size', 50)->nullable()->after('color');
            $table->string('material', 100)->nullable()->after('size');
            $table->decimal('weight', 10, 2)->nullable()->after('material');
            $table->text('shipping_info')->nullable()->after('weight');
            $table->text('return_policy')->nullable()->after('shipping_info');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'highlights', 'specifications', 'tags', 'brand', 'model_number',
                'color', 'size', 'material', 'weight', 'shipping_info', 'return_policy',
            ]);
        });
    }
};
