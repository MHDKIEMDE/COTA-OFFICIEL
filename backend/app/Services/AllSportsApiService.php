<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Classements, recherche joueur et calendrier via AllSportsAPI2 (RapidAPI).
 *
 * Couverture :
 *   - Classements d'un tournoi/saison
 *   - Recherche joueur/équipe par nom
 *   - Matchs à venir d'un joueur
 *
 * Utilise RAPIDAPI_KEY par défaut.
 */
class AllSportsApiService
{
    private const BASE_URL  = 'https://allsportsapi2.p.rapidapi.com';
    private const HOST      = 'allsportsapi2.p.rapidapi.com';
    private const CACHE_TTL = 7200; // 2h

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Classement total d'un tournoi pour une saison donnée.
     *
     * @param int|string $tournamentId  Ex: 17 (Premier League)
     * @param int|string $seasonId      Ex: 76986
     */
    public function getStandings(int|string $tournamentId, int|string $seasonId): array
    {
        $cacheKey = "allsports_standings_{$tournamentId}_{$seasonId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tournamentId, $seasonId) {
            try {
                $response = $this->http()->get(
                    self::BASE_URL . "/api/tournament/{$tournamentId}/season/{$seasonId}/standings/total"
                );

                if (!$response->successful()) {
                    return [];
                }

                $rows = $response->json('standings.0.rows', []);
                return array_map([$this, 'normalizeStandingRow'], $rows);
            } catch (\Throwable $e) {
                Log::error("AllSportsApiService::getStandings failed: " . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Recherche universelle (équipe, joueur, compétition).
     *
     * @param string $query  Terme de recherche
     * @return array{players: array, teams: array, leagues: array}
     */
    public function search(string $query): array
    {
        $cacheKey = 'allsports_search_' . md5(strtolower($query));

        return Cache::remember($cacheKey, 1800, function () use ($query) {
            try {
                $response = $this->http()->get(self::BASE_URL . '/api/search/all', [
                    'q' => $query,
                ]);

                if (!$response->successful()) {
                    return ['players' => [], 'teams' => [], 'leagues' => []];
                }

                $rawResults = $response->json('results', []);

                $players = [];
                $teams   = [];
                $leagues = [];

                foreach ($rawResults as $item) {
                    $entity = $item['entity'] ?? [];
                    $type   = $item['type'] ?? '';

                    if ($type === 'player' || isset($entity['position'])) {
                        $players[] = $this->normalizePlayer($entity);
                    } elseif ($type === 'team' || isset($entity['nameCode'])) {
                        $teams[] = $this->normalizeTeam($entity);
                    } elseif ($type === 'league' || isset($entity['primaryColorHex'])) {
                        $leagues[] = $entity;
                    } else {
                        // Fallback : cherche par présence de clé team/sport
                        if (isset($entity['team'])) {
                            $players[] = $this->normalizePlayer($entity);
                        } elseif (isset($entity['nameCode'])) {
                            $teams[] = $this->normalizeTeam($entity);
                        }
                    }
                }

                return compact('players', 'teams', 'leagues');
            } catch (\Throwable $e) {
                Log::error("AllSportsApiService::search failed: " . $e->getMessage());
                return ['players' => [], 'teams' => [], 'leagues' => []];
            }
        });
    }

    /**
     * Recherche joueur dédiée via /api/search/all (filtre les entités de type joueur).
     */
    public function searchPlayer(string $name): array
    {
        $results = $this->search($name);
        return $results['players'];
    }

    // ── Normalisation ─────────────────────────────────────────────────────────

    private function normalizeStandingRow(array $row): array
    {
        $team = $row['team'] ?? [];
        return [
            'position'    => $row['position'] ?? null,
            'team_id'     => $team['id'] ?? null,
            'team_name'   => $team['name'] ?? '',
            'team_logo'   => $team['logo'] ?? null,
            'played'      => $row['matches'] ?? 0,
            'wins'        => $row['wins'] ?? 0,
            'draws'       => $row['draws'] ?? 0,
            'losses'      => $row['losses'] ?? 0,
            'goals_for'   => $row['scoresFor'] ?? 0,
            'goals_against' => $row['scoresAgainst'] ?? 0,
            'goal_diff'   => ($row['scoresFor'] ?? 0) - ($row['scoresAgainst'] ?? 0),
            'points'      => $row['points'] ?? 0,
            'form'        => $row['form'] ?? null,
        ];
    }

    private function normalizePlayer(array $p): array
    {
        return [
            'id'          => $p['id'] ?? null,
            'name'        => $p['name'] ?? '',
            'team'        => $p['team']['name'] ?? null,
            'nationality' => $p['country']['alpha2'] ?? null,
            'position'    => $p['position'] ?? null,
            'image_url'   => isset($p['id']) ? "https://img.sofascore.com/api/v1/player/{$p['id']}/image" : null,
            'source'      => 'allsportsapi2',
        ];
    }

    private function normalizeTeam(array $t): array
    {
        return [
            'id'      => $t['id'] ?? null,
            'name'    => $t['name'] ?? '',
            'logo'    => $t['logo'] ?? null,
            'country' => $t['country']['name'] ?? null,
            'source'  => 'allsportsapi2',
        ];
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        $key = env('ALLSPORTS_KEY', env('SPORTAPI7_KEY', config('services.rapidapi.key', '')));

        return Http::withHeaders([
            'x-rapidapi-host' => self::HOST,
            'x-rapidapi-key'  => $key,
        ])->timeout(12);
    }
}
