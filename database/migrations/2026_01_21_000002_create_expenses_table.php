<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->nullable();
            $table->date('expense_date');
            $table->string('reference_number')->nullable();
            $table->string('supplier')->nullable();
            $table->string('payment_method')->default('cash');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'expense_date']);
            $table->index(['company_id', 'expense_category_id']);
            $table->index(['company_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
