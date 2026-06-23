<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service centralisé pour toutes les APIs RapidAPI utilisées dans COTA.
 *
 * APIs gérées :
 * - football-prediction-api   → prédictions tierces (10ème critère algo)
 * - free-football-soccer-videos → highlights vidéo post-match
 * - all-sport-live-stream     → liens streaming live
 * - 1xbet-api-live-odds       → cotes live 1xBet
 * - free-api-live-football-data → données backup ligues/matchs
 */
class RapidApiService
{
    private string $defaultKey;
    private string $predictionKey;
    private string $videosKey;
    private string $livestreamKey;
    private string $oddsKey;
    private string $footballDataKey;

    public function __construct()
    {
        $this->defaultKey      = env('RAPIDAPI_KEY', '');
        $this->predictionKey   = env('RAPIDAPI_PREDICTION_KEY',    $this->defaultKey);
        $this->videosKey       = env('RAPIDAPI_VIDEOS_KEY',        $this->defaultKey);
        $this->livestreamKey   = env('RAPIDAPI_LIVESTREAM_KEY',    $this->defaultKey);
        $this->oddsKey         = env('RAPIDAPI_1XBET_ODDS_KEY',    $this->defaultKey);
        $this->footballDataKey = env('RAPIDAPI_FOOTBALL_DATA_KEY', $this->defaultKey);
    }

    // =========================================================================
    // PRÉDICTIONS TIERCES — football-prediction-api.p.rapidapi.com
    // 1 seul appel API par jour (toutes fédérations) → cache 6h → matching mémoire.
    // =========================================================================

    /**
     * Charger TOUTES les prédictions du jour en 1 appel.
     * Cache 6h — la liste ne change pas entre les appels.
     * On couvre toutes les fédérations en faisant 3 appels parallèles (UEFA, CONMEBOL, autres).
     *
     * @return array<int, array> Liste brute normalisée, indexée par "home|away" (lowercase)
     */
    public function loadDailyThirdPartyPredictions(?string $date = null): array
    {
        $day      = $date ?? now()->format('Y-m-d');
        $cacheKey = 'rapidapi_pred_all_' . $day;

        return Cache::remember($cacheKey, 21600, function () use ($day) {
            $federations = ['UEFA', 'CONMEBOL', 'AFC', 'CAF', 'CONCACAF', 'OFC'];
            $all         = [];

            foreach ($federations as $fed) {
                try {
                    $response = Http::withHeaders([
                        'x-rapidapi-host' => 'football-prediction-api.p.rapidapi.com',
                        'x-rapidapi-key'  => $this->predictionKey,
                    ])->timeout(12)->get('https://football-prediction-api.p.rapidapi.com/api/v2/predictions', [
                        'market'     => 'classic',
                        'iso_date'   => $day,
                        'federation' => $fed,
                    ]);

                    if (!$response->successful()) continue;

                    foreach ($response->json('data', []) as $item) {
                        $key       = strtolower(($item['home_team'] ?? '') . '|' . ($item['away_team'] ?? ''));
                        $all[$key] = $this->normalizePrediction($item);
                    }
                } catch (\Throwable $e) {
                    Log::debug("[RapidApi] federation {$fed} failed: " . $e->getMessage());
                }
            }

            Log::info('[RapidApi] Prédictions tierces chargées', ['count' => count($all), 'date' => $day]);
            return $all;
        });
    }

    /**
     * Récupérer la prédiction tierce pour un match précis.
     * Utilise le cache du jour (1 appel pour tous les matchs).
     *
     * @return array{home_win_pct: float, draw_pct: float, away_win_pct: float, btts: bool|null, over25: bool|null, prediction: string|null}|null
     */
    public function getThirdPartyPrediction(string $homeTeam, string $awayTeam, ?string $date = null): ?array
    {
        $all = $this->loadDailyThirdPartyPredictions($date);

        if (empty($all)) return null;

        $needle = strtolower($homeTeam) . '|' . strtolower($awayTeam);

        // 1. Correspondance exacte
        if (isset($all[$needle])) return $all[$needle];

        // 2. Correspondance partielle (noms tronqués ou accentués différemment)
        foreach ($all as $key => $pred) {
            [$h, $a] = explode('|', $key, 2);
            $homeMatch = str_contains($h, strtolower($homeTeam)) || str_contains(strtolower($homeTeam), $h);
            $awayMatch = str_contains($a, strtolower($awayTeam)) || str_contains(strtolower($awayTeam), $a);
            if ($homeMatch && $awayMatch) return $pred;
        }

        return null;
    }

