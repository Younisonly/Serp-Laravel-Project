<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            // Remove the old string country column
            $table->dropColumn('country');

            // Add country_id as a foreign key
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('country')->nullable();
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
