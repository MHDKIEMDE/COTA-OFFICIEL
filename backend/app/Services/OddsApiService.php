<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Cotes pré-match 1xBet via The Odds API (api.the-odds-api.com)
 *
 * Stratégie :
 * - loadDailyOdds() : appelé une fois par génération, charge toutes les cotes
 *   des ligues actives en cache 4h (1 requête/sport)
 * - find() : recherche instantanée dans le cache par noms d'équipes
 *
 * Quota : 500 req/mois gratuit — on consomme ~10-15 req/jour max.
 */
class OddsApiService
{
    private const API_BASE    = 'https://api.the-odds-api.com/v4';
    private const BOOKMAKER   = 'onexbet';
    private const CACHE_TTL   = 14400; // 4 heures
    private const CACHE_KEY   = 'odds_api_daily_index';

    // Sports The Odds API correspondant aux ligues tier 1–3 de COTA
    private const SPORT_KEYS = [
        'soccer_epl',                           // Premier League
        'soccer_spain_la_liga',                 // La Liga
        'soccer_germany_bundesliga',            // Bundesliga
        'soccer_italy_serie_a',                 // Serie A
        'soccer_france_ligue_one',              // Ligue 1
        'soccer_uefa_champs_league',            // Champions League
        'soccer_uefa_europa_league',            // Europa League
        'soccer_uefa_europa_conference_league', // Conference League
        'soccer_portugal_primeira_liga',        // Liga Portugal
        'soccer_netherlands_eredivisie',        // Eredivisie
        'soccer_belgium_first_div',             // Pro League
        'soccer_turkey_super_league',           // Süper Lig
        'soccer_saudi_arabia_pro_league',       // Saudi Pro League
        'soccer_brazil_campeonato',             // Brasileirao
        'soccer_mexico_ligamx',                 // Liga MX
        'soccer_usa_mls',                       // MLS
        'soccer_china_superleague',             // Super League Chine
        'soccer_korea_kleague1',                // K League
        'soccer_africa_cup_of_nations',         // AFCON
        'soccer_spl',                           // Scottish Premiership
        'soccer_efl_champ',                     // Championship
    ];

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('ODDS_API_KEY', '');
    }

    /**
     * Charger toutes les cotes 1xBet du jour en cache.
     * À appeler UNE FOIS avant la boucle de génération des prédictions.
     * Retourne le nombre de matchs indexés.
     */
    public function loadDailyOdds(): int
    {
        if (empty($this->apiKey)) {
            Log::warning('[OddsApi] ODDS_API_KEY non configuré');
            return 0;
        }

        if (Cache::has(self::CACHE_KEY)) {
            $index = Cache::get(self::CACHE_KEY, []);
            Log::info('[OddsApi] Cache déjà chaud', ['matchs' => count($index)]);
            return count($index);
        }

        $index = [];
        $requestsUsed = 0;

        foreach (self::SPORT_KEYS as $sportKey) {
            try {
                $response = Http::timeout(15)->get(self::API_BASE . "/sports/{$sportKey}/odds/", [
                    'apiKey'      => $this->apiKey,
                    'regions'     => 'eu',
                    'markets'     => 'h2h,totals',
                    'bookmakers'  => self::BOOKMAKER,
                    'oddsFormat'  => 'decimal',
                ]);

                $requestsUsed++;

                if (!$response->successful()) {
                    Log::warning("[OddsApi] Erreur {$sportKey}", ['status' => $response->status()]);
                    continue;
                }

                $events = $response->json() ?? [];

                foreach ($events as $event) {
                    $parsed = $this->parseEvent($event);
                    if ($parsed) {
                        // Indexer par clé normalisée "home vs away"
                        $key = $this->normalizeKey($parsed['home'], $parsed['away']);
                        $index[$key] = $parsed;
                        // Aussi par clé inversée pour absorber les différences d'ordre
                        $keyInv = $this->normalizeKey($parsed['away'], $parsed['home']);
                        $index[$keyInv] = array_merge($parsed, ['_inverted' => true]);
                    }
                }

            } catch (\Throwable $e) {
                Log::warning("[OddsApi] Exception {$sportKey}: " . $e->getMessage());
            }
        }

        Cache::put(self::CACHE_KEY, $index, self::CACHE_TTL);

        Log::info('[OddsApi] Index chargé', [
            'sports'   => count(self::SPORT_KEYS),
            'requetes' => $requestsUsed,
            'matchs'   => count($index) / 2, // divisé par 2 car doublons inversés
        ]);

        return (int) (count($index) / 2);
    }

    /**
     * Trouver les cotes 1xBet pour un match donné.
     * Retourne null si introuvable.
     *
     * @return array{home: float, draw: float, away: float, over25: float|null, under25: float|null}|null
     */

    /**
     * Vider le cache (forcer rechargement).
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function parseEvent(array $event): ?array
    {
        $home = $event['home_team'] ?? null;
        $away = $event['away_team'] ?? null;
        if (!$home || !$away) return null;

        $result = [
            'home'    => $home,
            'away'    => $away,
            'time'    => $event['commence_time'] ?? null,
            'sport'   => $event['sport_key'] ?? null,
            'h2h'     => [],
            'over25'  => null,
            'under25' => null,
        ];

        $bookmaker = collect($event['bookmakers'] ?? [])
            ->firstWhere('key', self::BOOKMAKER);

        if (!$bookmaker) return null;

        foreach ($bookmaker['markets'] ?? [] as $market) {
            if ($market['key'] === 'h2h') {
                foreach ($market['outcomes'] ?? [] as $o) {
                    $result['h2h'][$o['name']] = (float) $o['price'];
                }
            }
            if ($market['key'] === 'totals') {
                foreach ($market['outcomes'] ?? [] as $o) {
                    if (($o['point'] ?? null) == 2.5) {
                        $name = strtolower($o['name'] ?? '');
                        if (str_contains($name, 'over'))  $result['over25']  = (float) $o['price'];
                        if (str_contains($name, 'under')) $result['under25'] = (float) $o['price'];
                    }
                }
            }
        }

        if (empty($result['h2h'])) return null;

        return $result;
    }

    private function extractOdds(array $entry): array
    {
        $h2h  = $entry['h2h'] ?? [];
        $home = $entry['home'] ?? '';
        $away = $entry['away'] ?? '';

        // Chercher home/draw/away dans les outcomes (nommés par équipe dans The Odds API)
        $homeOdds = $h2h[$home] ?? null;
        $awayOdds = $h2h[$away] ?? null;
        $drawOdds = $h2h['Draw'] ?? null;

        // Fallback si noms légèrement différents
        if (!$homeOdds || !$awayOdds) {
            $values = array_values($h2h);
            sort($values);
            if (count($values) >= 2) {
                $homeOdds = $homeOdds ?? ($values[1] ?? null); // tendance : home > draw
                $awayOdds = $awayOdds ?? ($values[0] ?? null);
                $drawOdds = $drawOdds ?? ($values[count($values) > 2 ? 1 : 0] ?? null);
            }
        }

        return [
            'home'    => $homeOdds ? round((float) $homeOdds, 2) : null,
            'draw'    => $drawOdds ? round((float) $drawOdds, 2) : null,
            'away'    => $awayOdds ? round((float) $awayOdds, 2) : null,
            'over25'  => $entry['over25']  ? round((float) $entry['over25'],  2) : null,
            'under25' => $entry['under25'] ? round((float) $entry['under25'], 2) : null,
        ];
    }

    private function normalizeKey(string $a, string $b): string
    {
        return $this->normalizeName($a) . '|||' . $this->normalizeName($b);
    }

    private function normalizeName(string $name): string
    {
        // Supprimer accents, mettre en minuscules, retirer ponctuation
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $normalized = strtolower($normalized ?? $name);
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);
        return trim(preg_replace('/\s+/', ' ', $normalized) ?? '');
    }

    private function keyWords(string $name): array
    {
        // Mots à ignorer seulement s'ils ne sont pas le nom complet
        $stop = ['city', 'united', 'club', 'fc', 'cf', 'sc', 'ac', 'the', 'de', 'du', 'and'];
        $words = preg_split('/[\s\-\.]+/', strtolower($this->normalizeName($name)));
        // Garder les mots ≥ 3 chars (pour PSG, AS...) sauf stop words
        $significant = array_values(array_filter($words, fn($w) => strlen($w) >= 3 && !in_array($w, $stop)));
        // Si rien de significatif (ex: "FC"), retourner tous les mots ≥ 2 chars
        return $significant ?: array_values(array_filter($words, fn($w) => strlen($w) >= 2));
    }

    /**
     * Score de similarité entre deux noms d'équipes (0-100).
     * Utilise similar_text + comparaison des mots-clés.
     */
    private function teamSimilarity(string $a, string $b): int
    {
        $na = $this->normalizeName($a);
        $nb = $this->normalizeName($b);

        // Correspondance exacte après normalisation
        if ($na === $nb) return 100;

        // similar_text score
        similar_text($na, $nb, $pct);

        // Bonus si les mots-clés se recoupent
        $wordsA = $this->keyWords($a);
        $wordsB = $this->keyWords($b);
        $common = count(array_intersect($wordsA, $wordsB));
        $bonus  = $common > 0 ? min(20, $common * 10) : 0;

        return (int) min(100, $pct + $bonus);
    }

    public function find(string $homeTeam, string $awayTeam): ?array
    {
        $index = Cache::get(self::CACHE_KEY, []);
        if (empty($index)) return null;

        // 1. Recherche exacte normalisée
        $key = $this->normalizeKey($homeTeam, $awayTeam);
        if (isset($index[$key])) {
            return $this->extractOdds($index[$key]);
        }

        // 2. Recherche par mots-clés (intersection)
        $homeWords = $this->keyWords($homeTeam);
        $awayWords = $this->keyWords($awayTeam);

        $bestScore = 0;
        $bestEntry = null;

        foreach ($index as $entry) {
            if (isset($entry['_inverted'])) continue;

            $entryHome = $entry['home'] ?? '';
            $entryAway = $entry['away'] ?? '';

            // Score de similarité pondéré home + away
            $homeScore = $this->teamSimilarity($homeTeam, $entryHome);
            $awayScore = $this->teamSimilarity($awayTeam, $entryAway);
            $score     = (int) round(($homeScore + $awayScore) / 2);

            // Fallback mots-clés pour détecter les correspondances partielles rapides
            $homeKwMatch = !empty(array_intersect($homeWords, $this->keyWords($entryHome)));
            $awayKwMatch = !empty(array_intersect($awayWords, $this->keyWords($entryAway)));
            if ($homeKwMatch && $awayKwMatch && $score < 60) $score = 60;

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestEntry = $entry;
            }
        }

        // Seuil : 65% de similarité minimum pour accepter une correspondance
        if ($bestScore >= 65 && $bestEntry !== null) {
            Log::debug("OddsApiService: fuzzy match [{$homeTeam} vs {$awayTeam}] → [{$bestEntry['home']} vs {$bestEntry['away']}] score={$bestScore}");
            return $this->extractOdds($bestEntry);
        }

        return null;
    }
}
