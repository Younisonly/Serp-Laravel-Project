<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->renameColumn('table', 'commentable_type');
        });
    }

    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->renameColumn('commentable_type', 'table');
        });
    }
};