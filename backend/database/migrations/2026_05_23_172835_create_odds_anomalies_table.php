<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odds_anomalies', function (Blueprint $table) {
            $table->id();
            $table->string('match_id');
            $table->string('home_team');
            $table->string('away_team');
            $table->string('competition')->nullable();
            $table->string('country')->nullable();
            $table->dateTime('match_date');
            $table->string('bet_type');       // h2h | over_25 | btts
            $table->string('outcome');        // 1 | X | 2 | over | under
            $table->string('bookmaker');      // 1xbet | rapidapi | bet365
            $table->decimal('odd_value', 6, 2);    // cote anormale détectée
            $table->decimal('market_odd', 6, 2);   // cote du marché (référence)
            $table->decimal('gap_pct', 5, 1);      // écart en %
            $table->boolean('is_overpriced')->default(true); // true = cote trop haute (erreur bookmaker)
            $table->boolean('notified')->default(false);
            $table->dateTime('expires_at');    // anomalie valable ~20 min
            $table->timestamps();

            $table->index(['match_id', 'bet_type', 'outcome']);
            $table->index('expires_at');
            $table->index('notified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odds_anomalies');
    }
};
