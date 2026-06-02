<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support', function (Blueprint $table) {
            $table->boolean('hidden_from_sender')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('support', function (Blueprint $table) {
            $table->dropColumn('hidden_from_sender');
        });
    }
};
