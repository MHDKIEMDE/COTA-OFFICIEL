<?php

declare(strict_types=1);

namespace App\Services\ApiGateway\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GNewsProvider
{
    private const BASE_URL = 'https://gnews.io/api/v4';

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gnews.key', '');
    }

    public function fetchFootballNews(int $max = 10): array
    {
        if (empty($this->apiKey)) {
            Log::warning('GNewsProvider: clé API non configurée (GNEWS_API_KEY)');
            return [];
        }

        try {
            $response = Http::timeout(10)
                ->get(self::BASE_URL . '/search', [
                    'q'       => 'football soccer',
                    'lang'    => 'fr',
                    'max'     => $max,
                    'apikey'  => $this->apiKey,
                ]);

            if (!$response->successful()) {
                Log::error("GNewsProvider: erreur HTTP {$response->status()}");
                return [];
            }

            return $response->json()['articles'] ?? [];
        } catch (\Throwable $e) {
            Log::error("GNewsProvider: exception fetchFootballNews", ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }
}
