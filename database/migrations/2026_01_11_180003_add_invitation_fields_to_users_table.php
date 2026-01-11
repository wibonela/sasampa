<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Invitation fields
            $table->string('invitation_token', 64)->nullable()->after('pin');
            $table->timestamp('invitation_sent_at')->nullable()->after('invitation_token');
            $table->timestamp('invitation_accepted_at')->nullable()->after('invitation_sent_at');
            $table->enum('invitation_method', ['email', 'pin', 'both'])->nullable()->after('invitation_accepted_at');

            // User status
            $table->boolean('is_active')->default(true)->after('role');

            // Indexes
            $table->index('invitation_token');
            $table->index(['company_id', 'is_active'], 'users_company_active_idx');
        });

        // Note: PIN length is kept at 4 characters for backwards compatibility
        // For a production MySQL database, you can run:
        // ALTER TABLE users MODIFY pin VARCHAR(6) NULL;
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_company_active_idx');
            $table->dropIndex(['invitation_token']);

            $table->dropColumn([
                'invitation_token',
                'invitation_sent_at',
                'invitation_accepted_at',
                'invitation_method',
                'is_active',
            ]);
        });
    }
};
