<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->unsignedInteger('price_tzs')->default(0);
            // Nullable limit = unlimited.
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_branches')->nullable();
            $table->unsignedInteger('max_products')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->string('status')->default('trialing'); // trialing, active, past_due, expired
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('amount_tzs');
            $table->unsignedSmallInteger('months')->default(1);
            $table->string('msisdn')->nullable();
            $table->string('gateway')->default('manual'); // manual, selcom
            $table->string('gateway_ref')->nullable()->unique();
            $table->string('status')->default('pending'); // pending, paid, failed
            $table->json('raw_payload')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });

        // Deploys run migrations but not seeders; billing needs its plans to exist.
        (new \Database\Seeders\PlanSeeder())->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
