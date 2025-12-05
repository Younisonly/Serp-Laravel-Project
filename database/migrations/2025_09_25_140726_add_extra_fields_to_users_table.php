<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country', 60)->nullable()->after('image');
            $table->string('city', 60)->nullable()->after('country');
            $table->enum('gender', ['male', 'female'])->nullable()->after('city');
            $table->date('birth_date')->nullable()->after('gender');
            $table->boolean('notifications_enabled')->default(true)->after('birth_date');
            $table->string('type', 20)->default('user')->after('notifications_enabled');
            $table->date('shop_stored_at')->nullable()->after('type');

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'country',
                'city',
                'gender',
                'birth_date',
                'notifications_enabled',
                'type',
                'shop_stored_at',

            ]);
        });
    }
};