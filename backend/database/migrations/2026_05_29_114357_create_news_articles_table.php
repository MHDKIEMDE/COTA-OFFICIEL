<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_source_id')->constrained('news_sources')->cascadeOnDelete();
            $table->string('title');
            $table->string('url');
            $table->string('guid')->unique();         // identifiant RSS unique
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('author')->nullable();
            $table->json('tags')->nullable();         // mots-clés extraits du titre
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['news_source_id', 'published_at']);
            $table->index('published_at');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
