<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            // Sélection exacte dans le marché (ex: "1", "X2", "O2.5", "GG", "COR_O8.5")
            $table->string('market_selection', 30)->nullable()->after('bet_market');

            // Score de confiance du marché sélectionné (0–100)
            $table->decimal('market_score', 5, 2)->nullable()->after('market_selection');

            // Tier de publication : gold ≥65 / standard 50–64 / bronze 35–49
            $table->enum('score_tier', ['gold', 'standard', 'bronze'])->nullable()->after('market_score');

            // Quelle(s) équipe(s) illuminer dans la carte UI
            $table->enum('active_side', ['home', 'away', 'both', 'none'])->nullable()->after('score_tier');

            $table->index('score_tier',   'idx_predictions_score_tier');
            $table->index('active_side',  'idx_predictions_active_side');
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropIndex('idx_predictions_score_tier');
            $table->dropIndex('idx_predictions_active_side');
            $table->dropColumn(['market_selection', 'market_score', 'score_tier', 'active_side']);
        });
    }
};
