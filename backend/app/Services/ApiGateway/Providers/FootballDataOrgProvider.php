<?php

declare(strict_types=1);

namespace App\Services\ApiGateway\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FootballDataOrgProvider
{
    private const BASE_URL = 'https://api.football-data.org/v4';

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) config('services.football_data_org.key', '');
    }

    public function fetchMatches(string $date): array
    {
        if (empty($this->apiKey)) {
            Log::warning('FootballDataOrgProvider: clé API non configurée (FOOTBALL_DATA_ORG_KEY)');
            return ['matches' => []];
        }

        $start = microtime(true);

        try {
            $response = Http::withHeaders(['X-Auth-Token' => $this->apiKey])
                ->timeout(10)
                ->get(self::BASE_URL . '/matches', [
                    'dateFrom' => $date,
                    'dateTo'   => $date,
                ]);

            $ms = (int) ((microtime(true) - $start) * 1000);

            if (!$response->successful()) {
                Log::error("FootballDataOrgProvider: erreur HTTP {$response->status()}", [
                    'date' => $date,
                    'body' => $response->body(),
                ]);
                return ['matches' => []];
            }

            $data = $response->json();

            Log::info("FootballDataOrgProvider: fetchMatches({$date}) en {$ms}ms", [
                'count' => count($data['matches'] ?? []),
            ]);

            return $data;
        } catch (\Throwable $e) {
            Log::error("FootballDataOrgProvider: exception fetchMatches", [
                'error' => $e->getMessage(),
                'date'  => $date,
            ]);
            return ['matches' => []];
        }
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }
}
