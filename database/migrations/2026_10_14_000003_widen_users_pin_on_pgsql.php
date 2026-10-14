<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * users.pin was created as varchar(4) but the app stores a bcrypt hash (60 chars) in it.
     * SQLite ignores column lengths, so production never noticed; PostgreSQL enforces them.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table users alter column pin type varchar(255)');
        }
    }

    public function down(): void
    {
        // intentionally empty: narrowing would truncate stored hashes
    }
};
