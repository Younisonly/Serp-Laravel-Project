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
        Schema::create('raters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rater_id');
            $table->string('table'); // 'shops', 'products'
            $table->unsignedBigInteger('rated_id'); // shop_id or product_id
            $table->unsignedTinyInteger('stars'); // 1 to 5
            $table->timestamps();
            $table->foreign('rater_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raters');
    }
};
