<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_limit_requests', function (Blueprint $table) {
            $table->foreignId('requested_plan_id')->nullable()->after('requested_limit')
                ->constrained('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_limit_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requested_plan_id');
        });
    }
};
