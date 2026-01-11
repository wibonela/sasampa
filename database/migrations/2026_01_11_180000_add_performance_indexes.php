<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Products - composite indexes for common queries
        Schema::table('products', function (Blueprint $table) {
            $table->index(['company_id', 'is_active'], 'products_company_active_idx');
            $table->index(['company_id', 'category_id'], 'products_company_category_idx');
            $table->index(['company_id', 'created_at'], 'products_company_created_idx');
        });

        // Transactions - composite indexes for filtering and reports
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'transactions_company_status_idx');
            $table->index(['company_id', 'created_at'], 'transactions_company_created_idx');
            $table->index(['branch_id', 'created_at'], 'transactions_branch_created_idx');
            $table->index(['company_id', 'user_id'], 'transactions_company_user_idx');
        });

        // Transaction items - composite index for lookups
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->index(['product_id', 'transaction_id'], 'transaction_items_product_trans_idx');
        });

        // Inventory - composite index for stock queries
        Schema::table('inventory', function (Blueprint $table) {
            $table->index(['company_id', 'quantity'], 'inventory_company_qty_idx');
        });

        // Stock adjustments - composite index for history queries
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->index(['company_id', 'created_at'], 'stock_adj_company_created_idx');
        });

        // Users - composite index for user queries
        Schema::table('users', function (Blueprint $table) {
            $table->index(['company_id', 'role'], 'users_company_role_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_company_active_idx');
            $table->dropIndex('products_company_category_idx');
            $table->dropIndex('products_company_created_idx');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_company_status_idx');
            $table->dropIndex('transactions_company_created_idx');
            $table->dropIndex('transactions_branch_created_idx');
            $table->dropIndex('transactions_company_user_idx');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropIndex('transaction_items_product_trans_idx');
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->dropIndex('inventory_company_qty_idx');
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropIndex('stock_adj_company_created_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_company_role_idx');
        });
    }
};