    private function normalizePrediction(array $item): array
    {
        $probs = $item['probabilities'] ?? [];
        $odds  = $item['odds'] ?? [];
        return [
            'home_win_pct' => (float) ($probs['home_win'] ?? $probs['1'] ?? 0),
            'draw_pct'     => (float) ($probs['draw']     ?? $probs['X'] ?? 0),
            'away_win_pct' => (float) ($probs['away_win'] ?? $probs['2'] ?? 0),
            'btts'         => isset($item['both_teams_to_score']) ? (bool) $item['both_teams_to_score'] : null,
            'over25'       => isset($item['over_2_5'])            ? (bool) $item['over_2_5']            : null,
            'prediction'   => $item['prediction'] ?? null,
            'agreement'    => null, // calculé après coup dans enrichPredictionWithThirdParty()
            'source'       => 'football-prediction-api',
            // Données brutes conservées pour l'import direct (source principale)
            'odds'             => $odds,
            'home_team'        => $item['home_team'] ?? null,
            'away_team'        => $item['away_team'] ?? null,
            'competition_name' => $item['competition_name'] ?? null,
            'competition_cluster' => $item['competition_cluster'] ?? null,
            'start_date'       => $item['start_date'] ?? null,
            'is_expired'       => (bool) ($item['is_expired'] ?? false),
            'ext_id'           => $item['id'] ?? null,
            // Résultat final fourni par l'API une fois le match joué (historique).
            // result = "1 - 1", status = won|lost (déjà calculé par football-prediction-api).
            'result'           => $item['result'] ?? null,
            'result_status'    => $item['status'] ?? null,
        ];
    }

    // =========================================================================
    // HIGHLIGHTS VIDÉO — free-football-soccer-videos.p.rapidapi.com
    // Affichés dans le détail d'un match terminé (côté mobile).
    // =========================================================================

