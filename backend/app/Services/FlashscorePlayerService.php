<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Données joueurs via Flashscore4 (RapidAPI).
 *
 * Couverture :
 *   - Détails joueur : âge, nationalité, poste, valeur marchande, contrat, équipe
 *   - Statut blessure (is_injured, injury_description)
 *   - Derniers matchs joués avec résultats
 *
 * Utilise RAPIDAPI_KEY par défaut (ou FLASHSCORE4_KEY si dédié).
 */
class FlashscorePlayerService
{
    private const BASE_URL  = 'https://flashscore4.p.rapidapi.com';
    private const HOST      = 'flashscore4.p.rapidapi.com';
    private const CACHE_TTL = 3600; // 1h — données joueur changent peu souvent

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Détails complets d'un joueur par son URL Flashscore.
     *
     * @param string $playerUrl  Ex: "ronaldo-cristiano/U7gGJuMo/"  (slug/id sans le domaine)
     * @return array|null        Données normalisées ou null si non trouvé
     */
    public function getPlayerDetails(string $playerUrl): ?array
    {
        $cacheKey = 'flashscore_player_' . md5($playerUrl);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($playerUrl) {
            try {
                $response = $this->http()->get(self::BASE_URL . '/api/flashscore/v2/players/details', [
                    'player_url' => $playerUrl,
                ]);

                if (!$response->successful()) {
                    Log::warning("FlashscorePlayerService::getPlayerDetails [{$playerUrl}] non-200", [
                        'status' => $response->status(),
                    ]);
                    return null;
                }

                $data = $response->json();
                return $this->normalizePlayer($data);
            } catch (\Throwable $e) {
                Log::error("FlashscorePlayerService::getPlayerDetails failed: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Recherche un joueur par nom et retourne ses détails.
     * Utilise AllSportsApiService pour la recherche, puis enrichit avec Flashscore.
     *
     * @param string $name  Nom du joueur
     * @return array        Liste des résultats avec détails Flashscore si disponible
     */
    public function searchPlayer(string $name): array
    {
        $cacheKey = 'flashscore_search_' . md5(strtolower($name));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($name) {
            try {
                $response = $this->http()->get(self::BASE_URL . '/api/flashscore/v2/players/search', [
                    'search' => $name,
                ]);

                if (!$response->successful()) {
                    return [];
                }

                $results = $response->json('results', []);
                return array_map([$this, 'normalizeSearchResult'], $results);
            } catch (\Throwable $e) {
                Log::error("FlashscorePlayerService::searchPlayer failed: " . $e->getMessage());
                return [];
            }
        });
    }

    // ── Normalisation ─────────────────────────────────────────────────────────

    private function normalizePlayer(array $data): array
    {
        return [
            'id'                 => $data['id'] ?? null,
            'name'               => $data['name'] ?? '',
            'nationality'        => $data['nationality'] ?? null,
            'position'           => $data['position'] ?? null,
            'age'                => $data['age'] ?? null,
            'birthday'           => $data['birthday'] ?? null,
            'height_cm'          => $data['height'] ?? null,
            'weight_kg'          => $data['weight'] ?? null,
            'market_value'       => $data['market_value'] ?? null,
            'market_value_raw'   => $data['market_value_raw'] ?? null,
            'contract_expires'   => $data['contract_expiry'] ?? null,
            'current_team'       => $data['team'] ?? null,
            'shirt_number'       => $data['shirt_number'] ?? null,
            'is_injured'         => (bool) ($data['is_injured'] ?? false),
            'injury_description' => $data['injury_description'] ?? null,
            'last_matches'       => $this->normalizeLastMatches($data['last_matches'] ?? []),
            'image_url'          => $data['image_url'] ?? null,
            'source'             => 'flashscore4',
        ];
    }

    private function normalizeLastMatches(array $matches): array
    {
        return array_map(function (array $m): array {
            return [
                'date'        => $m['date'] ?? null,
                'competition' => $m['tournament'] ?? $m['competition'] ?? null,
                'home_team'   => $m['home_team'] ?? $m['homeTeam'] ?? null,
                'away_team'   => $m['away_team'] ?? $m['awayTeam'] ?? null,
                'score'       => $m['score'] ?? null,
                'rating'      => $m['rating'] ?? null,
                'goals'       => $m['goals'] ?? 0,
                'assists'     => $m['assists'] ?? 0,
                'minutes'     => $m['minutes_played'] ?? $m['minutesPlayed'] ?? null,
            ];
        }, $matches);
    }

    private function normalizeSearchResult(array $result): array
    {
        return [
            'id'          => $result['id'] ?? null,
            'name'        => $result['name'] ?? '',
            'team'        => $result['team'] ?? null,
            'nationality' => $result['nationality'] ?? null,
            'position'    => $result['position'] ?? null,
            'image_url'   => $result['image_url'] ?? null,
            'player_url'  => $result['url'] ?? null,
            'source'      => 'flashscore4',
        ];
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        $key = env('FLASHSCORE4_KEY', env('SPORTAPI7_KEY', config('services.rapidapi.key', '')));

        return Http::withHeaders([
            'x-rapidapi-host' => self::HOST,
            'x-rapidapi-key'  => $key,
        ])->timeout(12);
    }
}
