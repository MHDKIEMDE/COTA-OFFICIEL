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
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Type de favori: 'team', 'competition', 'match'
            $table->enum('type', ['team', 'competition', 'match']);
            
            // ID de l'élément favori (peut être un ID API ou un ID interne)
            $table->string('item_id');
            
            // Métadonnées optionnelles (pour éviter de refaire des requêtes)
            $table->string('item_name')->nullable(); // Nom de l'équipe/compétition
            $table->string('item_logo')->nullable(); // Logo URL
            $table->string('item_country')->nullable(); // Pays (pour compétitions)
            
            $table->timestamps();
            
            // Index pour éviter les doublons et optimiser les requêtes
            $table->unique(['user_id', 'type', 'item_id']);
            $table->index('user_id');
            $table->index(['type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};
