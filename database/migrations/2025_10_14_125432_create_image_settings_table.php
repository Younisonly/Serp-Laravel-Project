<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); // e.g., 'user', 'product', 'shop'
            $table->integer('width')->default(1200); // max width
            $table->integer('height')->default(1200); // max height
            $table->float('max_size_mb')->default(2); // max size in MB
            $table->string('watermark')->nullable(); // path to watermark image
            $table->integer('watermark_opacity')->default(60); // opacity 0-100
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_settings');
    }
};
