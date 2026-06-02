<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'target_type')) {
                $table->string('target_type', 50)->nullable()->after('data')->index();
            }
            if (!Schema::hasColumn('notifications', 'target_id')) {
                $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
            }
            if (!Schema::hasColumn('notifications', 'navigation_type')) {
                $table->string('navigation_type', 100)->nullable()->after('target_id')->index();
            }
            if (!Schema::hasColumn('notifications', 'metadata')) {
                $table->json('metadata')->nullable()->after('navigation_type');
            }

            $table->index(['target_type', 'target_id'], 'notif_target_idx');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notif_target_idx');
            $table->dropColumn(['target_type', 'target_id', 'navigation_type', 'metadata']);
        });
    }
};
