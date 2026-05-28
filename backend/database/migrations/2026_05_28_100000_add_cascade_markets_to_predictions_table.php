<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            // Traçabilité hybridation (§8.5 CDC V2)
            $table->decimal('score_algo', 6, 2)->nullable()->after('total_score');
            $table->decimal('score_externe', 6, 2)->nullable()->after('score_algo');
            $table->decimal('score_publie', 6, 2)->nullable()->after('score_externe');
            $table->decimal('w_ext', 4, 2)->default(0)->after('score_publie');

            // Marché sélectionné par le sélecteur cascade (§7.3 CDC V2)
            $table->string('bet_market', 50)->nullable()->after('bet_type');
            // Moteur utilisé : 'force' | 'goals' | 'high_variance'
            $table->string('engine_used', 20)->nullable()->after('bet_market');
            // Score valeur du sélecteur (confiance × valeur_cote)
            $table->decimal('market_value_score', 6, 3)->nullable()->after('engine_used');
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn([
                'score_algo', 'score_externe', 'score_publie', 'w_ext',
                'bet_market', 'engine_used', 'market_value_score',
            ]);
        });
    }
};
