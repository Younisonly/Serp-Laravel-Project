<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('image_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('quality')
                  ->default(85)
                  ->after('max_size_mb')
                  ->comment('JPEG/PNG image quality (1-100)');
        });
    }

    public function down(): void
    {
        Schema::table('image_settings', function (Blueprint $table) {
            $table->dropColumn('quality');
        });
    }
};
