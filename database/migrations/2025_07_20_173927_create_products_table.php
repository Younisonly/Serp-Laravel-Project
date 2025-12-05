<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedBigInteger('shop_id');
            $table->unsignedInteger('views')->default(0);
            $table->boolean('warranty')->default(false);
            $table->date('warranty_start')->nullable();
            $table->date('warranty_end')->nullable();
            $table->text('warranty_description')->nullable();
            $table->boolean('discount')->default(false);
            $table->date('discount_start')->nullable();
            $table->date('discount_end')->nullable();
            $table->json('discount_dates')->nullable(); // for specific dates
            $table->decimal('stars_rate', 3, 2)->default(0);
            $table->unsignedInteger('raters_number')->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('type_id')->default(0);

            $table->foreign('type_id')->references('id')->on('product_categories')->onDelete('cascade');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
