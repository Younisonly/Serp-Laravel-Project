<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_forms', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('category', 50)->index();
            $table->string('title_template', 255);
            $table->text('body_template');
            $table->string('default_title', 255)->nullable();
            $table->text('default_body')->nullable();
            $table->json('placeholders')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->string('icon', 50)->nullable();
            $table->string('action_type', 50)->nullable();
            $table->string('priority', 20)->default('normal');
            $table->string('owner_type', 20)->default('user');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_forms');
    }
};