    /**
     * Récupérer les highlights d'un match terminé.
     * Cache 24h — les vidéos ne changent pas une fois publiées.
     *
     * @return array<int, array{title: string, url: string, thumbnail: string|null, embed: string|null}>
     */
    public function getMatchHighlights(string $homeTeam, string $awayTeam): array
    {
        $cacheKey = 'rapidapi_hl_' . md5($homeTeam . '|' . $awayTeam);

        return Cache::remember($cacheKey, 86400, function () use ($homeTeam, $awayTeam) {
            try {
                $response = Http::withHeaders([
                    'x-rapidapi-host' => 'free-football-soccer-videos.p.rapidapi.com',
                    'x-rapidapi-key'  => $this->videosKey,
                ])->timeout(10)->get('https://free-football-soccer-videos.p.rapidapi.com/');

                if (!$response->successful()) {
                    return [];
                }

                $videos = $response->json() ?? [];
                $results = [];

                foreach ($videos as $video) {
                    $title = strtolower($video['title'] ?? '');
                    if (
                        str_contains($title, strtolower($homeTeam)) ||
                        str_contains($title, strtolower($awayTeam))
                    ) {
                        $results[] = [
                            'title'     => $video['title']     ?? '',
                            'url'       => $video['url']       ?? '',
                            'thumbnail' => $video['thumbnail'] ?? null,
                            'embed'     => $video['embed']     ?? null,
                        ];
                    }
                }

                return array_slice($results, 0, 5);
            } catch (\Throwable $e) {
                Log::warning('[RapidApi] getMatchHighlights: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Récupérer les derniers highlights du jour (non filtrés par match).
     * Cache 30 minutes.
     *
     * @return array<int, array{title: string, url: string, thumbnail: string|null}>
     */
    public function getLatestHighlights(int $limit = 10): array
    {
        return Cache::remember('rapidapi_hl_latest', 1800, function () use ($limit) {
            try {
                $response = Http::withHeaders([
                    'x-rapidapi-host' => 'free-football-soccer-videos.p.rapidapi.com',
                    'x-rapidapi-key'  => $this->videosKey,
                ])->timeout(10)->get('https://free-football-soccer-videos.p.rapidapi.com/');

                if (!$response->successful()) {
                    return [];
                }

                $videos = $response->json() ?? [];

                return array_slice(array_map(fn($v) => [
                    'title'     => $v['title']     ?? '',
                    'url'       => $v['url']        ?? '',
                    'thumbnail' => $v['thumbnail']  ?? null,
                    'embed'     => $v['embed']       ?? null,
                    'date'      => $v['date']        ?? null,
                ], $videos), 0, $limit);
            } catch (\Throwable $e) {
                Log::warning('[RapidApi] getLatestHighlights: ' . $e->getMessage());
                return [];
            }
        });
    }

    // =========================================================================
    // STREAMING LIVE — all-sport-live-stream.p.rapidapi.com
    // Liens de streaming pour les matchs en direct.
    // =========================================================================

    /**
     * Récupérer les streams live football en cours.
     * Cache 5 minutes — les streams changent fréquemment.
     *
     * @return array<int, array{match: string, url: string, quality: string|null, language: string|null}>
     */
    public function getLiveStreams(): array
    {
        return Cache::remember('rapidapi_streams_live', 300, function () {
            try {
                $response = Http::withHeaders([
                    'x-rapidapi-host' => 'all-sport-live-stream.p.rapidapi.com',
                    'x-rapidapi-key'  => $this->livestreamKey,
                ])->timeout(10)->get('https://all-sport-live-stream.p.rapidapi.com/football');

                if (!$response->successful()) {
                    return [];
                }

                $streams = $response->json() ?? [];

                return array_map(fn($s) => [
                    'match'    => $s['match']    ?? $s['title']    ?? '',
                    'url'      => $s['url']       ?? $s['stream']   ?? '',
                    'quality'  => $s['quality']   ?? null,
                    'language' => $s['language']  ?? null,
                ], (array) $streams);
            } catch (\Throwable $e) {
                Log::warning('[RapidApi] getLiveStreams: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Récupérer le stream pour un match spécifique.
     *
     * @return array{url: string, quality: string|null}|null
     */
    public function getStreamForMatch(string $homeTeam, string $awayTeam): ?array
    {
        $streams = $this->getLiveStreams();

        foreach ($streams as $stream) {
            $title = strtolower($stream['match'] ?? '');
            if (
                str_contains($title, strtolower($homeTeam)) ||
                str_contains($title, strtolower($awayTeam))
            ) {
                return $stream;
            }
        }

        return null;
    }

    // =========================================================================
    // COTES LIVE 1XBET — 1xbet-api-live-odds.p.rapidapi.com
    // =========================================================================

    /**
     * Récupérer les cotes live 1xBet pour le football.
     * Cache 2 minutes — les cotes bougent constamment.
     *
     * @return array<int, array{match: string, home: float, draw: float, away: float}>
     */
    public function get1xBetLiveOdds(): array
    {
        return Cache::remember('rapidapi_1xbet_odds', 120, function () {
            try {
                $response = Http::withHeaders([
                    'x-rapidapi-host' => '1xbet-api-live-odds.p.rapidapi.com',
                    'x-rapidapi-key'  => $this->oddsKey,
                ])->timeout(10)->get('https://1xbet-api-live-odds.p.rapidapi.com/live');

                if (!$response->successful()) {
                    return [];
                }

                $matches = $response->json() ?? [];

                return array_map(fn($m) => [
                    'match' => ($m['home'] ?? '') . ' vs ' . ($m['away'] ?? ''),
                    'home'  => (float) ($m['odds']['1'] ?? $m['home_odds'] ?? 0),
                    'draw'  => (float) ($m['odds']['X'] ?? $m['draw_odds'] ?? 0),
                    'away'  => (float) ($m['odds']['2'] ?? $m['away_odds'] ?? 0),
                ], (array) $matches);
            } catch (\Throwable $e) {
                Log::warning('[RapidApi] get1xBetLiveOdds: ' . $e->getMessage());
                return [];
            }
        });
    }

    // =========================================================================
    // DONNÉES FOOTBALL BACKUP — free-api-live-football-data.p.rapidapi.com
    // Utilisé en fallback si API-Football est hors quota.
    // =========================================================================

    /**
     * Scores live de relais — free-api-live-football-data (RapidAPI).
     * Utilisé quand API-Football a épuisé son quota journalier.
     * Quota séparé d'API-Football, cache court (60s).
     *
     * Retourne le format normalisé attendu par MatchController::live.
     *
     * @return array<int, array>
     */
    public function getLiveMatchesBackup(): array
    {
        return Cache::remember('rapidapi_live_backup', 60, function () {
            try {
                $response = Http::withHeaders([
                    'x-rapidapi-host' => 'free-api-live-football-data.p.rapidapi.com',
                    'x-rapidapi-key'  => $this->footballDataKey,
                ])->timeout(12)->get('https://free-api-live-football-data.p.rapidapi.com/football-current-live');

                if (!$response->successful()) {
                    return [];
                }

                $live = $response->json('response.live', []) ?? [];
                $out  = [];

                foreach ($live as $m) {
                    $st       = $m['status'] ?? [];
                    $finished = (bool) ($st['finished'] ?? false);
                    $ongoing  = (bool) ($st['ongoing'] ?? false);
                    $minuteStr = $st['liveTime']['long'] ?? null; // "33:26"
                    $minute    = $minuteStr ? (int) explode(':', $minuteStr)[0] : null;

                    $out[] = [
                        'id'               => (string) ($m['id'] ?? ''),
                        'start_time'       => $st['utcTime'] ?? null,
                        'home_team'        => $m['home']['name'] ?? 'N/A',
                        'away_team'        => $m['away']['name'] ?? 'N/A',
                        'home_team_logo'   => null,
                        'away_team_logo'   => null,
                        'home_score'       => $m['home']['score'] ?? null,
                        'away_score'       => $m['away']['score'] ?? null,
                        'competition'      => $m['leagueName'] ?? $m['tournamentStage'] ?? 'N/A',
                        'competition_id'   => (string) ($m['leagueId'] ?? ''),
                        'competition_logo' => null,
                        'country'          => null,
                        'status'           => $finished ? 'finished' : ($ongoing ? 'live' : 'scheduled'),
                        'match_status'     => $st['liveTime']['short'] ?? null,
                        'elapsed_time'     => $minute,
                        'venue'            => null,
                    ];
                }

                Log::info('[RapidApi] live backup', ['count' => count($out)]);
                return $out;
            } catch (\Throwable $e) {
                Log::warning('[RapidApi] getLiveMatchesBackup: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Récupérer les ligues disponibles (backup).
     * Cache 24h.
     */
    public function getLeaguesBackup(): array
    {
        return Cache::remember('rapidapi_leagues_backup', 86400, function () {
            try {
                $response = Http::withHeaders([
                    'x-rapidapi-host' => 'free-api-live-football-data.p.rapidapi.com',
                    'x-rapidapi-key'  => $this->footballDataKey,
                ])->timeout(10)->get('https://free-api-live-football-data.p.rapidapi.com/football-get-all-leagues');

                if (!$response->successful()) {
                    return [];
                }

                return $response->json('response', $response->json() ?? []);
            } catch (\Throwable $e) {
                Log::warning('[RapidApi] getLeaguesBackup: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Score de confiance additionnel basé sur la prédiction tierce (0–7 pts).
     * Utilisé comme 10ème critère dans PredictionAlgorithmService.
     *
     * Logique : si la prédiction tierce confirme notre outcome → bonus max.
     *           si elle contredit → pénalité. Absent → 0 (neutre).
     */
    public function calculateThirdPartyScore(
        string $homeTeam,
        string $awayTeam,
        string $ourOutcome
    ): float {
        $maxScore = 7.0;
        $pred = $this->getThirdPartyPrediction($homeTeam, $awayTeam);

        if ($pred === null) {
            return 0.0; // pas de données = neutre
        }

        $thirdPartyOutcome = $pred['prediction'] ?? null;

        // Correspondance exacte avec notre pronostic → plein bonus
        if ($thirdPartyOutcome && strtoupper($thirdPartyOutcome) === strtoupper($ourOutcome)) {
            return $maxScore;
        }

        // Probabilité dominante conforme à notre sens → bonus partiel
        $homeWin = $pred['home_win_pct'];
        $draw    = $pred['draw_pct'];
        $awayWin = $pred['away_win_pct'];

        $dominant = match(true) {
            $homeWin > $draw && $homeWin > $awayWin => '1',
            $awayWin > $homeWin && $awayWin > $draw => '2',
            default                                  => 'X',
        };

        // Mapper notre outcome vers 1/X/2
        $ourSense = match(true) {
            str_contains(strtoupper($ourOutcome), 'HOME') || $ourOutcome === '1' => '1',
            str_contains(strtoupper($ourOutcome), 'AWAY') || $ourOutcome === '2' => '2',
            $ourOutcome === 'X' || str_contains(strtoupper($ourOutcome), 'DRAW') => 'X',
            default => null,
        };

        if ($ourSense === null) {
            return $maxScore * 0.5; // marché différent (BTTS, Over…) → neutre positif
        }

        if ($dominant === $ourSense) {
            // Bonus proportionnel à la force de la probabilité dominante
            $dominantPct = match($dominant) {
                '1' => $homeWin,
                '2' => $awayWin,
                'X' => $draw,
            };
            return round($maxScore * min($dominantPct / 60.0, 1.0), 2);
        }

        // Contradiction → légère pénalité (réduire notre score total de ~3 pts max)
        return max(0.0, $maxScore * 0.1);
    }

    // =========================================================================
    // ENRICHISSEMENT — ajoute les données tierces à une prédiction COTA
    // Appelé par GenerateAllPredictionsJob après generatePrediction().
    // =========================================================================

    /**
     * Enrichir une prédiction COTA avec les données de l'API tierce.
     *
     * Ajoute dans analysis_details :
     *   - third_party.prediction   : pronostic tierce (1/X/2)
     *   - third_party.home_win_pct : % victoire domicile
     *   - third_party.draw_pct     : % match nul
     *   - third_party.away_win_pct : % victoire extérieur
     *   - third_party.btts         : les deux équipes marquent ?
     *   - third_party.over25       : plus de 2.5 buts ?
     *   - third_party.agreement    : "confirmed" | "neutral" | "contradicts"
     *   - third_party.source       : 'football-prediction-api'
     *
     * @param array  $cotaPrediction  Résultat de PredictionAlgorithmService::generatePrediction()
     * @param string $homeTeam        Nom équipe domicile
     * @param string $awayTeam        Nom équipe extérieur
     * @param string|null $date       Date du match (Y-m-d)
     * @return array                  Même tableau enrichi
     */
    public function enrichPredictionWithThirdParty(
        array  $cotaPrediction,
        string $homeTeam,
        string $awayTeam,
        ?string $date = null
    ): array {
        $third = $this->getThirdPartyPrediction($homeTeam, $awayTeam, $date);

        if ($third === null) {
            $cotaPrediction['third_party'] = null;
            return $cotaPrediction;
        }

        // Calculer l'accord entre notre pronostic et le tierce
        $ourOutcome   = $cotaPrediction['outcome'] ?? '';
        $thirdOutcome = strtoupper($third['prediction'] ?? '');

        $ourSense = match(true) {
            str_contains(strtoupper($ourOutcome), 'HOME') || $ourOutcome === '1' => '1',
            str_contains(strtoupper($ourOutcome), 'AWAY') || $ourOutcome === '2' => '2',
            $ourOutcome === 'X' || str_contains(strtoupper($ourOutcome), 'DRAW') => 'X',
            default => null,
        };

        $agreement = match(true) {
            $ourSense === null                   => 'neutral',
            $thirdOutcome === $ourSense          => 'confirmed',
            $thirdOutcome !== '' && $thirdOutcome !== $ourSense => 'contradicts',
            default                              => 'neutral',
        };

        $third['agreement'] = $agreement;

        $cotaPrediction['third_party'] = $third;

        return $cotaPrediction;
    }
}
