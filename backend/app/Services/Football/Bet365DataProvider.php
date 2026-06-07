<?php

declare(strict_types=1);

namespace App\Services\Football;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Provider complémentaire — Bet365Data (RapidAPI)
 *
 * Couverture : cotes Bet365 en temps réel pour tennis (96 ligues)
 * et basketball (72 ligues dont NBA, EuroLeague).
 *
 * Football NON disponible sur ce plan → ce provider ne gère que
 * les cotes additionnelles pour enrichir les prédictions tennis/basket.
 *
 * Méthodes principales :
 *   getLeagues(sport)       → liste des ligues + fixture IDs
 *   getEventOdds(sport, fi) → marchés détaillés pour un match
 *   getMarketOdds(fi)       → cotes Money Line / Spread / Total extraites
 */
class Bet365DataProvider implements FootballProviderInterface
{
    private const BASE_URL  = 'https://bet365data.p.rapidapi.com';
    private const HOST      = 'bet365data.p.rapidapi.com';
    private const CACHE_TTL = 300; // 5 min — les cotes bougent

    /** Sports supportés par ce provider */
    private const SUPPORTED_SPORTS = ['tennis', 'basketball'];

    public function name(): string
    {
        return 'bet365data';
    }

    public function isAvailable(): bool
    {
        return !empty(config('services.bet365data.key', env('BET365DATA_KEY', '')));
    }

    /**
     * Non utilisé pour le football — retourne [] sans appel API.
     * Ce provider est utilisé via getLeagues() / getEventOdds() directement.
     */
    public function getFixtures(?string $date = null): array
    {
        return [];
    }

    /**
     * Non utilisé pour le live football.
     */
    public function getLiveMatches(): array
    {
        return [];
    }

    // ── API Bet365Data ────────────────────────────────────────────────────────

