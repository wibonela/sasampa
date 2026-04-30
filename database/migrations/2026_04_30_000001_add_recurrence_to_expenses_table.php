<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('frequency', 20)->default('one_time')->after('expense_date');
            $table->date('period_start')->nullable()->after('frequency');
            $table->date('period_end')->nullable()->after('period_start');
        });

        // Backfill: every existing expense becomes a one-off whose period
        // is exactly the day it was recorded.
        DB::statement("UPDATE expenses
                       SET period_start = expense_date,
                           period_end = expense_date
                       WHERE period_start IS NULL");
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['frequency', 'period_start', 'period_end']);
        });
    }
};
