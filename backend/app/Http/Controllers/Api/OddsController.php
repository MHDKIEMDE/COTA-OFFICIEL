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
        $this->apiKey = config('football-api.api_key', '');
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

        // Retourner immédiatement si déjà en cache (succès ou échec temporaire)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            if ($cached === false) {
                return response()->json(['success' => false, 'message' => 'Cotes non disponibles pour ce match'], 404);
            }
            return response()->json(['success' => true, 'data' => $cached]);
        }

        $result = null;
        try {
            $response = Http::timeout(10)
                ->withHeaders(['x-apisports-key' => $this->apiKey])
                ->get("{$this->baseUrl}/odds", ['fixture' => $matchId]);

            if (!$response->successful()) {
                Log::warning("Odds API-Football erreur {$response->status()} pour fixture {$matchId}");
                // 429 = quota dépassé : ne pas cacher pour réessayer plus tard
                if ($response->status() === 429) {
                    return response()->json(['success' => false, 'message' => 'Quota API dépassé, réessayez plus tard'], 429);
                }
                Cache::put($cacheKey, false, 300); // cacher l'échec 5 min seulement
                return response()->json(['success' => false, 'message' => 'Cotes non disponibles pour ce match'], 404);
            }

            $data     = $response->json();
            $fixtures = $data['response'] ?? [];

            if (empty($fixtures)) {
                Cache::put($cacheKey, false, 300);
                return response()->json(['success' => false, 'message' => 'Cotes non disponibles pour ce match'], 404);
            }

            $fixture    = $fixtures[0];
            $bookmakers = $fixture['bookmakers'] ?? [];

            if (empty($bookmakers)) {
                Cache::put($cacheKey, false, 300);
                return response()->json(['success' => false, 'message' => 'Cotes non disponibles pour ce match'], 404);
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

                $homeOdd = null; $drawOdd = null; $awayOdd = null;
                foreach ($matchWinner as $v) {
                    if ($v['value'] === 'Home') $homeOdd = (float) $v['odd'];
                    if ($v['value'] === 'Draw') $drawOdd = (float) $v['odd'];
                    if ($v['value'] === 'Away') $awayOdd = (float) $v['odd'];
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
                    'id'          => (string) $bmId,
                    'name'        => $bmName,
                    'priority'    => isset($this->priorityBookmakers[$bmId]) ? 0 : 1,
                    'home_win'    => $homeOdd,
                    'draw'        => $drawOdd,
                    'away_win'    => $awayOdd,
                    'over_25'     => $over25,
                    'under_25'    => $under25,
                    'btts_yes'    => $bttsYes,
                    'btts_no'     => $bttsNo,
                    'last_update' => now()->toIso8601String(),
                ];
            }

            usort($formatted, fn($a, $b) => $a['priority'] <=> $b['priority']);

            $result = [
                'match_id'   => $matchId,
                'home_team'  => $fixture['fixture']['teams']['home']['name'] ?? null,
                'away_team'  => $fixture['fixture']['teams']['away']['name'] ?? null,
                'bookmakers' => $formatted,
            ];

            Cache::put($cacheKey, $result, 600);

        } catch (\Exception $e) {
            Log::error("Odds exception fixture {$matchId}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur interne'], 500);
        }

        return response()->json(['success' => true, 'data' => $result]);
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
                ->with('blog:id,bookmaker_id,promo_code')
                ->get()
                ->filter(fn(Bookmaker $bm) => $this->matchesRegion($bm, $region))
                ->sortByDesc('clicks_count')
                ->map(fn(Bookmaker $bm) => $this->formatBookmaker($bm))
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
                $resp = Http::timeout(5)->get("http://ip-api.com/json/{$ip}?fields=continentCode,countryCode");
                if (!$resp->successful()) {
                    return 'global';
                }
                $continent   = strtoupper($resp->json('continentCode') ?? '');
                $countryCode = strtoupper($resp->json('countryCode') ?? '');
                return $this->resolveRegion($continent, $countryCode);
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

    private function resolveRegion(string $continent, string $countryCode): string
    {
        // Europe et autres continents → résolution directe sans liste de pays
        if ($continent === 'EU') return 'europe';
        if ($continent !== 'AF') return 'global';

        // Afrique uniquement : sous-régions nécessaires pour cibler les bookmakers
        if (in_array($countryCode, ['BF','ML','SN','CI','NE','GN','GW','MR','GM','SL','LR','GH','TG','BJ','NG','CV'])) {
            return 'west_africa';
        }
        if (in_array($countryCode, ['CM','CG','CD','GA','CF','TD','GQ','ST','AO'])) {
            return 'central_africa';
        }
        if (in_array($countryCode, ['KE','TZ','UG','RW','BI','ET','SO','DJ','ER','MZ','ZM','MW','ZW','MG','SC','MU'])) {
            return 'east_africa';
        }
        if (in_array($countryCode, ['MA','DZ','TN','EG','LY','SD','SS'])) {
            return 'north_africa';
        }
        if (in_array($countryCode, ['ZA','NA','BW','SZ','LS'])) {
            return 'south_africa';
        }

        return 'global';
    }

    private function matchesRegion(Bookmaker $bm, string $region): bool
    {
        $regions = $bm->regions ?? [];
        if (empty($regions)) {
            return true;
        }
        return in_array($region, $regions) || in_array('global', $regions);
    }

    // ── Helpers format ───────────────────────────────────────────────────────

    private function formatBookmaker(Bookmaker $bm): array
    {
        return [
            'api_id'              => (string) $bm->id,
            'name'                => $bm->name,
            'slug'                => $bm->slug,
            'primary_color'       => $bm->primary_color,
            'affiliate_link'      => $bm->affiliate_link,
            'download_link'       => $bm->download_link,
            'description'         => $bm->description,
            'logo_url'            => $bm->logo_url,
            'regions'             => $bm->regions ?? [],
            'is_configured'       => !empty($bm->affiliate_link),
            'clicks_count'        => $bm->clicks_count ?? 0,
            'popular_rank'        => $bm->popular_rank,
            'deposit_methods'     => $bm->deposit_methods ?? [],
            'withdrawal_methods'  => $bm->withdrawal_methods ?? [],
            'min_deposit'         => $bm->min_deposit,
            'min_withdrawal'      => $bm->min_withdrawal,
            'bonus_label'         => $bm->bonus_label,
            'rating'              => $bm->rating,
            'promo_code'          => $bm->blog?->promo_code,
        ];
    }

    // ── GET /api/bookmakers/all — liste complète (alpha ou popularité) ────────

    public function getAllBookmakers(Request $request): JsonResponse
    {
        $sort = $request->query('sort', 'popular'); // 'popular' | 'alpha'

        $bookmakers = Cache::remember("bookmakers:all:{$sort}", 3600, function () use ($sort) {
            $query = Bookmaker::active()->withCount('clicks');

            $collection = $query->get();

            if ($sort === 'alpha') {
                $collection = $collection->sortBy('name');
            } else {
                // popularité : popular_rank d'abord (null en dernier), puis clicks
                $collection = $collection->sortBy([
                    fn($a, $b) => ($a->popular_rank ?? 9999) <=> ($b->popular_rank ?? 9999),
                    fn($a, $b) => $b->clicks_count <=> $a->clicks_count,
                ]);
            }

            return $collection->map(fn(Bookmaker $bm) => $this->formatBookmaker($bm))->values()->all();
        });

        return response()->json([
            'success' => true,
            'sort'    => $sort,
            'data'    => $bookmakers,
            'count'   => count($bookmakers),
        ]);
    }

    // ── GET /api/bookmakers/{id}/detail — détail complet avec blog ───────────

    public function getBookmakerDetail(int $id): JsonResponse
    {
        $bm = Bookmaker::active()->withCount('clicks')->find($id);

        if (!$bm) {
            return response()->json(['success' => false, 'message' => 'Bookmaker introuvable.'], 404);
        }

        $blogs = \App\Models\BookmakerBlog::where('bookmaker_id', $id)
            ->active()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($b) => [
                'id'               => $b->id,
                'promo_code'       => $b->promo_code,
                'bonus_title'      => $b->bonus_title,
                'bonus_description'=> $b->bonus_description,
                'cta_label'        => $b->cta_label,
                'steps_count'      => count($b->steps ?? []),
                'created_at'       => $b->created_at?->toDateString(),
            ]);

        $tips = \App\Models\BookmakerTip::where('bookmaker_id', $id)
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn($t) => [
                'id'    => $t->id,
                'title' => $t->title,
                'icon'  => $t->icon,
                'tips'  => $t->tips ?? [],
            ]);

        return response()->json([
            'success' => true,
            'data'    => array_merge(
                $this->formatBookmaker($bm),
                ['blogs' => $blogs, 'tips' => $tips]
            ),
        ]);
    }

    /**
     * GET /api/bookmakers/{slug}/matches
     * Liste les matchs du jour disponibles sur ce bookmaker avec leurs cotes.
     * Croise nos prédictions du jour avec les cotes API-Football pour ce bookmaker.
     */
    public function getBookmakerMatches(Request $request, string $slug): JsonResponse
    {
        $bookmaker = Bookmaker::where('slug', $slug)->where('is_active', true)->first();

        if (!$bookmaker) {
            return response()->json(['success' => false, 'message' => 'Bookmaker introuvable.'], 404);
        }

        $date     = $request->query('date', now()->format('Y-m-d'));
        $cacheKey = "bookmaker_matches_{$slug}_{$date}";

        $data = Cache::remember($cacheKey, 900, function () use ($bookmaker, $date) {

            // Récupérer les prédictions publiées du jour
            $predictions = DB::table('predictions')
                ->where('is_published', true)
                ->whereDate('match_date', $date)
                ->orderBy('confidence_stars', 'desc')
                ->orderBy('total_score', 'desc')
                ->limit(20)
                ->get(['id', 'match_id', 'home_team', 'away_team', 'competition', 'country',
                       'match_date', 'match_time', 'prediction', 'bet_type', 'odds',
                       'confidence_stars', 'total_score', 'home_team_logo', 'away_team_logo',
                       'competition_logo', 'status']);

            if ($predictions->isEmpty()) {
                return ['matches' => [], 'bookmaker' => $bookmaker->name];
            }

            // Mapper le slug COTA vers le nom API-Football du bookmaker
            $bmApiName = $this->resolveApiFootballBookmakerName($bookmaker->slug);

            $matches = [];

            foreach ($predictions as $pred) {
                // Extraire l'ID numérique du fixture (format "apf_1234567" ou "1234567")
                $fixtureId = str_replace('apf_', '', $pred->match_id ?? '');
                if (!is_numeric($fixtureId)) {
                    // Pas d'ID API-Football — inclure quand même sans cote bookmaker
                    $matches[] = $this->buildMatchEntry($pred, null, null, null);
                    continue;
                }

                // Récupérer les cotes pour ce fixture
                $oddsCacheKey = "odds:apifootball:{$fixtureId}";
                $oddsData     = Cache::get($oddsCacheKey);

                $bmOdds = null;
                if ($oddsData && isset($oddsData['bookmakers'])) {
                    // Chercher ce bookmaker dans les cotes
                    foreach ($oddsData['bookmakers'] as $bm) {
                        if ($bmApiName && stripos($bm['name'] ?? '', $bmApiName) !== false) {
                            $bmOdds = $bm;
                            break;
                        }
                    }
                } elseif (empty($this->apiKey)) {
                    // Pas de clé API — inclure sans cote bookmaker
                    $matches[] = $this->buildMatchEntry($pred, null, null, null);
                    continue;
                } else {
                    // Fetch cotes depuis API-Football
                    try {
                        $response = Http::timeout(8)
                            ->withHeaders(['x-apisports-key' => $this->apiKey])
                            ->get("{$this->baseUrl}/odds", [
                                'fixture'    => $fixtureId,
                                'bookmaker'  => $this->resolveApiFootballBookmakerId($bookmaker->slug),
                            ]);

                        if ($response->successful()) {
                            $fixtures = $response->json('response', []);
                            if (!empty($fixtures[0]['bookmakers'])) {
                                $bms = $fixtures[0]['bookmakers'];
                                foreach ($bms as $bm) {
                                    if ($bmApiName && stripos($bm['name'] ?? '', $bmApiName) !== false) {
                                        $bmOdds = $this->parseBmOdds($bm);
                                        break;
                                    }
                                }
                                // Cacher pour les autres appels
                                Cache::put($oddsCacheKey, [
                                    'match_id'   => $fixtureId,
                                    'bookmakers' => array_map(fn($b) => $this->parseBmOdds($b), $bms),
                                ], 600);
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning("getBookmakerMatches: erreur cotes fixture {$fixtureId}", ['error' => $e->getMessage()]);
                    }
                }

                $matches[] = $this->buildMatchEntry($pred, $bmOdds, $bookmaker->slug, $fixtureId);
            }

            return [
                'bookmaker'      => $bookmaker->name,
                'bookmaker_slug' => $bookmaker->slug,
                'date'           => $date,
                'matches'        => $matches,
                'total'          => count($matches),
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ── Helpers privés ─────────────────────────────────────────────────────────

    private function buildMatchEntry(object $pred, ?array $bmOdds, ?string $slug, ?string $fixtureId): array
    {
        return [
            'prediction_id'   => $pred->id,
            'fixture_id'      => $fixtureId,
            'home_team'       => $pred->home_team,
            'away_team'       => $pred->away_team,
            'home_team_logo'  => $pred->home_team_logo,
            'away_team_logo'  => $pred->away_team_logo,
            'competition'     => $pred->competition,
            'competition_logo'=> $pred->competition_logo,
            'country'         => $pred->country,
            'match_date'      => $pred->match_date,
            'match_time'      => $pred->match_time,
            'cota_prediction' => $pred->prediction,
            'cota_bet_type'   => $pred->bet_type,
            'cota_odds'       => $pred->odds,
            'cota_stars'      => $pred->confidence_stars,
            'cota_score'      => round((float) $pred->total_score, 1),
            'status'          => $pred->status,
            // Cotes du bookmaker (null si non disponibles)
            'bm_home_win'     => $bmOdds['home_win'] ?? null,
            'bm_draw'         => $bmOdds['draw'] ?? null,
            'bm_away_win'     => $bmOdds['away_win'] ?? null,
            'bm_over_25'      => $bmOdds['over_25'] ?? null,
            'bm_under_25'     => $bmOdds['under_25'] ?? null,
            'bm_btts_yes'     => $bmOdds['btts_yes'] ?? null,
            'has_bm_odds'     => $bmOdds !== null,
            // Lien de recherche direct sur le bookmaker
            'search_url'      => $this->buildSearchUrl($slug, $pred->home_team, $pred->away_team),
        ];
    }

    private function parseBmOdds(array $bm): array
    {
        $homeOdd = $drawOdd = $awayOdd = $over25 = $under25 = $bttsYes = $bttsNo = null;

        foreach ($bm['bets'] ?? [] as $bet) {
            if ($bet['name'] === 'Match Winner') {
                foreach ($bet['values'] as $v) {
                    if ($v['value'] === 'Home') $homeOdd = (float) $v['odd'];
                    if ($v['value'] === 'Draw') $drawOdd = (float) $v['odd'];
                    if ($v['value'] === 'Away') $awayOdd = (float) $v['odd'];
                }
            } elseif (str_contains($bet['name'], 'Goals Over/Under')) {
                foreach ($bet['values'] as $v) {
                    if (str_contains($v['value'], 'Over 2.5'))  $over25  = (float) $v['odd'];
                    if (str_contains($v['value'], 'Under 2.5')) $under25 = (float) $v['odd'];
                }
            } elseif ($bet['name'] === 'Both Teams Score') {
                foreach ($bet['values'] as $v) {
                    if ($v['value'] === 'Yes') $bttsYes = (float) $v['odd'];
                    if ($v['value'] === 'No')  $bttsNo  = (float) $v['odd'];
                }
            }
        }

        return [
            'name'      => $bm['name'] ?? '',
            'home_win'  => $homeOdd,
            'draw'      => $drawOdd,
            'away_win'  => $awayOdd,
            'over_25'   => $over25,
            'under_25'  => $under25,
            'btts_yes'  => $bttsYes,
            'btts_no'   => $bttsNo,
        ];
    }

    /** Correspondance slug COTA → nom bookmaker dans API-Football */
    private function resolveApiFootballBookmakerName(string $slug): ?string
    {
        return match($slug) {
            '1xbet'        => '1xBet',
            'betwinner'    => 'BetWinner',
            'melbet'       => 'Melbet',
            'bet365'       => 'Bet365',
            'betclic'      => 'Betclic',
            'betway-africa'=> 'Betway',
            '22bet'        => '22Bet',
            'paripesa'     => 'PariPesa',
            default        => null,
        };
    }

    /** ID bookmaker dans API-Football pour filtrage précis */
    private function resolveApiFootballBookmakerId(string $slug): ?int
    {
        return match($slug) {
            '1xbet'    => 8,
            'betwinner'=> 71,
            'melbet'   => 80,
            'bet365'   => 8,
            'betclic'  => 10,
            default    => null,
        };
    }

    /** URL de recherche directe sur le bookmaker */
    private function buildSearchUrl(string $slug, string $homeTeam, string $awayTeam): string
    {
        $query = urlencode("{$homeTeam} {$awayTeam}");

        return match($slug) {
            '1xbet'         => "https://1xbet.com/fr/search?q={$query}",
            'betwinner'     => "https://betwinner.com/fr/search/?q={$query}",
            'melbet'        => "https://melbet.com/fr/search?q={$query}",
            'bet365'        => "https://www.bet365.com/fr/#/IP/",
            'betclic'       => "https://www.betclic.fr/football-s1",
            'betway-africa' => "https://betway.com/fr/sport/evt/football",
            '22bet'         => "https://22bet.com/fr/search?q={$query}",
            'paripesa'      => "https://paripesa.bet/fr/search?q={$query}",
            default         => "https://www.google.com/search?q={$homeTeam}+vs+{$awayTeam}+{$slug}",
        };
    }
}
