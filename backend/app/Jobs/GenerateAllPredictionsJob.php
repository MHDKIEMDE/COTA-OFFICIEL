<?php

namespace App\Jobs;

use App\Services\FootballApiService;
use App\Services\PredictionAlgorithmService;
use App\Models\Prediction;
use App\Models\FootballMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Génère les prédictions pour toutes les prochaines 24h via API-Football.
 * Fréquence: 2 fois par jour (8h00 et 20h00)
 */
class GenerateAllPredictionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FootballApiService $footballApi, PredictionAlgorithmService $algorithm): void
    {
        Log::info('GenerateAllPredictionsJob: Début génération prédictions');

        $response = $footballApi->getUpcomingMatches(1);

        if (!$response || empty($response['response'])) {
            Log::warning('GenerateAllPredictionsJob: Aucun match trouvé');
            return;
        }

        $fixtures   = $response['response'];
        $predictions = [];
        $processed  = 0;
        $skipped    = 0;

        Log::info('GenerateAllPredictionsJob: ' . count($fixtures) . ' matchs à traiter');

        foreach ($fixtures as $fixture) {
            try {
                $prediction = $this->generatePredictionForFixture($fixture, $algorithm);
                if ($prediction) {
                    $predictions[] = $prediction;
                    $processed++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                Log::error('GenerateAllPredictionsJob: Erreur', [
                    'fixture_id' => $fixture['fixture']['id'] ?? 'unknown',
                    'error'      => $e->getMessage(),
                ]);
                $skipped++;
            }
        }

        Log::info('GenerateAllPredictionsJob: Traitement terminé', [
            'processed' => $processed,
            'skipped'   => $skipped,
        ]);

        $this->selectCombinedDaily($predictions, Carbon::today());
        $this->cleanOldPredictions();
    }

    private function generatePredictionForFixture(array $fixture, PredictionAlgorithmService $algorithm): ?Prediction
    {
        $fixtureInfo = $fixture['fixture'] ?? [];
        $fixtureId   = $fixtureInfo['id'] ?? null;

        if (!$fixtureId) return null;

        $homeTeam = $fixture['teams']['home'] ?? [];
        $awayTeam = $fixture['teams']['away'] ?? [];
        $league   = $fixture['league'] ?? [];

        if (!($homeTeam['id'] ?? null) || !($awayTeam['id'] ?? null)) return null;

        $matchDate = Carbon::parse($fixtureInfo['date'] ?? now());

        // Sauvegarder le match en base
        FootballMatch::updateOrCreate(
            ['match_id' => (string) $fixtureId],
            [
                'home_team_id'   => $homeTeam['id'],
                'away_team_id'   => $awayTeam['id'],
                'competition_id' => $league['id'] ?? null,
                'home_team'      => $homeTeam['name'] ?? 'Unknown',
                'away_team'      => $awayTeam['name'] ?? 'Unknown',
                'competition'    => $league['name'] ?? 'Unknown',
                'country'        => $league['country'] ?? 'Unknown',
                'match_date'     => $matchDate,
                'match_time'     => $matchDate->format('H:i'),
                'status'         => 'scheduled',
                'venue_name'     => $fixtureInfo['venue']['name'] ?? null,
                'venue_city'     => $fixtureInfo['venue']['city'] ?? null,
            ]
        );

        $predictionData = $algorithm->generatePrediction($fixture);

        if (!$predictionData['should_publish']) {
            return null;
        }

        return Prediction::updateOrCreate(
            ['match_id' => (string) $fixtureId],
            [
                'home_team'          => $homeTeam['name'] ?? 'Unknown',
                'away_team'          => $awayTeam['name'] ?? 'Unknown',
                'home_team_id'       => $homeTeam['id'],
                'away_team_id'       => $awayTeam['id'],
                'competition'        => $league['name'] ?? 'Unknown',
                'competition_id'     => $league['id'] ?? null,
                'country'            => $league['country'] ?? 'Unknown',
                'match_date'         => $matchDate,
                'match_time'         => $matchDate->format('H:i'),
                'bet_type'           => $predictionData['type'] ?? '1X2',
                'prediction'         => $predictionData['outcome'] ?? '1',
                'odds'               => $predictionData['odds'] ?? '1.50',
                'confidence_stars'   => $predictionData['stars'] ?? 1,
                'score_form'         => $predictionData['scores']['form'] ?? 0,
                'score_h2h'          => $predictionData['scores']['h2h'] ?? 0,
                'score_home_away'    => $predictionData['scores']['home_away'] ?? 0,
                'score_league'       => $predictionData['scores']['league'] ?? 0,
                'score_goals'        => $predictionData['scores']['goals'] ?? 0,
                'score_time'         => $predictionData['scores']['time'] ?? 0,
                'score_weather'      => $predictionData['scores']['weather'] ?? 0,
                'score_shots'        => $predictionData['scores']['shots'] ?? 0,
                'score_physical'     => $predictionData['scores']['physical'] ?? 0,
                'total_score'        => $predictionData['confidence'] ?? 0,
                'status'             => 'pending',
                'is_published'       => true,
                'is_premium'         => $predictionData['is_premium'] ?? false,
                'analysis_details'   => json_encode([
                    'reasoning'         => $predictionData['reasoning'] ?? '',
                    'scores_breakdown'  => $predictionData['scores'] ?? [],
                    'algorithm_version' => '3.0',
                ]),
                'published_at'       => now(),
                'is_combined_daily'  => false,
                'combined_date'      => null,
                'combined_position'  => null,
            ]
        );
    }

    private function selectCombinedDaily(array $predictions, Carbon $date): void
    {
        DB::table('predictions')
            ->whereDate('combined_date', $date->toDateString())
            ->update(['is_combined_daily' => false, 'combined_position' => null]);

        $top = collect($predictions)
            ->filter(fn($p) => $p->total_score >= 80)
            ->sortByDesc('total_score')
            ->take(5)
            ->values();

        if ($top->isEmpty()) return;

        foreach ($top as $index => $prediction) {
            $prediction->update([
                'is_combined_daily' => true,
                'combined_date'     => $date->toDateString(),
                'combined_position' => $index + 1,
            ]);
        }

        Log::info('GenerateAllPredictionsJob: Combiné premium sélectionné', ['count' => $top->count()]);
    }

    private function cleanOldPredictions(): void
    {
        $deleted = DB::table('predictions')
            ->where('match_date', '<', Carbon::now()->subDays(30))
            ->where('status', '!=', 'pending')
            ->delete();

        Log::info('GenerateAllPredictionsJob: Anciens pronostics nettoyés', ['deleted' => $deleted]);
    }
}
