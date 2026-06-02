<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // e.g., 'wifi', 'parking', 'wheelchair'
            $table->string('icon')->nullable();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('category')->nullable(); // e.g., 'facility', 'service', 'accessibility'
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'category', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
