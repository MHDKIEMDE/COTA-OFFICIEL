<?php

declare(strict_types=1);

namespace App\Services\ApiGateway\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TheSportsDbProvider
{
    private const BASE_URL = 'https://www.thesportsdb.com/api/v1/json';

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) config('services.thesportsdb.key', '3');
    }

    public function fetchTeamLogo(string $teamName): ?string
    {
        try {
            $response = Http::timeout(10)
                ->get(self::BASE_URL . "/{$this->apiKey}/searchteams.php", [
                    't' => $teamName,
                ]);

            if (!$response->successful()) {
                return null;
            }

            return $response->json()['teams'][0]['strTeamBadge'] ?? null;
        } catch (\Throwable $e) {
            Log::error("TheSportsDbProvider: exception fetchTeamLogo", ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function fetchEventsByDate(string $date): array
    {
        try {
            $response = Http::timeout(10)
                ->get(self::BASE_URL . "/{$this->apiKey}/eventsday.php", [
                    'd'  => $date,
                    's'  => 'Soccer',
                ]);

            if (!$response->successful()) {
                return [];
            }

            return $response->json()['events'] ?? [];
        } catch (\Throwable $e) {
            Log::error("TheSportsDbProvider: exception fetchEventsByDate", ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function isAvailable(): bool
    {
        return true; // API publique gratuite
    }
}
