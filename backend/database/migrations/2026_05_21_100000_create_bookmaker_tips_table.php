<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmaker_tips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookmaker_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);              // ex: "Paris simples"
            $table->string('icon', 10)->default('💡'); // emoji
            $table->json('tips');                      // ["astuce 1", "astuce 2", ...]
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['bookmaker_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmaker_tips');
    }
};
