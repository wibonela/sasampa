<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Client-generated id for sales made offline; makes re-sending a sale idempotent.
            $table->string('offline_id', 100)->nullable()->after('transaction_number');
            $table->index(['company_id', 'offline_id']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'offline_id']);
            $table->dropColumn('offline_id');
        });
    }
};
