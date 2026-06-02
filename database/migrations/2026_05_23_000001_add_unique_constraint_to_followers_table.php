<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('followers', function (Blueprint $table) {
            $table->unique(['follower_id', 'followed_shop_id'], 'followers_unique');
        });
    }

    public function down(): void
    {
        Schema::table('followers', function (Blueprint $table) {
            $table->dropUnique('followers_unique');
        });
    }
};
