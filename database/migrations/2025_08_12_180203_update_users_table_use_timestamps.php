<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Remove the custom `date` column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('date');
        });

        // Add Laravel's standard timestamps
        Schema::table('users', function (Blueprint $table) {
            $table->timestamps(); // Adds created_at and updated_at
        });
    }

    public function down()
    {
        // Reverse: Remove timestamps, add `date` back
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
            $table->timestamp('date')->useCurrent();
        });
    }
};