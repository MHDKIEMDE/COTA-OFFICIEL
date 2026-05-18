<?php

declare(strict_types=1);

namespace App\Services\ApiGateway\Providers;

use App\Services\FootballApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ApiFootballProvider
{
    public function __construct(private readonly FootballApiService $api) {}

    public function fetchMatches(string $date): array
    {
        $start = microtime(true);

        $result = $this->api->getUpcomingMatches(1);

        $ms = (int) ((microtime(true) - $start) * 1000);
        Log::info("ApiFootballProvider: fetchMatches({$date}) en {$ms}ms", [
            'count' => count($result['response'] ?? []),
        ]);

        return $result;
    }

    public function fetchLive(): array
    {
        $result = $this->api->getLiveMatches();

        return $result ?? ['response' => []];
    }

    public function isAvailable(): bool
    {
        try {
            $stats     = $this->api->getUsageStats();
            $remaining = $stats['daily']['remaining'] ?? 0;

            return $remaining > 2;
        } catch (\Throwable) {
            return false;
        }
    }
}
