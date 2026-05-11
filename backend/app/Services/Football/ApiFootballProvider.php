<?php

declare(strict_types=1);

namespace App\Services\Football;

use App\Services\FootballApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Provider primaire — API-Football (rapidapi.com)
 * Wrap le FootballApiService existant avec détection de quota.
 */
class ApiFootballProvider implements FootballProviderInterface
{
    public function __construct(private readonly FootballApiService $api) {}

    public function name(): string
    {
        return 'api-football';
    }

    public function isAvailable(): bool
    {
        try {
            $stats = $this->api->getUsageStats();
            $remaining = $stats['daily']['remaining'] ?? 0;
            if ($remaining <= 2) {
                Log::warning('ApiFootball: quota quasi-épuisé', ['remaining' => $remaining]);
                return false;
            }
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function getFixtures(?string $date = null): array
    {
        try {
            $result = $this->api->getUpcomingMatches(1);
            return $this->normalize($result['response'] ?? []);
        } catch (\Throwable $e) {
            Log::error('ApiFootballProvider::getFixtures failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getLiveMatches(): array
    {
        try {
            $result = $this->api->getLiveMatches();
            return $this->normalize($result['response'] ?? []);
        } catch (\Throwable $e) {
            Log::error('ApiFootballProvider::getLiveMatches failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /** Convertit la réponse API-Football au format normalisé COTA */
    private function normalize(array $fixtures): array
    {
        return array_map(fn (array $f) => [
            '_source'    => $this->name(),
            '_raw'       => $f,
            'fixture_id' => $f['fixture']['id'] ?? null,
            'date'       => $f['fixture']['date'] ?? null,
            'status'     => $f['fixture']['status']['short'] ?? 'NS',
            'elapsed'    => $f['fixture']['status']['elapsed'] ?? null,
            'home_team'  => $f['teams']['home']['name'] ?? '',
            'away_team'  => $f['teams']['away']['name'] ?? '',
            'home_score' => $f['goals']['home'] ?? null,
            'away_score' => $f['goals']['away'] ?? null,
            'league'     => $f['league']['name'] ?? '',
            'league_id'  => $f['league']['id'] ?? null,
            'country'    => $f['league']['country'] ?? '',
            'venue'      => $f['fixture']['venue']['name'] ?? null,
        ], $fixtures);
    }
}
