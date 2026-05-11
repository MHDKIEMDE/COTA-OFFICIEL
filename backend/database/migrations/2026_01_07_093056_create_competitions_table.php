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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('sportradar_id')->unique(); // ex: sr:competition:17
            $table->string('name'); // Nom court: Premier League
            $table->string('full_name')->nullable(); // Nom complet: English Premier League
            $table->string('country')->nullable(); // Pays ou région
            $table->string('icon')->nullable(); // Emoji ou classe CSS
            $table->integer('priority')->default(99); // 1 = plus prioritaire
            $table->boolean('is_active')->default(true); // Compétition active
            $table->boolean('is_trending')->default(false); // 🔥 Tendance actuellement
            $table->date('trending_start')->nullable(); // Début période tendance
            $table->date('trending_end')->nullable(); // Fin période tendance
            $table->text('description')->nullable(); // Description optionnelle
            $table->integer('display_order')->default(0); // Ordre d'affichage
            $table->timestamps();

            // Index pour les requêtes fréquentes
            $table->index(['is_active', 'priority']);
            $table->index(['is_trending', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
