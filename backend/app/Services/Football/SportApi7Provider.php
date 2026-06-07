<?php

declare(strict_types=1);

namespace App\Services\Football;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Provider secondaire — SportAPI7 (SofaScore-based, RapidAPI)
 *
 * Couverture : 111+ matchs/jour football, cotes 1X2, forme récente,
 * H2H, classements, incidents live, compositions.
 *
 * Quota : illimité sur ce plan (pas de limite de requêtes documentée).
 * Cache agressif pour limiter la consommation.
 */
class SportApi7Provider implements FootballProviderInterface
{
    private const BASE_URL   = 'https://sportapi7.p.rapidapi.com/api/v1';
    private const HOST       = 'sportapi7.p.rapidapi.com';
    private const CACHE_TTL  = 600;  // 10 min pour fixtures
    private const LIVE_TTL   = 60;   // 1 min pour live

    public function name(): string
    {
        return 'sportapi7';
    }

    public function isAvailable(): bool
    {
        return !empty(config('services.sportapi7.key', env('SPORTAPI7_KEY', '')));
    }

    public function getFixtures(?string $date = null): array
    {
        $date     = $date ?? Carbon::today()->format('Y-m-d');
        $cacheKey = "sportapi7_fixtures_{$date}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($date) {
            try {
                $response = $this->http()
                    ->get(self::BASE_URL . "/sport/football/scheduled-events/{$date}");

                if (!$response->successful()) {
                    Log::warning('SportApi7: fixtures non-200', ['status' => $response->status(), 'date' => $date]);
                    return [];
                }

                $events = $response->json('events', []);
                Log::info('SportApi7: fixtures chargées', ['count' => count($events), 'date' => $date]);

                return $this->normalizeEvents($events);
            } catch (\Throwable $e) {
                Log::error('SportApi7Provider::getFixtures failed', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    public function getLiveMatches(): array
    {
        $cacheKey = 'sportapi7_live_' . now()->format('YmdHi');

        return Cache::remember($cacheKey, self::LIVE_TTL, function () {
            try {
                $response = $this->http()
                    ->get(self::BASE_URL . '/sport/football/events/live');

                if (!$response->successful()) {
                    return [];
                }

                $events = $response->json('events', []);
                return $this->normalizeEvents($events, isLive: true);
            } catch (\Throwable $e) {
                Log::error('SportApi7Provider::getLiveMatches failed', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Cotes 1X2 pour un match donné (identifiant SportAPI7).
     * Retourne null si non disponible.
     */
    public function getOdds(int|string $eventId): ?array
    {
        $cacheKey = "sportapi7_odds_{$eventId}";

        return Cache::remember($cacheKey, 1800, function () use ($eventId) {
            try {
                $response = $this->http()
                    ->get(self::BASE_URL . "/event/{$eventId}/odds/1/all");

                if (!$response->successful()) {
                    return null;
                }

                $markets = $response->json('markets', []);
                $result  = [];

                foreach ($markets as $market) {
                    $name    = $market['marketName'] ?? '';
                    $choices = $market['choices'] ?? [];

                    if (str_contains(strtolower($name), 'full time')) {
                        foreach ($choices as $choice) {
                            $result['1x2'][$choice['name']] = $this->fractionToDecimal($choice['fractionalValue'] ?? '');
                        }
                    }

                    if (str_contains(strtolower($name), '1st half')) {
                        foreach ($choices as $choice) {
                            $result['half_time'][$choice['name']] = $this->fractionToDecimal($choice['fractionalValue'] ?? '');
                        }
                    }

                    if (str_contains(strtolower($name), 'double chance')) {
                        foreach ($choices as $choice) {
                            $result['double_chance'][$choice['name']] = $this->fractionToDecimal($choice['fractionalValue'] ?? '');
                        }
                    }
                }

                return empty($result) ? null : $result;
            } catch (\Throwable $e) {
                Log::warning('SportApi7Provider::getOdds failed', ['eventId' => $eventId, 'error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * 30 derniers matchs d'une équipe (pour le critère forme récente).
     */
    public function getTeamRecentForm(int|string $teamId): array
    {
        $cacheKey = "sportapi7_form_{$teamId}";

        return Cache::remember($cacheKey, 3600, function () use ($teamId) {
            try {
                $response = $this->http()
                    ->get(self::BASE_URL . "/team/{$teamId}/events/last/0");

                if (!$response->successful()) {
                    return [];
                }

                return $this->normalizeEvents($response->json('events', []));
            } catch (\Throwable $e) {
                Log::warning('SportApi7Provider::getTeamRecentForm failed', ['teamId' => $teamId, 'error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Bilan H2H entre deux équipes.
     */
    public function getH2H(int|string $eventId): array
    {
        $cacheKey = "sportapi7_h2h_{$eventId}";

        return Cache::remember($cacheKey, 3600, function () use ($eventId) {
            try {
                $response = $this->http()
                    ->get(self::BASE_URL . "/event/{$eventId}/h2h");

                if (!$response->successful()) {
                    return [];
                }

                return $response->json('teamDuel', []);
            } catch (\Throwable $e) {
                Log::warning('SportApi7Provider::getH2H failed', ['eventId' => $eventId, 'error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Classement d'un tournoi/saison.
     */
    public function getStandings(int|string $tournamentId, int|string $seasonId): array
    {
        $cacheKey = "sportapi7_standings_{$tournamentId}_{$seasonId}";

        return Cache::remember($cacheKey, 7200, function () use ($tournamentId, $seasonId) {
            try {
                $response = $this->http()
                    ->get(self::BASE_URL . "/unique-tournament/{$tournamentId}/season/{$seasonId}/standings/total");

                if (!$response->successful()) {
                    return [];
                }

                $rows = [];
                foreach ($response->json('standings', []) as $standing) {
                    foreach ($standing['rows'] ?? [] as $row) {
                        $rows[] = [
                            'position' => $row['position'] ?? 0,
                            'team'     => $row['team']['name'] ?? '',
                            'team_id'  => $row['team']['id'] ?? null,
                            'points'   => $row['points'] ?? 0,
                            'played'   => $row['matches'] ?? 0,
                            'wins'     => $row['wins'] ?? 0,
                            'draws'    => $row['draws'] ?? 0,
                            'losses'   => $row['losses'] ?? 0,
                            'goals_for'     => $row['scoresFor'] ?? 0,
                            'goals_against' => $row['scoresAgainst'] ?? 0,
                        ];
                    }
                }

                return $rows;
            } catch (\Throwable $e) {
                Log::warning('SportApi7Provider::getStandings failed', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Incidents d'un match (buts, cartons, substitutions).
     */
    public function getIncidents(int|string $eventId): array
    {
        $cacheKey = "sportapi7_incidents_{$eventId}";

        return Cache::remember($cacheKey, 120, function () use ($eventId) {
            try {
                $response = $this->http()
                    ->get(self::BASE_URL . "/event/{$eventId}/incidents");

                if (!$response->successful()) {
                    return [];
                }

                return array_map(fn(array $i) => [
                    'type'    => $i['incidentType'] ?? '',
                    'minute'  => $i['time'] ?? null,
                    'player'  => $i['player']['name'] ?? null,
                    'is_home' => $i['isHome'] ?? null,
                    'result'  => $i['homeScore'] . '-' . $i['awayScore'],
                ], $response->json('incidents', []));
            } catch (\Throwable $e) {
                Log::warning('SportApi7Provider::getIncidents failed', ['eventId' => $eventId, 'error' => $e->getMessage()]);
                return [];
            }
        });
    }

    // ── Normalisation ─────────────────────────────────────────────────────────

    private function normalizeEvents(array $events, bool $isLive = false): array
    {
        $fixtures = [];

        foreach ($events as $e) {
            $homeTeam = $e['homeTeam'] ?? [];
            $awayTeam = $e['awayTeam'] ?? [];
            $status   = $e['status']['description'] ?? 'Not started';
            $ts       = $e['startTimestamp'] ?? null;

            $fixtures[] = [
                '_source'    => $this->name(),
                '_raw'       => $e,
                'fixture_id' => $e['id'] ?? null,
                'date'       => $ts ? Carbon::createFromTimestamp($ts)->toIso8601String() : null,
                'status'     => $this->mapStatus($status, $isLive),
                'elapsed'    => $e['time']['played'] ?? ($isLive ? ($e['time']['period'] ?? null) : null),
                'home_team'  => $homeTeam['name'] ?? '',
                'away_team'  => $awayTeam['name'] ?? '',
                'home_team_id' => $homeTeam['id'] ?? null,
                'away_team_id' => $awayTeam['id'] ?? null,
                'home_score' => $e['homeScore']['current'] ?? null,
                'away_score' => $e['awayScore']['current'] ?? null,
                'league'     => $e['tournament']['name'] ?? '',
                'league_id'  => $e['tournament']['uniqueTournament']['id'] ?? null,
                'country'    => $e['tournament']['category']['name'] ?? '',
                'venue'      => $e['venue']['stadium']['name'] ?? null,
            ];
        }

        return $fixtures;
    }

    private function mapStatus(string $status, bool $isLive): string
    {
        if ($isLive) {
            return match(strtolower($status)) {
                'halftime' => 'HT',
                'ended', 'finished' => 'FT',
                default    => '1H',
            };
        }

        return match(strtolower($status)) {
            'not started'         => 'NS',
            'first half'          => '1H',
            'second half'         => '2H',
            'halftime'            => 'HT',
            'extra time'          => 'ET',
            'penalties'           => 'P',
            'ended', 'finished', 'aet' => 'FT',
            'postponed'           => 'PST',
            'cancelled'           => 'CANC',
            default               => 'NS',
        };
    }

    /** Convertit une cote fractionnelle "10/11" en décimal */
    private function fractionToDecimal(string $fractional): float
    {
        if (str_contains($fractional, '/')) {
            [$num, $den] = explode('/', $fractional, 2);
            $den = (float) $den;
            return $den > 0 ? round((float)$num / $den + 1, 4) : 0.0;
        }
        return (float) $fractional;
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'x-rapidapi-host' => self::HOST,
            'x-rapidapi-key'  => config('services.sportapi7.key', env('SPORTAPI7_KEY', '')),
        ])->timeout(12);
    }
}
