<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = ['products', 'categories', 'inventory', 'transactions', 'stock_adjustments'];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('company_id')
                      ->constrained()->nullOnDelete();
                $table->index('branch_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign([$tableName . '_branch_id_foreign']);
                $table->dropIndex([$tableName . '_branch_id_index']);
                $table->dropColumn('branch_id');
            });
        }
    }
};
