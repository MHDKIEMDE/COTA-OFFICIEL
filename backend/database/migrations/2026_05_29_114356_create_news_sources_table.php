<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('rss_url');
            $table->string('website_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('language', 5)->default('fr');
            $table->string('category')->default('football');
            $table->boolean('is_active')->default(true);
            $table->integer('fetch_interval')->default(30);
            $table->timestamp('last_fetched_at')->nullable();
            $table->integer('articles_count')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
