<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_waitlist', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->string('business_name');
            $table->enum('business_type', ['restaurant', 'retail', 'pharmacy', 'supermarket', 'salon', 'other']);
            $table->enum('platform', ['ios', 'android', 'both']);
            $table->string('referral_source')->nullable();
            $table->enum('status', ['pending', 'contacted', 'converted', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('platform');
            $table->index('business_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_waitlist');
    }
};
