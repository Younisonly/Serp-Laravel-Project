<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['shop_id']);
            $table->dropColumn('shop_id');
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->unsignedBigInteger('favorable_id')->after('user_id');
            $table->string('favorable_type')->after('favorable_id');
            $table->index(['favorable_id', 'favorable_type']);
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropColumn(['favorable_id', 'favorable_type']);
            $table->unsignedBigInteger('shop_id');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};