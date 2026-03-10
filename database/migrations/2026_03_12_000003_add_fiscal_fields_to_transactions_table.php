<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('fiscal_receipt_number')->nullable()->after('notes');
            $table->string('fiscal_verification_code')->nullable()->after('fiscal_receipt_number');
            $table->text('fiscal_qr_code')->nullable()->after('fiscal_verification_code');
            $table->timestamp('fiscal_receipt_time')->nullable()->after('fiscal_qr_code');
            $table->boolean('fiscal_submitted')->default(false)->after('fiscal_receipt_time');
            $table->text('fiscal_submission_error')->nullable()->after('fiscal_submitted');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'fiscal_receipt_number', 'fiscal_verification_code',
                'fiscal_qr_code', 'fiscal_receipt_time',
                'fiscal_submitted', 'fiscal_submission_error',
            ]);
        });
    }
};
