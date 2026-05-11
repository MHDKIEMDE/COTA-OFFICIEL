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
        Schema::create('combined_bets', function (Blueprint $table) {
            $table->id();

            // Type
            $table->enum('type', ['daily', 'welcome']); // Combiné quotidien ou bienvenue
            $table->date('date'); // Date du combiné

            // Pronostics inclus (JSON array of prediction IDs)
            $table->json('prediction_ids'); // Ex: [12, 45, 78, 92]
            $table->integer('predictions_count')->default(0); // Nombre de matchs (4-5)

            // Cote totale
            $table->decimal('total_odds', 6, 2); // Cote combinée (ex: 8.45)
            $table->decimal('potential_payout', 10, 2)->nullable(); // Gain potentiel pour 1000 FCFA

            // Statut
            $table->enum('status', ['pending', 'won', 'lost', 'partial'])->default('pending');
            $table->integer('won_count')->default(0); // Nombre de pronostics gagnés
            $table->integer('lost_count')->default(0); // Nombre de pronostics perdus

            // Visibilité
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Pour combiné bienvenue (24h)

            // Métadonnées
            $table->text('details')->nullable(); // JSON avec détails matchs
            $table->timestamps();

            // Index
            $table->index(['date', 'type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combined_bets');
    }
};
