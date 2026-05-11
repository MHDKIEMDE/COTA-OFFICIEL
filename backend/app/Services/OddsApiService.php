<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service pour récupérer les cotes des bookmakers en temps réel
 * 
 * Utilise l'API The Odds API (https://the-odds-api.com/)
 * Alternative: BetAPI (https://betapi.io/)
 * 
 * IMPORTANT: Ce service ne stocke PAS les données en base de données
 * Il fait uniquement du proxy/cache pour optimiser les performances
 */
class OddsApiService
{
    private $apiKey;
    private $baseUrl = 'https://api.the-odds-api.com/v4';
    private $cacheTtl;

    public function __construct()
    {
        $this->apiKey = env('ODDS_API_KEY');
        $this->cacheTtl = env('ODDS_API_CACHE_TTL', 120); // 2 minutes par défaut
    }

    /**
     * Récupérer les cotes pour un match spécifique
     * 
     * @param string $sportKey 'soccer' ou 'soccer_epl', 'soccer_uefa_champs_league', etc.
     * @param string $matchId ID du match (fixture_id ou sportradar_id)
     * @param array $bookmakers ['bet365', '1xbet', 'betway', 'betwinner']
     * @return array|null
     */
    public function getMatchOdds(string $sportKey, string $matchId, array $bookmakers = [])
    {
        // Cache de 2 minutes pour éviter trop de requêtes
        $cacheKey = "odds:{$sportKey}:{$matchId}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($sportKey, $matchId, $bookmakers) {
            try {
                // Pour l'instant, on utilise l'endpoint général
                // TODO: Utiliser l'endpoint spécifique par match si disponible
                $url = "{$this->baseUrl}/sports/{$sportKey}/odds";
                
                $params = [
                    'apiKey' => $this->apiKey,
                    'regions' => 'us,eu,uk', // Multiples régions pour plus de bookmakers
                    'markets' => 'h2h,spreads,totals', // h2h = 1X2, spreads = handicap, totals = over/under
                    'oddsFormat' => 'decimal',
                    'dateFormat' => 'iso',
                ];

                if (!empty($bookmakers)) {
                    $params['bookmakers'] = implode(',', $bookmakers);
                }

                Log::info("📊 Récupération cotes pour match: {$matchId} (sport: {$sportKey})");

                $response = Http::timeout(15)->get($url, $params);

                if (!$response->successful()) {
                    Log::error("❌ Odds API Error: " . $response->status() . " - " . $response->body());
                    return null;
                }

                $data = $response->json();
                
                if (!is_array($data) || empty($data)) {
                    Log::warning("⚠️ Aucune donnée retournée par Odds API");
                    return null;
                }

                // Chercher le match correspondant dans les résultats
                // Note: L'API peut retourner plusieurs matchs, on doit trouver le bon
                foreach ($data as $event) {
                    // Comparer par ID ou par équipes
                    if (isset($event['id']) && $event['id'] === $matchId) {
                        return $this->formatOdds($event);
                    }
                }

                // Si pas trouvé par ID, retourner le premier match (fallback)
                // TODO: Améliorer la correspondance avec les équipes
                if (!empty($data)) {
                    Log::info("ℹ️ Match non trouvé par ID, utilisation du premier résultat");
                    return $this->formatOdds($data[0]);
                }

                return null;
            } catch (\Exception $e) {
                Log::error("❌ Odds API Exception: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Récupérer les cotes pour plusieurs matchs en une requête
     * 
     * @param string $sportKey
     * @param array $matchIds
     * @param array $bookmakers
     * @return array
     */
    public function getBatchOdds(string $sportKey, array $matchIds, array $bookmakers = []): array
    {
        $results = [];
        
        // Limiter à 10 matchs par requête pour éviter timeout
        $chunks = array_chunk($matchIds, 10);
        
        foreach ($chunks as $chunk) {
            foreach ($chunk as $matchId) {
                $odds = $this->getMatchOdds($sportKey, $matchId, $bookmakers);
                if ($odds) {
                    $results[$matchId] = $odds;
                }
            }
        }
        
        return $results;
    }

    /**
     * Formater les cotes pour notre format standard
     * 
     * @param array $event Données brutes de l'API
     * @return array Format standardisé
     */
    private function formatOdds(array $event): array
    {
        $formatted = [
            'match_id' => $event['id'] ?? null,
            'sport_key' => $event['sport_key'] ?? null,
            'home_team' => $event['home_team'] ?? null,
            'away_team' => $event['away_team'] ?? null,
            'commence_time' => $event['commence_time'] ?? null,
            'bookmakers' => [],
        ];

        // Extraire les cotes de chaque bookmaker
        foreach ($event['bookmakers'] ?? [] as $bookmaker) {
            $bookmakerOdds = [
                'id' => $bookmaker['key'] ?? null,
                'name' => $bookmaker['title'] ?? null,
                'last_update' => $bookmaker['last_update'] ?? now()->toIso8601String(),
                'home_win' => null,
                'draw' => null,
                'away_win' => null,
                'over_25' => null,
                'under_25' => null,
            ];

            // Extraire les cotes 1X2 (h2h = head-to-head)
            foreach ($bookmaker['markets'] ?? [] as $market) {
                if ($market['key'] === 'h2h') {
                    foreach ($market['outcomes'] ?? [] as $outcome) {
                        $name = $outcome['name'] ?? '';
                        $price = $outcome['price'] ?? null;
                        
                        // Identifier le type de cote
                        if (stripos($name, $formatted['home_team']) !== false || 
                            $name === '1' || 
                            stripos($name, 'home') !== false) {
                            $bookmakerOdds['home_win'] = $price;
                        } elseif ($name === 'X' || stripos($name, 'draw') !== false) {
                            $bookmakerOdds['draw'] = $price;
                        } elseif (stripos($name, $formatted['away_team']) !== false || 
                                  $name === '2' || 
                                  stripos($name, 'away') !== false) {
                            $bookmakerOdds['away_win'] = $price;
                        }
                    }
                }
                
                // Extraire les cotes Over/Under 2.5
                if ($market['key'] === 'totals') {
                    foreach ($market['outcomes'] ?? [] as $outcome) {
                        $name = $outcome['name'] ?? '';
                        $point = $outcome['point'] ?? null;
                        $price = $outcome['price'] ?? null;
                        
                        if ($point == 2.5) {
                            if (stripos($name, 'over') !== false) {
                                $bookmakerOdds['over_25'] = $price;
                            } elseif (stripos($name, 'under') !== false) {
                                $bookmakerOdds['under_25'] = $price;
                            }
                        }
                    }
                }
            }

            // Ne garder que les bookmakers avec au moins une cote
            if ($bookmakerOdds['home_win'] || $bookmakerOdds['draw'] || $bookmakerOdds['away_win']) {
                $formatted['bookmakers'][] = $bookmakerOdds;
            }
        }

        return $formatted;
    }

    /**
     * Obtenir la liste des bookmakers disponibles
     * 
     * @return array
     */
    public function getAvailableBookmakers(): array
    {
        $cacheKey = 'odds:bookmakers:list';
        
        return Cache::remember($cacheKey, 3600, function () { // Cache 1 heure
            try {
                $url = "{$this->baseUrl}/sports";
                $response = Http::timeout(10)->get($url, [
                    'apiKey' => $this->apiKey,
                ]);

                if (!$response->successful()) {
                    return [];
                }

                // L'API retourne les sports, pas directement les bookmakers
                // Pour obtenir les bookmakers, on doit faire une requête de test
                // Pour l'instant, retourner une liste statique des bookmakers populaires
                return [
                    'bet365',
                    '1xbet',
                    'betway',
                    'betwinner',
                    'melbet',
                    'williamhill',
                    'pinnacle',
                ];
            } catch (\Exception $e) {
                Log::error("Erreur récupération bookmakers: " . $e->getMessage());
                return [];
            }
        });
    }
}
