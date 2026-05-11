<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Service pour interagir avec l'API AffiliateControl
 * 
 * Documentation: https://affiliatecontrol-api.com/affiliates/docs/
 */
class AffiliateControlService
{
    private string $baseUrl = 'https://affiliatecontrol-api.com/affiliates';
    private ?string $customerId;
    private ?string $accessKey;
    private ?string $secretKey;

    public function __construct()
    {
        $this->customerId = config('affiliates.api.customer_id');
        $this->accessKey = config('affiliates.api.access_key');
        $this->secretKey = config('affiliates.api.secret_key');
    }

    /**
     * Vérifier si un player_id existe dans nos conversions
     * 
     * @param string $playerId ID du joueur sur le bookmaker
     * @param string|null $bookmaker Filtrer par bookmaker (optionnel)
     * @return array ['found' => bool, 'conversion' => array|null]
     */
    public function verifyPlayerConversion(string $playerId, ?string $bookmaker = null): array
    {
        try {
            // Chercher dans les conversions CPA
            $response = $this->makeRequest('GET', '/cpa-conversions', [
                'query' => [
                    'filter[playerId]' => $playerId,
                ],
            ]);

            if (!$response['success']) {
                return [
                    'found' => false,
                    'error' => $response['error'] ?? 'Erreur API',
                ];
            }

            $conversions = $response['data'] ?? [];

            // Chercher une conversion avec ce player_id
            foreach ($conversions as $conversion) {
                if (isset($conversion['playerId']) && $conversion['playerId'] === $playerId) {
                    // Si bookmaker spécifié, vérifier qu'il correspond
                    if ($bookmaker && isset($conversion['offer'])) {
                        $offerName = strtolower($conversion['offer']['name'] ?? '');
                        if (strpos($offerName, strtolower($bookmaker)) === false) {
                            continue;
                        }
                    }

                    return [
                        'found' => true,
                        'conversion' => [
                            'player_id' => $conversion['playerId'],
                            'created_at' => $conversion['createdAt'] ?? null,
                            'offer' => $conversion['offer']['name'] ?? null,
                            'status' => $conversion['status'] ?? null,
                            'revenue' => $conversion['revenue'] ?? null,
                        ],
                    ];
                }
            }

            return [
                'found' => false,
                'message' => 'Player ID non trouvé dans les conversions',
            ];

        } catch (\Exception $e) {
            Log::error('AffiliateControl API error', [
                'message' => $e->getMessage(),
                'player_id' => $playerId,
            ]);

            return [
                'found' => false,
                'error' => 'Erreur de communication avec AffiliateControl',
            ];
        }
    }

    /**
     * Récupérer toutes les conversions récentes
     * 
     * @param int $limit Nombre max de résultats
     * @param array $filters Filtres additionnels
     * @return array
     */
    public function getConversions(int $limit = 100, array $filters = []): array
    {
        $query = array_merge([
            'limit' => $limit,
            'sort' => '-createdAt', // Plus récent en premier
        ], $filters);

        return $this->makeRequest('GET', '/cpa-conversions', ['query' => $query]);
    }

    /**
     * Récupérer les rapports de joueurs
     * 
     * @param array $filters
     * @return array
     */
    public function getPlayersReport(array $filters = []): array
    {
        return $this->makeRequest('POST', '/reports', [
            'json' => array_merge([
                'type' => 'players',
            ], $filters),
        ]);
    }

    /**
     * Faire une requête à l'API AffiliateControl
     * 
     * @param string $method GET, POST, PUT, DELETE
     * @param string $endpoint
     * @param array $options
     * @return array
     */
    private function makeRequest(string $method, string $endpoint, array $options = []): array
    {
        if (!$this->customerId || !$this->accessKey || !$this->secretKey) {
            Log::warning('AffiliateControl API credentials not configured');
            return [
                'success' => false,
                'error' => 'API credentials not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'X-Customer-Id' => $this->customerId,
                'X-Access-Key' => $this->accessKey,
                'X-Secret-Key' => $this->secretKey,
                'Accept' => 'application/json',
            ])->{strtolower($method)}($this->baseUrl . $endpoint, $options['query'] ?? $options['json'] ?? []);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data') ?? $response->json(),
                ];
            }

            Log::warning('AffiliateControl API request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Request failed',
                'status' => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('AffiliateControl API exception', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier si les credentials API sont configurés
     */
    public function isConfigured(): bool
    {
        return !empty($this->customerId) && !empty($this->accessKey) && !empty($this->secretKey);
    }
}

