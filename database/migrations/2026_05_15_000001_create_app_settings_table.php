<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        DB::table('app_settings')->insert([
            ['key' => 'contact_email', 'value' => 'support@shopmaps.com', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => null, 'group' => 'contact'],
            ['key' => 'instagram', 'value' => null, 'group' => 'social'],
            ['key' => 'twitter', 'value' => null, 'group' => 'social'],
            ['key' => 'facebook', 'value' => null, 'group' => 'social'],
            ['key' => 'whatsapp', 'value' => null, 'group' => 'contact'],
            ['key' => 'min_age', 'value' => '18', 'group' => 'compliance'],
            ['key' => 'watermark_image', 'value' => null, 'group' => 'branding'],
            ['key' => 'watermark_opacity', 'value' => '60', 'group' => 'branding'],
            ['key' => 'app_version', 'value' => '1.0.0', 'group' => 'general'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
