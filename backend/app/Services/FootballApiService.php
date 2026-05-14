<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FootballApiService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected array $rateLimits;

    public function __construct()
    {
        $this->apiKey = config('football-api.api_key');
        $this->baseUrl = config('football-api.base_url');
        $this->timeout = config('football-api.timeout');

        $plan = config('football-api.current_plan');
        $this->rateLimits = config("football-api.rate_limits.{$plan}");
    }

    /**
     * Effectuer une requête à l'API-Football
     */
    protected function makeRequest(string $endpoint, array $params = [], int $cacheTtl = 0): ?array
    {
        try {
            // Vérifier les limites de rate
            $this->checkRateLimit();

            // Générer la clé de cache
            $cacheKey = $this->generateCacheKey($endpoint, $params);

            // Essayer de récupérer depuis le cache
            if ($cacheTtl > 0 && config('football-api.cache.enabled')) {
                $cached = Cache::get($cacheKey);
                if ($cached) {
                    Log::info("API-Football: Cache hit for {$endpoint}");
                    return $cached;
                }
            }

            // Faire la requête
            Log::info("API-Football: Making request to {$endpoint}", $params);

            $response = Http::withHeaders([
                'x-rapidapi-key' => $this->apiKey,
                'x-rapidapi-host' => parse_url($this->baseUrl, PHP_URL_HOST),
            ])
            ->timeout($this->timeout)
            ->get($this->baseUrl . $endpoint, $params);

            if (!$response->successful()) {
                Log::error("API-Football error: " . $response->status(), [
                    'endpoint' => $endpoint,
                    'response' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            // Vérifier la réponse
            if (!isset($data['response'])) {
                Log::error("API-Football: Invalid response format", $data);
                return null;
            }

            // Incrémenter le compteur de requêtes
            $this->incrementRequestCount();

            // Mettre en cache si nécessaire
            if ($cacheTtl > 0 && config('football-api.cache.enabled')) {
                Cache::put($cacheKey, $data, $cacheTtl);
            }

            return $data;

        } catch (\Exception $e) {
            Log::error("API-Football exception: " . $e->getMessage(), [
                'endpoint' => $endpoint,
                'params' => $params,
            ]);
            return null;
        }
    }

    /**
     * Récupérer les matchs à venir
     * Utilise le paramètre `date` pour aujourd'hui (compatible plan Free).
     * Pour plusieurs jours, fait une requête par jour.
     */
    public function getUpcomingMatches(int $days = 1, ?int $leagueId = null): ?array
    {
        // Pour un seul jour, utilise `date` (plus simple et compatible Free)
        if ($days <= 1) {
            $params = [
                'date'     => Carbon::today()->format('Y-m-d'),
                'timezone' => config('football-api.timezone'),
            ];

            if ($leagueId) {
                $params['league'] = $leagueId;
            }

            $cacheTtl = config('football-api.cache.ttl.fixtures');

            return $this->makeRequest('/fixtures', $params, $cacheTtl);
        }

        // Pour plusieurs jours, agrège les résultats jour par jour
        $allFixtures = [];
        for ($i = 0; $i < $days; $i++) {
            $params = [
                'date'     => Carbon::today()->addDays($i)->format('Y-m-d'),
                'timezone' => config('football-api.timezone'),
            ];

            if ($leagueId) {
                $params['league'] = $leagueId;
            }

            $cacheTtl = config('football-api.cache.ttl.fixtures');
            $result = $this->makeRequest('/fixtures', $params, $cacheTtl);

            if (!empty($result['response'])) {
                $allFixtures = array_merge($allFixtures, $result['response']);
            }
        }

        return ['response' => $allFixtures];
    }

    /**
     * Récupérer uniquement les matchs populaires du jour
     * Filtre les fixtures par les ligues configurées dans popular_leagues
     */
    public function getPopularMatches(?string $date = null): array
    {
        $response = $this->getUpcomingMatches(1);
        $fixtures = $response['response'] ?? [];

        if (empty($fixtures)) {
            return ['response' => [], 'popular_leagues_only' => true];
        }

        $leagueConfig = config('football-api.popular_leagues', []);
        $popularIds   = array_keys($leagueConfig);

        $popular = [];
        foreach ($fixtures as $fixture) {
            $leagueId = $fixture['league']['id'] ?? null;
            if ($leagueId && in_array($leagueId, $popularIds)) {
                $fixture['_tier'] = $leagueConfig[$leagueId]['tier'];
                $popular[] = $fixture;
            }
        }

        // Trier par tier croissant (1 = plus populaire en premier)
        usort($popular, fn($a, $b) => $a['_tier'] <=> $b['_tier']);

        Log::info("API-Football: Popular matches filtered", [
            'total'   => count($fixtures),
            'popular' => count($popular),
        ]);

        return ['response' => $popular, 'popular_leagues_only' => true];
    }

    /**
     * Récupérer les matchs en direct
     */
    public function getLiveMatches(): ?array
    {
        $params = [
            'live' => 'all',
            'timezone' => config('football-api.timezone'),
        ];

        $cacheTtl = config('football-api.cache.ttl.live_scores');

        return $this->makeRequest('/fixtures', $params, $cacheTtl);
    }

    /**
     * Récupérer les détails d'un match
     */
    public function getMatchDetails(int $fixtureId): ?array
    {
        $params = [
            'id' => $fixtureId,
            'timezone' => config('football-api.timezone'),
        ];

        $cacheTtl = config('football-api.cache.ttl.fixtures');

        return $this->makeRequest('/fixtures', $params, $cacheTtl);
    }

    /**
     * Récupérer les statistiques d'une équipe
     */
    public function getTeamStatistics(int $teamId, int $season, int $leagueId): ?array
    {
        $params = [
            'team' => $teamId,
            'season' => $season,
            'league' => $leagueId,
        ];

        $cacheTtl = config('football-api.cache.ttl.statistics');

        return $this->makeRequest('/teams/statistics', $params, $cacheTtl);
    }

    /**
     * Récupérer l'historique des confrontations (H2H)
     */
    public function getHeadToHead(int $team1Id, int $team2Id, int $last = 5): ?array
    {
        $params = [
            'h2h' => "{$team1Id}-{$team2Id}",
            'last' => $last,
            'timezone' => config('football-api.timezone'),
        ];

        $cacheTtl = config('football-api.cache.ttl.fixtures');

        return $this->makeRequest('/fixtures/headtohead', $params, $cacheTtl);
    }

    /**
     * Récupérer le classement d'une ligue
     */
    public function getStandings(int $leagueId, int $season): ?array
    {
        $params = [
            'league' => $leagueId,
            'season' => $season,
        ];

        $cacheTtl = config('football-api.cache.ttl.standings');

        return $this->makeRequest('/standings', $params, $cacheTtl);
    }

    /**
     * Récupérer les derniers matchs d'une équipe
     */
    public function getTeamRecentMatches(int $teamId, int $last = 5, int $season = null): ?array
    {
        $params = [
            'team' => $teamId,
            'last' => $last,
            'season' => $season ?? Carbon::now()->year,
            'timezone' => config('football-api.timezone'),
        ];

        $cacheTtl = config('football-api.cache.ttl.fixtures');

        return $this->makeRequest('/fixtures', $params, $cacheTtl);
    }

    /**
     * Récupérer les événements d'un match (buts, cartons, remplacements, etc.)
     */
    public function getMatchEvents(int $fixtureId): ?array
    {
        $params = [
            'fixture' => $fixtureId,
        ];

        // Cache très court pour les events (données en temps réel)
        return $this->makeRequest('/fixtures/events', $params, 60);
    }

    /**
     * Récupérer les compositions (lineups) d'un match
     */
    public function getMatchLineups(int $fixtureId): ?array
    {
        $params = [
            'fixture' => $fixtureId,
        ];

        $cacheTtl = config('football-api.cache.ttl.fixtures');
        return $this->makeRequest('/fixtures/lineups', $params, $cacheTtl);
    }

    /**
     * Récupérer les informations d'une équipe
     */
    public function getTeamInfo(int $teamId): ?array
    {
        return $this->makeRequest('/teams', ['id' => $teamId], 86400);
    }

    /**
     * Récupérer l'effectif d'une équipe (saison en cours)
     */
    public function getTeamSquad(int $teamId): ?array
    {
        return $this->makeRequest('/players/squads', ['team' => $teamId], 43200);
    }

    /**
     * Récupérer les joueurs d'une équipe avec stats
     */
    public function getTeamPlayers(int $teamId, int $season, int $leagueId): ?array
    {
        return $this->makeRequest('/players', [
            'team'   => $teamId,
            'season' => $season,
            'league' => $leagueId,
        ], 43200);
    }

    /**
     * Récupérer les transferts d'une équipe
     */
    public function getTeamTransfers(int $teamId): ?array
    {
        return $this->makeRequest('/transfers', ['team' => $teamId], 86400);
    }

    /**
     * Récupérer les blessures/suspensions d'une équipe
     */
    public function getTeamInjuries(int $teamId, int $season): ?array
    {
        return $this->makeRequest('/injuries', [
            'team'   => $teamId,
            'season' => $season,
        ], 3600);
    }

    /**
     * Récupérer les statistiques d'un match (possession, tirs, corners…)
     */
    public function getMatchStats(int $fixtureId): ?array
    {
        return $this->makeRequest('/fixtures/statistics', ['fixture' => $fixtureId], 300);
    }

    /**
     * Récupérer les trophées d'une équipe
     */
    public function getTeamTrophies(int $teamId): ?array
    {
        return $this->makeRequest('/trophies', ['team' => $teamId], 86400);
    }

    /**
     * Récupérer les prédictions de l'API (optionnel)
     */
    public function getApiPredictions(int $fixtureId): ?array
    {
        $params = [
            'fixture' => $fixtureId,
        ];

        // Ne pas mettre en cache les prédictions
        return $this->makeRequest('/predictions', $params, 0);
    }

    /**
     * Vérifier les limites de rate
     */
    protected function checkRateLimit(): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $minute = Carbon::now()->format('Y-m-d H:i');

        $dailyCount = Cache::get("football_api_daily_{$today}", 0);
        $minuteCount = Cache::get("football_api_minute_{$minute}", 0);

        if ($dailyCount >= $this->rateLimits['requests_per_day']) {
            throw new \Exception("Daily API rate limit exceeded ({$this->rateLimits['requests_per_day']} requests/day)");
        }

        if ($minuteCount >= $this->rateLimits['requests_per_minute']) {
            throw new \Exception("Minute API rate limit exceeded ({$this->rateLimits['requests_per_minute']} requests/minute)");
        }
    }

    /**
     * Incrémenter le compteur de requêtes
     */
    protected function incrementRequestCount(): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $minute = Carbon::now()->format('Y-m-d H:i');

        // Incrémenter le compteur journalier
        $dailyCount = Cache::get("football_api_daily_{$today}", 0);
        Cache::put("football_api_daily_{$today}", $dailyCount + 1, 86400);

        // Incrémenter le compteur par minute
        $minuteCount = Cache::get("football_api_minute_{$minute}", 0);
        Cache::put("football_api_minute_{$minute}", $minuteCount + 1, 60);

        Log::info("API-Football: Request count - Daily: " . ($dailyCount + 1) . ", Minute: " . ($minuteCount + 1));
    }

    /**
     * Générer une clé de cache
     */
    protected function generateCacheKey(string $endpoint, array $params): string
    {
        return 'football_api_' . md5($endpoint . json_encode($params));
    }

    /**
     * Vider le cache
     */
    public function clearCache(): void
    {
        Cache::flush();
        Log::info("API-Football: Cache cleared");
    }

    /**
     * Obtenir les statistiques d'utilisation de l'API
     */
    public function getUsageStats(): array
    {
        $today = Carbon::today()->format('Y-m-d');
        $minute = Carbon::now()->format('Y-m-d H:i');

        $dailyCount = Cache::get("football_api_daily_{$today}", 0);
        $minuteCount = Cache::get("football_api_minute_{$minute}", 0);

        return [
            'daily' => [
                'used' => $dailyCount,
                'limit' => $this->rateLimits['requests_per_day'],
                'remaining' => $this->rateLimits['requests_per_day'] - $dailyCount,
                'percentage' => round(($dailyCount / $this->rateLimits['requests_per_day']) * 100, 2),
            ],
            'minute' => [
                'used' => $minuteCount,
                'limit' => $this->rateLimits['requests_per_minute'],
                'remaining' => $this->rateLimits['requests_per_minute'] - $minuteCount,
            ],
            'plan' => config('football-api.current_plan'),
        ];
    }
}
