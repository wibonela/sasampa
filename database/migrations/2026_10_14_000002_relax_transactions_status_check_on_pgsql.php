<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * transactions.status was created as enum(completed, refunded, voided), but orders also use
     * pending/cancelled/converted etc. SQLite lost that CHECK when a later migration rebuilt the
     * table, so production never enforced it; PostgreSQL keeps it and rejects those rows.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table transactions drop constraint if exists transactions_status_check');
        }
    }

    public function down(): void
    {
        // intentionally empty: the old constraint was wrong for the data the app writes
    }
};
