<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('unit_price');
        });

        // Backfill existing transaction items with current product cost prices
        DB::statement("
            UPDATE transaction_items
            SET cost_price = (
                SELECT COALESCE(products.cost_price, 0)
                FROM products
                WHERE products.id = transaction_items.product_id
            )
            WHERE cost_price = 0
        ");
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
