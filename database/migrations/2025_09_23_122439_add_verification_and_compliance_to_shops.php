<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            /* ---- Verification Documents ---- */
            $table->json('verification_files')->nullable()
                  ->comment('PDF / image proofs (license, tax, etc.)');

            /* ---- Terms Acceptance ---- */
            $table->timestamp('terms_accepted_at')->nullable()
                  ->comment('When owner accepted platform terms');
            $table->string('terms_ip', 45)->nullable()
                  ->comment('IP from which terms were accepted');

            /* ---- Admin Review ---- */
            $table->string('verification_status', 20)
                  ->default('pending')
                  ->comment('pending | approved | rejected | suspended');
            $table->text('verification_note')->nullable()
                  ->comment('Admin note after review');
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable()
                  ->comment('Admin who clicked approve / reject');
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();

            /* ---- Rejection / Suspension ---- */
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();

            
            $table->string('contact_email', 100)->nullable()
                  ->comment('Business e-mail (can differ from owner)');

            /* ---- Social & Web ---- */
            $table->string('website', 200)->nullable();
            $table->string('instagram', 200)->nullable();
            $table->string('X', 200)->nullable();
            $table->string('whatsapp', 200)->nullable();
            $table->string('facebook', 200)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $columns = [
                'facebook', 'whatsapp', 'X', 'instagram', 'website',
                'contact_email', 'suspension_reason',
                'suspended_at', 'reviewed_by', 'reviewed_at',
                'verification_note', 'verification_status', 'terms_ip',
                'terms_accepted_at', 'verification_files',
            ];
            foreach ($columns as $col) {
                $table->dropColumn($col);
            }
        });
    }
};