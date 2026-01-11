<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('branches_enabled')->default(false)->after('onboarding_completed');
            $table->enum('branch_sharing_mode', ['shared', 'independent'])->default('shared')->after('branches_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['branches_enabled', 'branch_sharing_mode']);
        });
    }
};
