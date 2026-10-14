<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = ['products', 'categories', 'inventory', 'transactions', 'stock_adjustments'];

    public function up(): void
    {
        // Runs before create_branches_table (same timestamp, earlier name), and PostgreSQL
        // refuses a foreign key to a table that does not exist yet.
        if (! Schema::hasTable('branches')) {
            (require __DIR__ . '/2026_01_11_125839_create_branches_table.php')->up();
        }

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
