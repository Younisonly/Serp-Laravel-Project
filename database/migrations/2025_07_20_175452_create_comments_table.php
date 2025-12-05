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
        Schema::create('comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('commenter_id');
                $table->string('table'); // 'shops', 'products'
                $table->unsignedBigInteger('commented_id'); // shop_id or product_id
                $table->text('content');
                $table->string('status')->default('active'); // e.g., active, blocked
                $table->timestamps();
            
                $table->foreign('commenter_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
