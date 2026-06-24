<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service Zafronix — données FIFA World Cup (API directe api.zafronix.com).
 *
 * Authentification par header X-API-Key (≠ RapidAPI). Plan free : 250 req/h.
 * Endpoints utiles à COTA : tournoi 2026, équipes (avec phase de groupes),
 * matchs. Données structurées officielles, complémentaires de wc-live.
 */
class ZafronixService
{
    private string $key;
    private string $baseUrl;

    public function __construct()
    {
        $this->key     = (string) config('services.zafronix.key', '');
        $this->baseUrl = rtrim((string) config('services.zafronix.base_url', ''), '/');
    }

    /**
     * Métadonnées d'un tournoi (par défaut 2026) : hôtes, dates, stades, format.
     * Cache 24h (données quasi statiques).
     */
    public function getTournament(int $year = 2026): ?array
    {
        return Cache::remember("zafronix_tournament_{$year}", 86400, function () use ($year) {
            $data = $this->get("/tournaments/{$year}");
            return $data['tournament'] ?? null;
        });
    }

    /**
     * Équipes d'un tournoi avec leur phase de groupes (groupe, points, position).
     * Cache 1h. Retourne la liste brute normalisée.
     *
     * @return array<int, array>
     */
    public function getTeams(int $year = 2026): array
    {
        return Cache::remember("zafronix_teams_{$year}", 3600, function () use ($year) {
            $data = $this->get('/teams', ['tournament' => $year]);
            if (!is_array($data)) {
                return [];
            }

            return array_map(function (array $t): array {
                $gs = $t['groupStage'] ?? [];
                return [
                    'name'          => $t['name'] ?? '?',
                    'code'          => $t['code'] ?? null,
                    'iso'           => $t['iso'] ?? null,
                    'confederation' => $t['confederation'] ?? null,
                    'group'         => $gs['group'] ?? null,
                    'played'        => $gs['played'] ?? 0,
                    'won'           => $gs['won'] ?? 0,
                    'drawn'         => $gs['drawn'] ?? 0,
                    'lost'          => $gs['lost'] ?? 0,
                    'goals_for'     => $gs['goalsFor'] ?? 0,
                    'goals_against' => $gs['goalsAgainst'] ?? 0,
                    'points'        => $gs['points'] ?? 0,
                    'position'      => $gs['position'] ?? null,
                ];
            }, $data);
        });
    }

    /**
     * Liste des matchs d'un tournoi via la route dédiée /matches/{year}.
     * (Le filtre ?tournament= est ignoré par l'API → données historiques.)
     * Cache 10min. Renvoie la version résumée (sans compos/buteurs).
     *
     * @return array<int, array>
     */
    public function getMatches(int $year = 2026): array
    {
        return Cache::remember("zafronix_matches_{$year}", 600, function () use ($year) {
            $data = $this->get("/matches/{$year}");
            $rows = $data['data'] ?? [];

            return array_map(fn(array $m): array => $this->summarizeMatch($m), $rows);
        });
    }

    /**
     * Détail complet d'un match via /matches/{matchId} : heure, buteurs,
     * compositions, formations, cartons, remplacements, statistiques.
     * Cache 60s (un match en cours évolue). Retourne null si introuvable.
     */
    public function getMatch(string $matchId): ?array
    {
        return Cache::remember("zafronix_match_{$matchId}", 60, function () use ($matchId) {
            $m = $this->get("/matches/{$matchId}");
            if (empty($m) || !isset($m['id'])) {
                return null;
            }
            return $this->detailMatch($m);
        });
    }

    /**
     * Résumé d'un match (liste).
     */
    private function summarizeMatch(array $m): array
    {
        return [
            'id'          => $m['id'] ?? null,
            'match_no'    => $m['matchNo'] ?? null,
            'date'        => $m['date'] ?? null,
            'kickoff'     => $m['kickoff'] ?? null,
            'kickoff_utc' => $m['kickoffUtc'] ?? null,
            'stage'       => $m['stage'] ?? null,
            'status'      => $m['status'] ?? null,
            'live_minute' => $m['liveMinute'] ?? null,
            'home_team'   => $m['homeTeam'] ?? '?',
            'away_team'   => $m['awayTeam'] ?? '?',
            'home_score'  => $m['homeScore'] ?? null,
            'away_score'  => $m['awayScore'] ?? null,
            'result'      => $m['result'] ?? null,
            'stadium'     => $m['stadium'] ?? null,
            'city'        => $m['city'] ?? null,
        ];
    }

    /**
     * Détail enrichi : ajoute buteurs, compos, formations, cartons.
     */
    private function detailMatch(array $m): array
    {
        $goals = array_map(fn(array $g): array => [
            'minute' => $g['minute'] ?? null,
            'team'   => $g['team'] ?? null,
            'scorer' => $g['scorer'] ?? null,
        ], $m['goals'] ?? []);

        $cards = array_map(fn(array $c): array => [
            'minute' => $c['minute'] ?? null,
            'team'   => $c['team'] ?? null,
            'player' => $c['player'] ?? null,
            'color'  => $c['color'] ?? null,
        ], $m['cards'] ?? []);

        $mapLineup = fn(array $players): array => array_map(fn(array $p): array => [
            'player'   => $p['player'] ?? '?',
            'number'   => $p['number'] ?? null,
            'position' => $p['position'] ?? null,
            'starter'  => (bool) ($p['starter'] ?? false),
            'captain'  => (bool) ($p['captain'] ?? false),
        ], $players);

        $lineups = $m['lineups'] ?? [];

        return array_merge($this->summarizeMatch($m), [
            'timezone'      => $m['timezone'] ?? null,
            'referee'       => $m['referee'] ?? null,
            'attendance'    => $m['attendance'] ?? null,
            'weather'       => $m['weather'] ?? null,
            'formations'    => $m['formations'] ?? null,
            'goals'         => $goals,
            'cards'         => $cards,
            'lineups'       => [
                'home' => $mapLineup($lineups['home'] ?? []),
                'away' => $mapLineup($lineups['away'] ?? []),
            ],
            'substitutions' => $m['substitutions'] ?? [],
            'statistics'    => $m['statistics'] ?? [],
        ]);
    }

    /**
     * Appel HTTP générique vers Zafronix. Retourne le JSON décodé ou null.
     */
    private function get(string $path, array $query = []): ?array
    {
        if ($this->key === '' || $this->baseUrl === '') {
            return null;
        }

        try {
            $response = Http::withHeaders(['X-API-Key' => $this->key])
                ->timeout(15)
                ->get($this->baseUrl . $path, $query);

            if (!$response->successful()) {
                Log::warning('[Zafronix] HTTP ' . $response->status() . " {$path}");
                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning("[Zafronix] {$path}: " . $e->getMessage());
            return null;
        }
    }
}
