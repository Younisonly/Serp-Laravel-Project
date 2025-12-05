<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\String\CodePointString;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('backlogo')->nullable();
            $table->string('logo')->nullable();
            $table->string('iconname')->nullable();
            $table->time('open_at')->nullable();
            $table->time('close_at')->nullable();
            $table->string('status')->default('closed'); // open, closed
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('category_id')->nullable(); // shop_categories
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->unsignedInteger('followers_number')->default(0);
            $table->unsignedInteger('raters_number')->default(0);
            $table->decimal('stars_rate', 3, 2)->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->string('days_open')->nullable();
            $table->decimal('points')->default(0);

        
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
