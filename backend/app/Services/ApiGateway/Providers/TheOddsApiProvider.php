<?php

declare(strict_types=1);

namespace App\Services\ApiGateway\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TheOddsApiProvider
{
    private const BASE_URL = 'https://api.the-odds-api.com/v4';

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) config('services.the_odds_api.key', '');
    }

    public function fetchOdds(string $sport = 'soccer', string $region = 'eu'): array
    {
        if (empty($this->apiKey)) {
            Log::warning('TheOddsApiProvider: clé API non configurée (THE_ODDS_API_KEY)');
            return [];
        }

        try {
            $response = Http::timeout(10)
                ->get(self::BASE_URL . "/sports/{$sport}/odds", [
                    'apiKey'   => $this->apiKey,
                    'regions'  => $region,
                    'markets'  => 'h2h',
                ]);

            if (!$response->successful()) {
                Log::error("TheOddsApiProvider: erreur HTTP {$response->status()}");
                return [];
            }

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error("TheOddsApiProvider: exception fetchOdds", ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }
}
