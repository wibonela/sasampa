<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sandukus', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['feedback', 'bug'])->default('feedback');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('contact')->nullable();
            $table->string('page_url')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('screenshot')->nullable();
            $table->enum('status', ['new', 'reviewed', 'resolved'])->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sandukus');
    }
};
