<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('verification_plans')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->nullable();
            $table->integer('percentage')->nullable();
            $table->string('reason_title')->nullable();
            $table->string('reason_subtitle')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_discounts');
    }
};
