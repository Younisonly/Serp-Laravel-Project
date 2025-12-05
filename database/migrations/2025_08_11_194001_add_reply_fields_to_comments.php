<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            // The reply content
            $table->text('reply_content')->nullable()->after('content');

            // Who replied: 'admin', 'shop_owner', 'product_owner'
            $table->string('replyer_type')->nullable()->after('reply_content');

            // When it was replied
            $table->timestamp('replied_at')->nullable()->after('replyer_type');
        });
    }

    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['reply_content', 'replyer_type', 'replied_at']);
        });
    }
};