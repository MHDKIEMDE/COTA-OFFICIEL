<?php

namespace App\Console\Commands;

use App\Models\Prediction;
use App\Services\MarketScoringService;
use App\Services\PredictionAlgorithmService;
use Illuminate\Console\Command;

/**
 * Reconstruit les marchés switchables (prediction_markets) des prédictions
 * déjà en base, à partir de leurs scores 9 critères stockés.
 *
 * Usage : php artisan predictions:backfill-markets [--fresh]
 */
class BackfillPredictionMarkets extends Command
{
    protected $signature = 'predictions:backfill-markets {--fresh : Vide les marchés existants avant}';

    protected $description = 'Génère les marchés multi-cascade pour les prédictions existantes';

    public function handle(PredictionAlgorithmService $algo, MarketScoringService $scoring): int
    {
        $predictions = Prediction::query()
            ->when(! $this->option('fresh'), fn ($q) => $q->doesntHave('markets'))
            ->get();

        if ($predictions->isEmpty()) {
            $this->info('Aucune prédiction à traiter.');

            return self::SUCCESS;
        }

        $build = (new \ReflectionMethod($algo, 'buildAllCandidates'));
        $build->setAccessible(true);

        $bar = $this->output->createProgressBar($predictions->count());
        $bar->start();
        $totalMarkets = 0;

        foreach ($predictions as $p) {
            $scores = [
                'form' => (float) $p->score_form,
                'h2h' => (float) $p->score_h2h,
                'home_away' => (float) $p->score_home_away,
                'league' => (float) $p->score_league,
                'goals' => (float) $p->score_goals,
                'time' => (float) $p->score_time,
                'weather' => (float) $p->score_weather,
                'shots' => (float) $p->score_shots,
                'physical' => (float) $p->score_physical,
            ];

            $candidates = $build->invoke(
                $algo,
                $scores,
                (float) $p->total_score,
                ['id' => $p->home_team_id, 'name' => $p->home_team],
                ['id' => $p->away_team_id, 'name' => $p->away_team],
                0.0, 0.0, 1.2, 1.2
            );

            $markets = $scoring->allMarketsFor($candidates, false, 0.0, $p->home_team, $p->away_team);

            if ($this->option('fresh')) {
                $p->markets()->delete();
            }

            if (! empty($markets)) {
                $rows = array_map(fn (array $m): array => $m + ['status' => 'pending'], $markets);
                $p->markets()->createMany($rows);
                $totalMarkets += count($rows);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ {$predictions->count()} prédictions traitées — {$totalMarkets} marchés créés.");

        return self::SUCCESS;
    }
}
