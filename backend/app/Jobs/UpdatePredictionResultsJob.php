<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job pour mettre à jour les résultats des prédictions
 *
 * Ce job est exécuté toutes les heures pour vérifier les matchs terminés
 * et marquer les prédictions comme won/lost/void.
 */
class UpdatePredictionResultsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('🎯 UpdatePredictionResultsJob: Mise à jour des résultats des prédictions');

        $won = 0;
        $lost = 0;
        $void = 0;

        try {
            // Récupérer toutes les prédictions en attente avec scores finaux
            $predictions = DB::table('predictions')
                ->where('status', 'pending')
                ->where('is_published', true)
                ->whereNotNull('home_score')
                ->whereNotNull('away_score')
                ->whereNotNull('prediction')
                ->where('match_date', '<', now()) // Matchs passés
                ->get();

            Log::info("📊 " . count($predictions) . " prédictions à vérifier");

            foreach ($predictions as $prediction) {
                try {
                    $result = $this->checkPrediction($prediction);

                    DB::table('predictions')
                        ->where('id', $prediction->id)
                        ->update([
                            'status' => $result,
                            'updated_at' => now(),
                        ]);

                    if ($result === 'won') {
                        $won++;
                    } elseif ($result === 'lost') {
                        $lost++;
                    } else {
                        $void++;
                    }

                    Log::debug("✅ Prédiction #{$prediction->id} marquée comme: {$result}");

                } catch (\Exception $e) {
                    Log::error("❌ Erreur vérification prédiction #{$prediction->id}: " . $e->getMessage());
                }
            }

            // Calculer le taux de réussite
            $total = $won + $lost;
            $winRate = $total > 0 ? round(($won / $total) * 100, 2) : 0;

            Log::info("✅ UpdatePredictionResultsJob terminé: {$won} gagnées, {$lost} perdues, {$void} annulées (Win rate: {$winRate}%)");

        } catch (\Exception $e) {
            Log::error("❌ UpdatePredictionResultsJob échoué: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vérifier si une prédiction est gagnée, perdue ou annulée
     *
     * @param object $prediction
     * @return string won|lost|void
     */
    private function checkPrediction(object $prediction): string
    {
        $betType = $prediction->bet_type;
        $predictedOutcome = $prediction->prediction;
        $homeScore = (int) $prediction->home_score;
        $awayScore = (int) $prediction->away_score;

        switch ($betType) {
            case '1X2':
                return $this->check1X2($predictedOutcome, $homeScore, $awayScore);

            case 'BTTS':
                return $this->checkBTTS($predictedOutcome, $homeScore, $awayScore);

            case 'Over/Under':
                return $this->checkOverUnder($predictedOutcome, $homeScore, $awayScore);

            case 'Double Chance':
                return $this->checkDoubleChance($predictedOutcome, $homeScore, $awayScore);

            case 'Handicap':
                return $this->checkHandicap($predictedOutcome, $homeScore, $awayScore);

            default:
                Log::warning("⚠️  Type de pari non reconnu: {$betType}");
                return 'void';
        }
    }

    /**
     * Vérifier une prédiction 1X2
     */
    private function check1X2(string $prediction, int $homeScore, int $awayScore): string
    {
        if ($prediction === '1' && $homeScore > $awayScore) {
            return 'won';
        }
        if ($prediction === 'X' && $homeScore === $awayScore) {
            return 'won';
        }
        if ($prediction === '2' && $awayScore > $homeScore) {
            return 'won';
        }

        return 'lost';
    }

    /**
     * Vérifier une prédiction BTTS (Both Teams To Score)
     */
    private function checkBTTS(string $prediction, int $homeScore, int $awayScore): string
    {
        $bothScored = $homeScore > 0 && $awayScore > 0;

        if ($prediction === 'Yes' && $bothScored) {
            return 'won';
        }
        if ($prediction === 'No' && !$bothScored) {
            return 'won';
        }

        return 'lost';
    }

    /**
     * Vérifier une prédiction Over/Under
     */
    private function checkOverUnder(string $prediction, int $homeScore, int $awayScore): string
    {
        $totalGoals = $homeScore + $awayScore;

        // Extraire la ligne (ex: "Over 2.5" -> 2.5)
        preg_match('/(\d+\.?\d*)/', $prediction, $matches);
        $line = isset($matches[1]) ? (float) $matches[1] : 2.5;

        if (str_starts_with($prediction, 'Over') && $totalGoals > $line) {
            return 'won';
        }
        if (str_starts_with($prediction, 'Under') && $totalGoals < $line) {
            return 'won';
        }

        // Si exact (ex: 2.5 et total = 2), c'est remboursé
        if ($totalGoals == $line) {
            return 'void';
        }

        return 'lost';
    }

    /**
     * Vérifier une prédiction Double Chance
     */
    private function checkDoubleChance(string $prediction, int $homeScore, int $awayScore): string
    {
        if ($prediction === '1X' && $homeScore >= $awayScore) {
            return 'won';
        }
        if ($prediction === 'X2' && $awayScore >= $homeScore) {
            return 'won';
        }
        if ($prediction === '12' && $homeScore !== $awayScore) {
            return 'won';
        }

        return 'lost';
    }

    /**
     * Vérifier une prédiction Handicap
     */
    private function checkHandicap(string $prediction, int $homeScore, int $awayScore): string
    {
        // Format: "Home -1.5" ou "Away +1.5"
        preg_match('/(Home|Away)\s*([+-]?\d+\.?\d*)/', $prediction, $matches);

        if (count($matches) < 3) {
            return 'void';
        }

        $team = $matches[1];
        $handicap = (float) $matches[2];

        if ($team === 'Home') {
            $adjustedHomeScore = $homeScore + $handicap;
            return $adjustedHomeScore > $awayScore ? 'won' : 'lost';
        } else {
            $adjustedAwayScore = $awayScore + $handicap;
            return $adjustedAwayScore > $homeScore ? 'won' : 'lost';
        }
    }
}
