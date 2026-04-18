<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_app_requests', function (Blueprint $table) {
            $table->boolean('is_suspicious')->default(false)->after('expected_devices');
            $table->string('suspicious_reason')->nullable()->after('is_suspicious');
            $table->timestamp('scheduled_approval_at')->nullable()->after('suspicious_reason');
            $table->boolean('auto_approved')->default(false)->after('scheduled_approval_at');

            $table->index('scheduled_approval_at');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_app_requests', function (Blueprint $table) {
            $table->dropIndex(['scheduled_approval_at']);
            $table->dropColumn(['is_suspicious', 'suspicious_reason', 'scheduled_approval_at', 'auto_approved']);
        });
    }
};
