<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('type', 10)->default('sale')->after('transaction_number');
            $table->timestamp('valid_until')->nullable()->after('notes');
            $table->string('payment_method')->nullable()->change();
            $table->index(['company_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'type', 'status']);
            $table->dropColumn(['type', 'valid_until']);
        });
    }
};
