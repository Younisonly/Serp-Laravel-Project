<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['owner_id', 'owner_type', 'is_read'], 'notif_owner_read_idx');
            $table->index(['type']);
            $table->index(['created_at']);
        });

        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->morphs('notifiable');
                $table->string('category', 50);
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
                $table->unique(['notifiable_type', 'notifiable_id', 'category'], 'notif_pref_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notif_owner_read_idx');
            $table->dropIndex(['type']);
            $table->dropIndex(['created_at']);
        });

        Schema::dropIfExists('notification_preferences');
    }
};