    /**
     * Liste toutes les ligues + events d'un sport avec leurs fixture IDs.
     *
     * @param string $sport  'tennis' | 'basketball'
     * @return array{total: int, leagues: array}
     */
    public function getLeagues(string $sport): array
    {
        if (!in_array($sport, self::SUPPORTED_SPORTS, true)) {
            Log::warning("Bet365Data: sport '{$sport}' non supporté");
            return ['total' => 0, 'leagues' => []];
        }

        $cacheKey = "bet365_leagues_{$sport}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($sport) {
            try {
                $response = $this->http()->get(self::BASE_URL . "/{$sport}/leagues");

                if (!$response->successful()) {
                    Log::warning("Bet365Data: leagues non-200 [{$sport}]", ['status' => $response->status()]);
                    return ['total' => 0, 'leagues' => []];
                }

                $data = $response->json();
                Log::info("Bet365Data: leagues chargées [{$sport}]", ['total' => $data['total'] ?? 0]);

                return [
                    'total'   => $data['total'] ?? 0,
                    'leagues' => $data['leagues'] ?? [],
                ];
            } catch (\Throwable $e) {
                Log::error("Bet365DataProvider::getLeagues [{$sport}] failed", ['error' => $e->getMessage()]);
                return ['total' => 0, 'leagues' => []];
            }
        });
    }

    /**
     * Récupère tous les événements d'un sport avec leurs fixture IDs.
     * Extrait directement depuis getLeagues() — pas d'appel API supplémentaire.
     *
     * @return array<int, array{fi: string, sport: string, league: string, home: string, away: string, bc: string}>
     */
    public function getAllEvents(string $sport): array
    {
        $data    = $this->getLeagues($sport);
        $events  = [];

        foreach ($data['leagues'] as $league) {
            $leagueName = $league['league'] ?? ($league['leagueName'] ?? '');
            foreach ($league['events'] ?? [] as $event) {
                $events[] = [
                    'fi'     => $event['fi'] ?? '',
                    'sport'  => $sport,
                    'league' => $leagueName,
                    'home'   => $event['home'] ?? '',
                    'away'   => $event['away'] ?? '',
                    'bc'     => $event['bc'] ?? '',  // broadcast time: YYYYMMDDHHmmss
                    'live'   => (bool) ($league['live'] ?? false),
                ];
            }
        }

        return $events;
    }

    /**
     * Cotes détaillées d'un match spécifique.
     *
     * @param string $sport  'tennis' | 'basketball'
     * @param string $fi     Fixture ID Bet365
     * @return array         Marchés structurés ou [] si non disponible
     */
    public function getEventOdds(string $sport, string $fi): array
    {
        if (!in_array($sport, self::SUPPORTED_SPORTS, true) || empty($fi)) {
            return [];
        }

        $cacheKey = "bet365_odds_{$sport}_{$fi}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($sport, $fi) {
            try {
                $response = $this->http()->get(self::BASE_URL . "/{$sport}/events/{$fi}");

                if (!$response->successful()) {
                    return [];
                }

                $data = $response->json('data', []);
                if (empty($data)) {
                    return [];
                }

                return $this->normalizeEventOdds($data[0] ?? [], $sport);
            } catch (\Throwable $e) {
                Log::warning("Bet365DataProvider::getEventOdds [{$sport}/{$fi}] failed", ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Cotes Money Line / Spread / Total extraites simplement.
     * Utile pour enrichir l'affichage sans parser tout le détail.
     *
     * @return array{money_line: array|null, spread: array|null, total: array|null}
     */
    public function getMarketOdds(string $sport, string $fi): array
    {
        $full = $this->getEventOdds($sport, $fi);

        return [
            'money_line' => $full['money_line'] ?? null,
            'spread'     => $full['spread'] ?? null,
            'total'      => $full['total'] ?? null,
            'source'     => $this->name(),
            'fi'         => $fi,
            'sport'      => $sport,
        ];
    }

    /**
     * Trouver le fixture ID Bet365 pour un match par nom d'équipes.
     * Recherche approximative sur home/away.
     */
    public function findEventFi(string $sport, string $home, string $away): ?string
    {
        $events = $this->getAllEvents($sport);
        $homeL  = strtolower($home);
        $awayL  = strtolower($away);

        foreach ($events as $event) {
            $h = strtolower($event['home']);
            $a = strtolower($event['away']);

            $homeMatch = str_contains($h, $homeL) || str_contains($homeL, $h);
            $awayMatch = str_contains($a, $awayL) || str_contains($awayL, $a);

            if ($homeMatch && $awayMatch) {
                return $event['fi'];
            }
        }

        return null;
    }

    // ── Normalisation interne ─────────────────────────────────────────────────

    private function normalizeEventOdds(array $event, string $sport): array
    {
        $result = [
            'fi'          => $event['fi'] ?? '',
            'sport'       => $sport,
            'home'        => $event['home'] ?? '',
            'away'        => $event['away'] ?? '',
            'league'      => $event['league'] ?? '',
            'live'        => (bool) ($event['live'] ?? false),
            'money_line'  => null,
            'spread'      => null,
            'total'       => null,
            'markets'     => [],
        ];

        foreach ($event['mg'] ?? [] as $marketGroup) {
            $groupName = $marketGroup['name'] ?? '';

            foreach ($marketGroup['ma'] ?? [] as $market) {
                $marketName = $market['name'] ?? '';
                $picks      = [];

                foreach ($market['pa'] ?? [] as $pa) {
                    $decimal = $pa['decimal'] ?? '';
                    if ($decimal === '' || $decimal === null) {
                        continue;
                    }

                    $picks[] = [
                        'name'     => $pa['NA'] ?? $pa['name'] ?? '',
                        'handicap' => $pa['HA'] ?? null,
                        'decimal'  => (float) $decimal,
                        'id'       => $pa['ID'] ?? null,
                    ];
                }

                if (empty($picks)) {
                    continue;
                }

                // Identifier les marchés principaux
                if ($marketName === 'Money Line' || $marketName === 'To Win Match') {
                    $result['money_line'] = $picks;
                } elseif ($marketName === 'Spread') {
                    $result['spread'] = $picks;
                } elseif ($marketName === 'Total') {
                    $result['total'] = $picks;
                }

                $result['markets'][] = [
                    'group'  => $groupName,
                    'market' => $marketName,
                    'picks'  => $picks,
                ];
            }
        }

        return $result;
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'x-rapidapi-host' => self::HOST,
            'x-rapidapi-key'  => config('services.bet365data.key', env('BET365DATA_KEY', '')),
        ])->timeout(12);
    }
}
