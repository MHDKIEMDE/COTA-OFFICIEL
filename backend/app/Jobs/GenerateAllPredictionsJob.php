<?php

namespace App\Jobs;

use App\Services\FootballApiService;
use App\Services\HybridationService;
use App\Services\PredictionAlgorithmService;
use App\Services\PredictionAnalysisService;
use App\Services\RapidApiService;
use App\Services\ValueBettingService;
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

    private ValueBettingService $valueBetting;
    private PredictionAnalysisService $analysisService;
    private HybridationService $hybridation;

    public function handle(FootballApiService $footballApi, PredictionAlgorithmService $algorithm, RapidApiService $rapidApi, ValueBettingService $valueBetting, PredictionAnalysisService $analysisService, HybridationService $hybridation): void
    {
        $this->valueBetting    = $valueBetting;
        $this->analysisService = $analysisService;
        $this->hybridation     = $hybridation;
        Log::info('GenerateAllPredictionsJob: Début génération prédictions');

        // ── Vérifier le quota API-Football ───────────────────────────────────
        $quotaOk = $this->hasApiQuota($footballApi);

        if ($quotaOk) {
            // MODE COMPLET : algo 9 critères + données réelles API-Football
            $this->generateWithFullAlgorithm($footballApi, $algorithm, $rapidApi);
        } else {
            // MODE FALLBACK : prédictions tierces RapidAPI directement
            Log::info('GenerateAllPredictionsJob: Quota épuisé → fallback prédictions tierces RapidAPI');
            $this->generateFromThirdPartyApi($rapidApi);
        }
    }

    // ── Vérification quota ────────────────────────────────────────────────────

    private function hasApiQuota(FootballApiService $footballApi): bool
    {
        try {
            $stats     = $footballApi->getUsageStats();
            $remaining = $stats['daily']['remaining'] ?? 0;
            Log::info('GenerateAllPredictionsJob: Quota API-Football', ['remaining' => $remaining]);
            return $remaining > 5;
        } catch (\Throwable $e) {
            Log::warning('GenerateAllPredictionsJob: Impossible de lire le quota', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // ── MODE COMPLET — algo 9 critères ───────────────────────────────────────

    private function generateWithFullAlgorithm(FootballApiService $footballApi, PredictionAlgorithmService $algorithm, RapidApiService $rapidApi): void
    {
        $response = $footballApi->getUpcomingMatches(1);
        $fixtures = $response['response'] ?? [];

        if (empty($fixtures)) {
            Log::info('GenerateAllPredictionsJob: API-Football vide → fallback matchs en base');
            $fixtures = $this->buildFixturesFromDb();
        }

        if (empty($fixtures)) {
            Log::warning('GenerateAllPredictionsJob: Aucun match trouvé → fallback prédictions tierces');
            $this->generateFromThirdPartyApi($rapidApi);
            return;
        }

        $predictions = [];
        $processed   = 0;
        $skipped     = 0;

        Log::info('GenerateAllPredictionsJob: ' . count($fixtures) . ' matchs à traiter (algo complet)');

        $rapidApi->loadDailyThirdPartyPredictions(Carbon::today()->format('Y-m-d'));

        $liveOdds  = $rapidApi->get1xBetLiveOdds();
        $oddsIndex = [];
        foreach ($liveOdds as $entry) {
            $key = strtolower($entry['match'] ?? '');
            if ($key) $oddsIndex[$key] = $entry;
        }

        foreach ($fixtures as $fixture) {
            try {
                $prediction = $this->generatePredictionForFixture($fixture, $algorithm, $rapidApi, $oddsIndex);
                if ($prediction) {
                    $predictions[] = $prediction;
                    $processed++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                Log::error('GenerateAllPredictionsJob: Erreur fixture', [
                    'fixture_id' => $fixture['fixture']['id'] ?? 'unknown',
                    'error'      => $e->getMessage(),
                ]);
                $skipped++;
            }
        }

        Log::info('GenerateAllPredictionsJob: Algo complet terminé', compact('processed', 'skipped'));

        $this->selectCombinedDaily($predictions, Carbon::today());
        $this->cleanOldPredictions();
    }

    // ── MODE FALLBACK — prédictions tierces RapidAPI ─────────────────────────

    private function generateFromThirdPartyApi(RapidApiService $rapidApi): void
    {
        $today       = Carbon::today()->format('Y-m-d');
        $federations = ['UEFA', 'CONMEBOL', 'AFC', 'CAF', 'CONCACAF', 'OFC'];
        $liveOdds    = $rapidApi->get1xBetLiveOdds();
        $oddsIndex   = [];
        foreach ($liveOdds as $entry) {
            $key = strtolower($entry['match'] ?? '');
            if ($key) $oddsIndex[$key] = $entry;
        }

        $saved   = 0;
        $skipped = 0;

        foreach ($federations as $federation) {
            try {
                $items = $this->fetchThirdPartyPredictions($rapidApi, $today, $federation);

                foreach ($items as $item) {
                    try {
                        $this->saveThirdPartyPrediction($item, $federation, $oddsIndex);
                        $saved++;
                    } catch (\Throwable $e) {
                        Log::debug('Fallback: erreur sauvegarde', ['error' => $e->getMessage()]);
                        $skipped++;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Fallback: erreur federation {$federation}", ['error' => $e->getMessage()]);
            }
        }

        Log::info('GenerateAllPredictionsJob: Fallback terminé', compact('saved', 'skipped'));

        $allPreds    = Prediction::whereDate('match_date', Carbon::today())->get();
        $predictions = $allPreds->all();
        $this->selectCombinedDaily($predictions, Carbon::today());
        $this->cleanOldPredictions();
    }

    private function fetchThirdPartyPredictions(RapidApiService $rapidApi, string $date, string $federation): array
    {
        $cacheKey = "rapidapi_raw_pred_{$date}_{$federation}";
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 21600, function () use ($rapidApi, $date, $federation) {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-rapidapi-host' => 'football-prediction-api.p.rapidapi.com',
                'x-rapidapi-key'  => env('RAPIDAPI_PREDICTION_KEY', env('RAPIDAPI_KEY', '')),
            ])->timeout(12)->get('https://football-prediction-api.p.rapidapi.com/api/v2/predictions', [
                'market'     => 'classic',
                'iso_date'   => $date,
                'federation' => $federation,
            ]);

            if (!$response->successful()) return [];
            return $response->json('data', []);
        });
    }

    private function saveThirdPartyPrediction(array $item, string $federation, array $oddsIndex): void
    {
        $homeTeam   = $item['home_team'] ?? null;
        $awayTeam   = $item['away_team'] ?? null;
        $prediction = $item['prediction'] ?? '1';  // 1/X/2/1X/X2/12
        $matchId    = 'rapi_' . ($item['id'] ?? md5($homeTeam . $awayTeam));

        if (!$homeTeam || !$awayTeam) return;

        // Exclure matchs déjà générés par l'algo complet aujourd'hui
        if (Prediction::where('match_id', $matchId)->whereDate('match_date', Carbon::today())->exists()) return;

        $matchDate = Carbon::parse($item['start_date'] ?? now());

        // Sélectionner le meilleur marché parmi les 6 disponibles (1, X, 2, 1X, X2, 12)
        $apiOdds = $item['odds'] ?? [];
        [$betType, $outcome, $apiOdd] = $this->selectBestMarket($prediction, $apiOdds);

        // Cote : priorité 1xBet live, puis API tierce (déjà sélectionnée), puis défaut
        $odds = $apiOdd > 1.0
            ? round($apiOdd, 2)
            : $this->resolveOdds($item, $outcome, $oddsIndex, $homeTeam, $awayTeam);

        // Confiance basée sur les probabilités tierces si disponibles
        $probs      = $item['probabilities'] ?? [];
        $confidence = $this->estimateConfidenceFromThirdParty($prediction, $probs, $item);
        $stars      = $this->starsFromConfidence($confidence);

        // Value Betting
        $vb = $this->valueBetting->calculate($confidence, $odds > 1.0 ? $odds : 1.50);

        // Tier ligue
        $competition = $item['competition_name'] ?? 'Unknown';
        $leagueTier  = $this->resolveLeagueTier($competition, $federation);

        Prediction::updateOrCreate(
            ['match_id' => $matchId],
            [
                'home_team'        => $homeTeam,
                'away_team'        => $awayTeam,
                'home_team_id'     => 0,
                'away_team_id'     => 0,
                'competition'      => $competition,
                'competition_id'   => 0,
                'country'          => $item['competition_cluster'] ?? $federation,
                'match_date'       => $matchDate,
                'match_time'       => $matchDate->format('H:i'),
                'bet_type'         => $betType,
                'prediction'       => $outcome,
                'odds'             => $odds,
                'confidence_stars' => $stars,
                'total_score'      => $confidence,
                'score_form'       => 0,
                'score_h2h'        => 0,
                'score_home_away'  => 0,
                'score_league'     => 0,
                'score_goals'      => 0,
                'score_time'       => 0,
                'score_weather'    => 0,
                'score_shots'      => 0,
                'score_physical'   => 0,
                'league_tier'      => $leagueTier,
                'value_score'      => $vb['value_score'],
                'kelly_fraction'   => $vb['kelly_fraction'],
                'ev_positive'      => $vb['ev_positive'],
                'status'           => 'pending',
                'is_published'     => true,
                'is_premium'       => $stars >= 3,
                'analysis_details' => json_encode([
                    'reasoning'         => "Prédiction issue de l'API tierce (quota API-Football épuisé).",
                    'third_party'       => [
                        'prediction' => $prediction,
                        'source'     => 'football-prediction-api',
                        'federation' => $federation,
                    ],
                    'algorithm_version' => 'fallback-v1',
                ]),
                'published_at'     => now(),
                'is_combined_daily' => false,
            ]
        );
    }

    /**
     * Choisit le meilleur marché à partir des cotes disponibles.
     *
     * Logique : on sélectionne le marché qui a la meilleure probabilité implicite
     * (= cote la plus basse) parmi tous les marchés cohérents avec le pronostic tierce.
     * Exemple : tierce dit "1" → on peut jouer 1X2:1, Double Chance:1X ou Double Chance:12
     *           On choisit celui dont la cote donne le plus de confiance.
     */
    private function selectBestMarket(string $thirdPartyPrediction, array $apiOdds): array
    {
        $pred = strtoupper($thirdPartyPrediction);

        // Marchés possibles selon le sens du pronostic
        $candidates = match($pred) {
            '1'  => [
                ['1X2',          '1',  $apiOdds['1']  ?? 0],
                ['Double Chance','1X', $apiOdds['1X'] ?? 0],
                ['Double Chance','12', $apiOdds['12'] ?? 0],
            ],
            '2'  => [
                ['1X2',          '2',  $apiOdds['2']  ?? 0],
                ['Double Chance','X2', $apiOdds['X2'] ?? 0],
                ['Double Chance','12', $apiOdds['12'] ?? 0],
            ],
            'X'  => [
                ['1X2',          'X',  $apiOdds['X']  ?? 0],
                ['Double Chance','1X', $apiOdds['1X'] ?? 0],
                ['Double Chance','X2', $apiOdds['X2'] ?? 0],
            ],
            '1X' => [
                ['Double Chance','1X', $apiOdds['1X'] ?? 0],
                ['1X2',          '1',  $apiOdds['1']  ?? 0],
            ],
            'X2' => [
                ['Double Chance','X2', $apiOdds['X2'] ?? 0],
                ['1X2',          '2',  $apiOdds['2']  ?? 0],
            ],
            '12' => [
                ['Double Chance','12', $apiOdds['12'] ?? 0],
                ['1X2',          '1',  $apiOdds['1']  ?? 0],
            ],
            default => [['1X2', '1', 0]],
        };

        // Filtrer les marchés avec cote valide (> 1.0)
        $valid = array_filter($candidates, fn($c) => (float)$c[2] > 1.0);

        if (empty($valid)) {
            // Aucune cote dispo → retourner le premier candidat sans cote
            return [$candidates[0][0], $candidates[0][1], 0.0];
        }

        // Choisir la cote la plus basse (= marché le plus sûr / probabilité implicite la plus haute)
        usort($valid, fn($a, $b) => $a[2] <=> $b[2]);
        $best = reset($valid);

        return [$best[0], $best[1], (float)$best[2]];
    }

    private function resolveOdds(array $item, string $outcome, array $oddsIndex, string $home, string $away): float
    {
        // 1. Cote 1xBet live (si dispo)
        $key      = strtolower("{$home} vs {$away}");
        $realOdds = $oddsIndex[$key] ?? null;
        if ($realOdds) {
            $mapped = match($outcome) {
                '1'  => $realOdds['home'] ?? null,
                '2'  => $realOdds['away'] ?? null,
                'X'  => $realOdds['draw'] ?? null,
                default => null,
            };
            if ($mapped && $mapped > 1.0) return round($mapped, 2);
        }

        // 2. Cote depuis l'API tierce (6 marchés disponibles)
        $apiOdds = $item['odds'] ?? [];
        if (!empty($apiOdds[$outcome]) && (float)$apiOdds[$outcome] > 1.0) {
            return round((float) $apiOdds[$outcome], 2);
        }

        // 3. Cote par défaut selon le marché
        return match($outcome) {
            '1'  => round(1.80 + (mt_rand(0, 60) / 100), 2),
            '2'  => round(2.20 + (mt_rand(0, 80) / 100), 2),
            'X'  => round(3.10 + (mt_rand(0, 60) / 100), 2),
            '1X' => round(1.35 + (mt_rand(0, 30) / 100), 2),
            'X2' => round(1.45 + (mt_rand(0, 30) / 100), 2),
            '12' => round(1.25 + (mt_rand(0, 20) / 100), 2),
            default => 1.80,
        };
    }

    private function estimateConfidenceFromThirdParty(string $prediction, array $probs, array $item = []): float
    {
        // Priorité 1 : probabilités fournies par l'API
        if (!empty($probs)) {
            $dominant = match(strtoupper($prediction)) {
                '1'  => (float) ($probs['home_win'] ?? $probs['1'] ?? 50),
                '2'  => (float) ($probs['away_win'] ?? $probs['2'] ?? 50),
                'X'  => (float) ($probs['draw']     ?? $probs['X'] ?? 33),
                '1X' => max((float)($probs['home_win'] ?? 40), (float)($probs['draw'] ?? 30)),
                'X2' => max((float)($probs['away_win'] ?? 40), (float)($probs['draw'] ?? 30)),
                '12' => max((float)($probs['home_win'] ?? 40), (float)($probs['away_win'] ?? 40)),
                default => 50.0,
            };
            return round(50.0 + ($dominant / 100.0) * 40.0, 1);
        }

        // Priorité 2 : déduire la confiance depuis la cote du marché
        // La probabilité implicite (1/cote) est mappée vers 50–85 pts
        // Cote 1.10 (~91%) → 82 pts (3★) | Cote 1.30 (~77%) → 74 pts (3★)
        // Cote 1.60 (~63%) → 68 pts (2★) | Cote 2.00 (~50%) → 62 pts (2★)
        // Cote 2.50+ (~40%) → 60 pts (2★)
        $apiOdds    = $item['odds'] ?? [];
        $outcomeOdd = (float) ($apiOdds[$prediction] ?? 0);

        if ($outcomeOdd > 1.0) {
            $implied = min(1 / $outcomeOdd, 0.95); // probabilité implicite, cap à 95%
            // Mapper 40%–95% → 50–85 pts (linéaire)
            $score = 50.0 + (($implied - 0.40) / 0.55) * 35.0;
            return round(max(50.0, min(85.0, $score)), 1);
        }

        // Priorité 3 : valeur de base selon le type de marché (sans cotes)
        return match(strtoupper($prediction)) {
            '1X', 'X2', '12' => 63.0 + mt_rand(0, 3),
            '1', '2'         => 61.0 + mt_rand(0, 4),
            'X'              => 60.0 + mt_rand(0, 2),
            default          => 61.0,
        };
    }

    private function starsFromConfidence(float $score): int
    {
        return match(true) {
            $score >= 85 => 4,
            $score >= 70 => 3,
            $score >= 60 => 2,
            default      => 1,
        };
    }

    private function generatePredictionForFixture(array $fixture, PredictionAlgorithmService $algorithm, RapidApiService $rapidApi, array $oddsIndex = []): ?Prediction
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

        // Enrichir avec les prédictions tierces (déjà en cache → 0 appel réseau)
        $predictionData = $rapidApi->enrichPredictionWithThirdParty(
            $predictionData,
            $homeTeam['name'] ?? '',
            $awayTeam['name'] ?? '',
            $matchDate->format('Y-m-d')
        );

        // Hybridation algo + source externe (§8 CDC V2)
        $external       = $predictionData['third_party'] ?? null;
        $predictionData = $this->hybridation->hybridize($predictionData, $external);

        if (!$predictionData['should_publish']) {
            return null;
        }

        // Value Betting
        $vbConf = (float) ($predictionData['confidence'] ?? 60.0);
        $vbOdds = (float) ($predictionData['odds'] ?? 1.50);
        $vb     = $this->valueBetting->calculate($vbConf, $vbOdds);

        // Remplacer la cote algo par la cote 1xBet réelle si disponible
        $oddsKey  = strtolower(($homeTeam['name'] ?? '') . ' vs ' . ($awayTeam['name'] ?? ''));
        $realOdds = $oddsIndex[$oddsKey] ?? null;
        if ($realOdds) {
            $outcome = $predictionData['outcome'] ?? '1';
            $mapped  = match($outcome) {
                '1'    => $realOdds['home'],
                'X'    => $realOdds['draw'],
                '2'    => $realOdds['away'],
                default => null,
            };
            if ($mapped && $mapped > 1.0) {
                $predictionData['odds']        = round($mapped, 2);
                $predictionData['odds_source'] = '1xbet';
            }
        }

        // Si aucune cote réelle disponible → on marque "estimated", sera masqué côté mobile
        if (($predictionData['odds_source'] ?? 'algo') === 'algo') {
            $predictionData['odds_source'] = 'estimated';
        }

        return Prediction::updateOrCreate(
            ['match_id' => (string) $fixtureId],
            [
                'home_team'          => $homeTeam['name'] ?? 'Unknown',
                'away_team'          => $awayTeam['name'] ?? 'Unknown',
                'home_team_logo'     => $homeTeam['logo'] ?? null,
                'away_team_logo'     => $awayTeam['logo'] ?? null,
                'home_team_id'       => $homeTeam['id'],
                'away_team_id'       => $awayTeam['id'],
                'competition'        => $league['name'] ?? 'Unknown',
                'competition_id'     => $leagueId,
                'competition_logo'   => $league['logo'] ?? null,
                'league_tier'        => $leagueTier,
                'country'            => $league['country'] ?? 'Unknown',
                'match_date'         => $matchDate,
                'match_time'         => $matchDate->format('H:i'),
                'bet_type'           => $predictionData['type'] ?? '1X2',
                'bet_market'         => $predictionData['type'] ?? '1X2',
                'engine_used'        => $predictionData['engine'] ?? 'force',
                'market_value_score' => $predictionData['market_value'] ?? null,
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
                'score_algo'         => $predictionData['score_algo']    ?? $predictionData['confidence'] ?? 0,
                'score_externe'      => $predictionData['score_externe'] ?? null,
                'score_publie'       => $predictionData['score_publie']  ?? $predictionData['confidence'] ?? 0,
                'w_ext'              => $predictionData['w_ext']         ?? 0,
                'value_score'        => $vb['value_score'],
                'kelly_fraction'     => $vb['kelly_fraction'],
                'ev_positive'        => $vb['ev_positive'],
                'status'             => 'pending',
                'is_published'       => true,
                'is_premium'         => $predictionData['is_premium'] ?? false,
                'analysis_details'   => json_encode([
                    'reasoning'         => $predictionData['reasoning'] ?? '',
                    'scores_breakdown'  => $predictionData['scores'] ?? [],
                    'engine'            => $predictionData['engine'] ?? 'force',
                    'market_value'      => $predictionData['market_value'] ?? null,
                    'algorithm_version' => '4.0',
                    'odds_source'       => $predictionData['odds_source'] ?? 'algo',
                ]),
                'analysis_text'      => $this->analysisService->generateAnalysis(
                    $predictionData,
                    [
                        'home_team'   => $homeTeam['name'] ?? '',
                        'away_team'   => $awayTeam['name'] ?? '',
                        'competition' => $league['name'] ?? '',
                    ]
                ),
                'analysis_source'    => config('services.llm.provider', 'template'),
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
                    'home' => ['id' => $m->home_team_id ?? 0, 'name' => $m->home_team, 'logo' => $m->home_team_logo ?? null],
                    'away' => ['id' => $m->away_team_id ?? 0, 'name' => $m->away_team, 'logo' => $m->away_team_logo ?? null],
                ],
                'league' => [
                    'id'      => $m->competition_id ?? 0,
                    'name'    => $m->competition ?? 'Unknown',
                    'country' => $m->country ?? 'Unknown',
                    'logo'    => $m->competition_logo ?? null,
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
