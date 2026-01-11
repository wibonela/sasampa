<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('documentation_articles')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->unique(['article_id', 'locale']);
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentation_article_translations');
    }
};
