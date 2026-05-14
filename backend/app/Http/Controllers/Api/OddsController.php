<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmaker;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cotes bookmakers via API-Football /odds
 * Cache 10 minutes — pas de stockage base
 */
class OddsController extends Controller
{
    private string $apiKey;
    private string $baseUrl = 'https://v3.football.api-sports.io';

    // Bookmakers prioritaires pour l'Afrique de l'Ouest
    private array $priorityBookmakers = [
        1  => '10Bet',
        7  => 'William Hill',
        8  => 'Bet365',
        2  => 'Marathonbet',
        6  => 'Bwin',
        11 => 'Betclic',
    ];

    public function __construct()
    {
        $this->apiKey = env('FOOTBALL_API_KEY', '');
    }

    /**
     * GET /api/odds/match/{matchId}
     * Retourne les cotes du meilleur bookmaker disponible + tous les bookmakers
     */
    public function getMatchOdds(Request $request, string $matchId): JsonResponse
    {
        if (empty($this->apiKey)) {
            return response()->json(['success' => false, 'message' => 'API key manquante'], 500);
        }

        $cacheKey = "odds:apifootball:{$matchId}";

        $result = Cache::remember($cacheKey, 600, function () use ($matchId) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders(['x-apisports-key' => $this->apiKey])
                    ->get("{$this->baseUrl}/odds", ['fixture' => $matchId]);

                if (!$response->successful()) {
                    Log::warning("Odds API-Football erreur {$response->status()} pour fixture {$matchId}");
                    return null;
                }

                $data = $response->json();
                $fixtures = $data['response'] ?? [];

                if (empty($fixtures)) {
                    return null;
                }

                $fixture   = $fixtures[0];
                $bookmakers = $fixture['bookmakers'] ?? [];

                if (empty($bookmakers)) {
                    return null;
                }

                $formatted = [];
                foreach ($bookmakers as $bm) {
                    $bmId   = $bm['id'];
                    $bmName = $bm['name'];

                    $matchWinner = null;
                    $btts        = null;
                    $overUnder   = null;

                    foreach ($bm['bets'] ?? [] as $bet) {
                        if ($bet['name'] === 'Match Winner') {
                            $matchWinner = $bet['values'];
                        } elseif ($bet['name'] === 'Both Teams Score') {
                            $btts = $bet['values'];
                        } elseif (str_contains($bet['name'], 'Goals Over/Under')) {
                            $overUnder = $bet['values'];
                        }
                    }

                    if (!$matchWinner) continue;

                    $homeOdd  = null;
                    $drawOdd  = null;
                    $awayOdd  = null;
                    foreach ($matchWinner as $v) {
                        if ($v['value'] === 'Home') $homeOdd  = (float) $v['odd'];
                        if ($v['value'] === 'Draw') $drawOdd  = (float) $v['odd'];
                        if ($v['value'] === 'Away') $awayOdd  = (float) $v['odd'];
                    }

                    $over25 = null; $under25 = null;
                    foreach ($overUnder ?? [] as $v) {
                        if (str_contains($v['value'], 'Over 2.5'))  $over25  = (float) $v['odd'];
                        if (str_contains($v['value'], 'Under 2.5')) $under25 = (float) $v['odd'];
                    }

                    $bttsYes = null; $bttsNo = null;
                    foreach ($btts ?? [] as $v) {
                        if ($v['value'] === 'Yes') $bttsYes = (float) $v['odd'];
                        if ($v['value'] === 'No')  $bttsNo  = (float) $v['odd'];
                    }

                    $formatted[] = [
                        'id'        => (string) $bmId,
                        'name'      => $bmName,
                        'priority'  => isset($this->priorityBookmakers[$bmId]) ? 0 : 1,
                        'home_win'  => $homeOdd,
                        'draw'      => $drawOdd,
                        'away_win'  => $awayOdd,
                        'over_25'   => $over25,
                        'under_25'  => $under25,
                        'btts_yes'  => $bttsYes,
                        'btts_no'   => $bttsNo,
                        'last_update' => now()->toIso8601String(),
                    ];
                }

                // Trier : bookmakers prioritaires en premier
                usort($formatted, fn($a, $b) => $a['priority'] <=> $b['priority']);

                return [
                    'match_id'    => $matchId,
                    'home_team'   => $fixture['fixture']['teams']['home']['name'] ?? null,
                    'away_team'   => $fixture['fixture']['teams']['away']['name'] ?? null,
                    'bookmakers'  => $formatted,
                ];
            } catch (\Exception $e) {
                Log::error("Odds exception fixture {$matchId}: " . $e->getMessage());
                return null;
            }
        });

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Cotes non disponibles pour ce match',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * GET /api/odds/batch?match_ids=id1,id2,...
     */
    public function getBatchOdds(Request $request): JsonResponse
    {
        $ids = array_filter(explode(',', $request->query('match_ids', '')));

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'match_ids requis'], 422);
        }

        $results = [];
        foreach (array_slice($ids, 0, 10) as $id) {
            $sub = $this->getMatchOdds($request, $id);
            $decoded = $sub->getData(true);
            if ($decoded['success'] ?? false) {
                $results[$id] = $decoded['data'];
            }
        }

        return response()->json(['success' => true, 'data' => $results]);
    }

    /**
     * GET /api/odds/bookmakers
     */
    public function getBookmakers(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => array_values($this->priorityBookmakers),
        ]);
    }

    /**
     * GET /api/bookmakers/auto
     *
     * Détecte automatiquement les bookmakers présents dans les cotes
     * des prédictions du jour, puis fusionne avec les liens configurés en admin.
     * Cache 30 minutes.
     */
    public function getAutoBookmakers(): JsonResponse
    {
        $cacheKey = 'bookmakers:auto:' . now()->format('Y-m-d');

        $result = Cache::remember($cacheKey, 1800, function () {
            // 1. Récupérer les match_id des prédictions publiées aujourd'hui
            $matchIds = DB::table('predictions')
                ->where('is_published', true)
                ->whereDate('match_date', now()->toDateString())
                ->pluck('match_id')
                ->unique()
                ->filter()
                ->values()
                ->toArray();

            if (empty($matchIds)) {
                return [];
            }

            // 2. Collecter les bookmakers uniques depuis les cotes (max 8 matchs)
            $detectedBookmakers = [];

            foreach (array_slice($matchIds, 0, 8) as $matchId) {
                try {
                    $oddsCache = Cache::get("odds:apifootball:{$matchId}");

                    if (!$oddsCache) {
                        // Appel API si pas encore en cache
                        $response = Http::timeout(8)
                            ->withHeaders(['x-apisports-key' => $this->apiKey])
                            ->get("{$this->baseUrl}/odds", ['fixture' => $matchId]);

                        if (!$response->successful()) continue;

                        $fixtures = $response->json()['response'] ?? [];
                        if (empty($fixtures)) continue;

                        $oddsCache = ['bookmakers' => []];
                        foreach ($fixtures[0]['bookmakers'] ?? [] as $bm) {
                            $oddsCache['bookmakers'][] = [
                                'id'   => (string) $bm['id'],
                                'name' => $bm['name'],
                            ];
                        }
                    }

                    foreach ($oddsCache['bookmakers'] ?? [] as $bm) {
                        $id = $bm['id'] ?? $bm['name'];
                        if (!isset($detectedBookmakers[$id])) {
                            $detectedBookmakers[$id] = [
                                'api_id' => $id,
                                'name'   => $bm['name'],
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("AutoBookmakers: odds fetch failed for {$matchId}: " . $e->getMessage());
                }
            }

            if (empty($detectedBookmakers)) {
                return [];
            }

            // 3. Fusionner avec les liens configurés en admin (matching par nom insensible à la casse)
            $configured = Bookmaker::all()->keyBy(fn($b) => strtolower($b->name));

            $output = [];
            foreach ($detectedBookmakers as $bm) {
                $key        = strtolower($bm['name']);
                $configured_bm = $configured->get($key);

                $output[] = [
                    'api_id'        => $bm['api_id'],
                    'name'          => $configured_bm?->name ?? $bm['name'],
                    'slug'          => $configured_bm?->slug ?? null,
                    'primary_color' => $configured_bm?->primary_color ?? null,
                    'affiliate_link'=> $configured_bm?->affiliate_link ?? null,
                    'download_link' => $configured_bm?->download_link ?? null,
                    'description'   => $configured_bm?->description ?? null,
                    'is_configured' => $configured_bm !== null,
                ];
            }

            // Trier : configurés avec lien en premier, puis par nom
            usort($output, function ($a, $b) {
                if ($a['is_configured'] !== $b['is_configured']) {
                    return $b['is_configured'] <=> $a['is_configured'];
                }
                return strcmp($a['name'], $b['name']);
            });

            return $output;
        });

        return response()->json([
            'success' => true,
            'data'    => $result,
            'count'   => count($result),
        ]);
    }

    /**
     * GET /api/bookmakers/by-region
     *
     * Détecte la région de l'utilisateur via son IP (ou le paramètre ?region=)
     * et retourne les bookmakers correspondants triés par sort_order.
     *
     * Mapping IP → région :
     *   - Burkina, Mali, Sénégal, Côte d'Ivoire, Niger, Guinée… → west_africa
     *   - Cameroun, Congo, Gabon…                               → central_africa
     *   - Kenya, Tanzanie, Ouganda…                             → east_africa
     *   - Maroc, Algérie, Tunisie, Égypte…                     → north_africa
     *   - Europe                                                → europe
     *   - Autre                                                 → global
     */
    public function getByRegion(Request $request): JsonResponse
    {
        $forcedRegion = $request->query('region');
        $ip           = $request->ip();

        $region = $forcedRegion ?? $this->detectRegion($ip);

        $cacheKey = "bookmakers:region:{$region}";

        $data = Cache::remember($cacheKey, 3600, function () use ($region) {
            return Bookmaker::active()
                ->withCount('clicks')
                ->get()
                ->filter(fn(Bookmaker $bm) => $this->matchesRegion($bm, $region))
                ->sortByDesc('clicks_count')
                ->map(fn(Bookmaker $bm) => [
                    'api_id'         => (string) $bm->id,
                    'name'           => $bm->name,
                    'slug'           => $bm->slug,
                    'primary_color'  => $bm->primary_color,
                    'affiliate_link' => $bm->affiliate_link,
                    'download_link'  => $bm->download_link,
                    'description'    => $bm->description,
                    'logo_url'       => $bm->logo_url,
                    'regions'        => $bm->regions ?? [],
                    'is_configured'  => !empty($bm->affiliate_link),
                    'clicks_count'   => $bm->clicks_count,
                ])
                ->values()
                ->all();
        });

        return response()->json([
            'success'         => true,
            'detected_region' => $region,
            'data'            => $data,
            'count'           => count($data),
        ]);
    }

    // ── Helpers région ───────────────────────────────────────────────────────

    private function detectRegion(string $ip): string
    {
        // IPs locales / développement → Afrique de l'Ouest par défaut
        if ($this->isLocalIp($ip)) {
            return 'west_africa';
        }

        $cacheKey = "ip_region:{$ip}";

        return Cache::remember($cacheKey, 86400, function () use ($ip) {
            try {
                $resp = Http::timeout(5)->get("http://ip-api.com/json/{$ip}?fields=countryCode");
                if (!$resp->successful()) {
                    return 'global';
                }
                $code = strtoupper($resp->json('countryCode') ?? '');
                return $this->countryCodeToRegion($code);
            } catch (\Exception $e) {
                Log::warning("ip-api.com failed for {$ip}: " . $e->getMessage());
                return 'global';
            }
        });
    }

    private function isLocalIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.');
    }

    private function countryCodeToRegion(string $code): string
    {
        $westAfrica    = ['BF', 'ML', 'SN', 'CI', 'NE', 'GN', 'GW', 'MR', 'GM', 'SL', 'LR', 'GH', 'TG', 'BJ', 'NG', 'CV'];
        $centralAfrica = ['CM', 'CG', 'CD', 'GA', 'CF', 'TD', 'GQ', 'ST', 'AO'];
        $eastAfrica    = ['KE', 'TZ', 'UG', 'RW', 'BI', 'ET', 'SO', 'DJ', 'ER', 'MZ', 'ZM', 'MW', 'ZW', 'MG', 'SC', 'MU'];
        $northAfrica   = ['MA', 'DZ', 'TN', 'EG', 'LY', 'SD', 'SS'];
        $southAfrica   = ['ZA', 'NA', 'BW', 'SZ', 'LS'];
        $europe        = ['FR', 'DE', 'ES', 'IT', 'GB', 'PT', 'BE', 'NL', 'CH', 'AT', 'PL', 'SE', 'NO', 'DK', 'FI', 'IE', 'CZ', 'HU', 'RO', 'BG', 'GR', 'HR', 'SK', 'SI', 'RS', 'LT', 'LV', 'EE', 'LU', 'MT', 'CY', 'IS', 'UA', 'RU', 'TR'];

        if (in_array($code, $westAfrica))    return 'west_africa';
        if (in_array($code, $centralAfrica)) return 'central_africa';
        if (in_array($code, $eastAfrica))    return 'east_africa';
        if (in_array($code, $northAfrica))   return 'north_africa';
        if (in_array($code, $southAfrica))   return 'south_africa';
        if (in_array($code, $europe))        return 'europe';

        return 'global';
    }

    private function matchesRegion(Bookmaker $bm, string $region): bool
    {
        $regions = $bm->regions ?? [];
        if (empty($regions)) {
            return true; // pas de restriction = visible partout
        }
        return in_array($region, $regions) || in_array('global', $regions);
    }
}
