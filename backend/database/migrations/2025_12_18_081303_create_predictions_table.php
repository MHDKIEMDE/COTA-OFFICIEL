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
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();

            // Informations Match
            $table->integer('match_id')->unique(); // ID match de l'API-Football
            $table->string('home_team');
            $table->string('away_team');
            $table->integer('home_team_id');
            $table->integer('away_team_id');
            $table->string('competition');
            $table->integer('competition_id');
            $table->string('country')->nullable();
            $table->dateTime('match_date');
            $table->string('match_time', 10); // Format HH:mm

            // Pronostic
            $table->enum('bet_type', ['1X2', 'Over/Under', 'BTTS', 'Double Chance', 'HT/FT']); // Type de pari
            $table->string('prediction'); // Ex: "1" (victoire domicile), "Over 2.5", "Yes"
            $table->decimal('odds', 5, 2); // Cote (ex: 1.85)
            $table->integer('confidence_stars')->default(1); // 1 à 4 étoiles

            // Scores Algorithme (6 critères)
            $table->decimal('score_form', 5, 2)->default(0); // Score forme récente (max 28)
            $table->decimal('score_h2h', 5, 2)->default(0); // Score H2H (max 23)
            $table->decimal('score_home_away', 5, 2)->default(0); // Score domicile/extérieur (max 18)
            $table->decimal('score_league', 5, 2)->default(0); // Score classement (max 13)
            $table->decimal('score_goals', 5, 2)->default(0); // Score statistiques buts (max 10)
            $table->decimal('score_time', 5, 2)->default(0); // Score horaire (max 8)
            $table->decimal('total_score', 5, 2); // Score total (max 100)

            // Résultat Match
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->enum('status', ['pending', 'won', 'lost', 'void'])->default('pending');
            $table->boolean('is_published')->default(false); // Publié ou non
            $table->boolean('is_premium')->default(false); // Visible par premium uniquement

            // Métadonnées
            $table->text('analysis_details')->nullable(); // JSON avec détails algorithme
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index('match_date');
            $table->index('status');
            $table->index(['is_published', 'is_premium']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
