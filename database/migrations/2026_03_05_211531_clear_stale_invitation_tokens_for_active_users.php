<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Clear stale invitation tokens for users who are already active
     * and have been using the platform (have verified email or transactions).
     */
    public function up(): void
    {
        // Clear invitation tokens for active users who have email_verified_at set
        // or who have been actively using the platform
        DB::table('users')
            ->whereNotNull('invitation_token')
            ->whereNull('invitation_accepted_at')
            ->where('is_active', true)
            ->update([
                'invitation_token' => null,
                'invitation_accepted_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse - data was stale
    }
};
