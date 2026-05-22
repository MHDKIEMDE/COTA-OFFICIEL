<?php

namespace App\Jobs;

use App\Services\FootballApiService;
use App\Services\PredictionAlgorithmService;
use App\Services\RapidApiService;
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

    public function handle(FootballApiService $footballApi, PredictionAlgorithmService $algorithm, RapidApiService $rapidApi): void
    {
        Log::info('GenerateAllPredictionsJob: Début génération prédictions');

        $response = $footballApi->getUpcomingMatches(1);
        $fixtures = $response['response'] ?? [];

        // Fallback DB : si API-Football ne retourne rien, on prend les matchs déjà en base
        if (empty($fixtures)) {
            Log::info('GenerateAllPredictionsJob: API-Football vide → fallback matchs en base');
            $fixtures = $this->buildFixturesFromDb();
        }

        if (empty($fixtures)) {
            Log::warning('GenerateAllPredictionsJob: Aucun match trouvé (API + DB)');
            return;
        }
        $predictions = [];
        $processed  = 0;
        $skipped    = 0;

        Log::info('GenerateAllPredictionsJob: ' . count($fixtures) . ' matchs à traiter');

        // Précharger toutes les prédictions tierces du jour en 1 seul appel API
        $rapidApi->loadDailyThirdPartyPredictions(Carbon::today()->format('Y-m-d'));

        foreach ($fixtures as $fixture) {
            try {
                $prediction = $this->generatePredictionForFixture($fixture, $algorithm, $rapidApi);
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

    private function generatePredictionForFixture(array $fixture, PredictionAlgorithmService $algorithm, RapidApiService $rapidApi): ?Prediction
    {
        $fixtureInfo = $fixture['fixture'] ?? [];
        $fixtureId   = $fixtureInfo['id'] ?? null;

        if (!$fixtureId) return null;

        $homeTeam = $fixture['teams']['home'] ?? [];
        $awayTeam = $fixture['teams']['away'] ?? [];
        $league   = $fixture['league'] ?? [];

        if (!($homeTeam['id'] ?? null) || !($awayTeam['id'] ?? null)) return null;

        $matchDate    = Carbon::parse($fixtureInfo['date'] ?? now());
        $leagueId     = $league['id'] ?? null;
        $leagueName   = $league['name'] ?? '';
        $leagueCountry = $league['country'] ?? '';
        $leagueTier   = $this->resolveLeagueTier($leagueName, $leagueCountry);

        // Exclure les ligues de basse qualité qui ne fournissent pas de données utiles
        $excludedPatterns = ['Friendl', 'Women', 'Female', 'Youth', 'U17', 'U18', 'U19', 'U20', 'U21', 'U23', 'Reserve', 'Amateur'];
        foreach ($excludedPatterns as $pattern) {
            if (stripos($leagueName, $pattern) !== false) {
                return null;
            }
        }

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

        // Enrichir avec les prédictions tierces (déjà en cache → 0 appel réseau)
        $predictionData = $rapidApi->enrichPredictionWithThirdParty(
            $predictionData,
            $homeTeam['name'] ?? '',
            $awayTeam['name'] ?? '',
            $matchDate->format('Y-m-d')
        );

        return Prediction::updateOrCreate(
            ['match_id' => (string) $fixtureId],
            [
                'home_team'          => $homeTeam['name'] ?? 'Unknown',
                'away_team'          => $awayTeam['name'] ?? 'Unknown',
                'home_team_id'       => $homeTeam['id'],
                'away_team_id'       => $awayTeam['id'],
                'competition'        => $league['name'] ?? 'Unknown',
                'competition_id'     => $leagueId,
                'league_tier'        => $leagueTier,
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
                    'third_party'       => $predictionData['third_party'] ?? null,
                    'algorithm_version' => '3.1',
                ]),
                'published_at'       => now(),
                'is_combined_daily'  => false,
                'combined_date'      => null,
                'combined_position'  => null,
            ]
        );
    }

    private function buildFixturesFromDb(): array
    {
        $matches = FootballMatch::whereDate('match_date', Carbon::today())
            ->whereIn('status', ['scheduled', 'live'])
            ->get();

        return $matches->map(function (FootballMatch $m): array {
            // Extraire l'ID numérique depuis le préfixe (apf_123 ou tsdb_123)
            $rawId = preg_replace('/^(apf_|tsdb_)/', '', $m->match_id);

            return [
                'fixture' => [
                    'id'       => $rawId,
                    'date'     => $m->match_date->toIso8601String(),
                    'timezone' => $m->timezone ?? 'UTC',
                    'status'   => ['short' => 'NS'],
                    'venue'    => ['name' => $m->venue_name, 'city' => $m->venue_city ?? null],
                ],
                'teams' => [
                    'home' => ['id' => $m->home_team_id ?? 0, 'name' => $m->home_team],
                    'away' => ['id' => $m->away_team_id ?? 0, 'name' => $m->away_team],
                ],
                'league' => [
                    'id'      => $m->competition_id ?? 0,
                    'name'    => $m->competition ?? 'Unknown',
                    'country' => $m->country ?? 'Unknown',
                ],
                'goals' => ['home' => null, 'away' => null],
                '_source' => 'db',
            ];
        })->values()->toArray();
    }

    private function resolveLeagueTier(string $name, string $country): int
    {
        $tiers     = config('football-api.league_tiers', []);
        $whitelist = config('football-api.tier1_country_whitelist', []);

        $tier = $tiers[$name] ?? 99;

        // Pour les ligues ambiguës (ex: "Premier League" existe dans 50 pays),
        // on n'accorde le tier configuré que si le pays correspond.
        if ($tier === 1 && isset($whitelist[$name]) && $whitelist[$name] !== $country) {
            return 5; // Ligue mineure homonyme
        }

        return $tier;
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
