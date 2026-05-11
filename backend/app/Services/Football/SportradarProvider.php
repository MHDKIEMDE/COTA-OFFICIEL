<?php

declare(strict_types=1);

namespace App\Services\Football;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Provider secondaire — Sportradar Soccer API v4
 * Utilisé quand API-Football a épuisé son quota.
 * Clé configurée via SPORTRADAR_API_KEY dans .env
 */
class SportradarProvider implements FootballProviderInterface
{
    private const BASE_URL  = 'https://api.sportradar.com/soccer/trial/v4/fr';
    private const CACHE_TTL = 600; // 10 minutes

    // Mapping compétitions Sportradar → nom lisible
    private const COMPETITION_MAP = [
        'sr:competition:17'  => 'Premier League',
        'sr:competition:8'   => 'Champions League',
        'sr:competition:23'  => 'La Liga',
        'sr:competition:35'  => 'Serie A',
        'sr:competition:44'  => 'Bundesliga',
        'sr:competition:34'  => 'Ligue 1',
        'sr:competition:18'  => 'Europa League',
    ];

    public function __construct(
        private readonly string $apiKey = '',
    ) {}

    public function name(): string
    {
        return 'sportradar';
    }

    public function isAvailable(): bool
    {
        $key = config('services.sportradar.api_key', $this->apiKey);
        return !empty($key);
    }

    public function getFixtures(?string $date = null): array
    {
        $date ??= Carbon::today()->format('Y-m-d');
        $cacheKey = "sportradar_fixtures_{$date}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($date) {
            try {
                $key = config('services.sportradar.api_key', $this->apiKey);
                $response = Http::timeout(10)
                    ->get(self::BASE_URL . "/schedules/{$date}/schedule.json", [
                        'api_key' => $key,
                    ]);

                if (!$response->successful()) {
                    Log::warning('Sportradar: réponse non-200', ['status' => $response->status()]);
                    return [];
                }

                $data = $response->json();
                return $this->normalize($data['sport_events'] ?? []);
            } catch (\Throwable $e) {
                Log::error('SportradarProvider::getFixtures failed', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    public function getLiveMatches(): array
    {
        $cacheKey = 'sportradar_live_' . now()->format('YmdHi');

        return Cache::remember($cacheKey, 60, function () {
            try {
                $key = config('services.sportradar.api_key', $this->apiKey);
                $response = Http::timeout(10)
                    ->get(self::BASE_URL . '/schedules/live/schedule.json', [
                        'api_key' => $key,
                    ]);

                if (!$response->successful()) {
                    return [];
                }

                $data = $response->json();
                return $this->normalize($data['sport_events'] ?? []);
            } catch (\Throwable $e) {
                Log::error('SportradarProvider::getLiveMatches failed', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /** Normalise un sport_event Sportradar au format COTA */
    private function normalize(array $events): array
    {
        $fixtures = [];
        foreach ($events as $e) {
            $competitors = $e['competitors'] ?? [];
            $home = collect($competitors)->firstWhere('qualifier', 'home');
            $away = collect($competitors)->firstWhere('qualifier', 'away');

            if (!$home || !$away) {
                continue;
            }

            $competitionId = $e['tournament']['id'] ?? '';
            $fixtures[] = [
                '_source'    => $this->name(),
                '_raw'       => $e,
                'fixture_id' => $e['id'] ?? null,
                'date'       => $e['scheduled'] ?? null,
                'status'     => $this->mapStatus($e['status'] ?? 'not_started'),
                'elapsed'    => null,
                'home_team'  => $home['name'] ?? '',
                'away_team'  => $away['name'] ?? '',
                'home_score' => null,
                'away_score' => null,
                'league'     => self::COMPETITION_MAP[$competitionId] ?? ($e['tournament']['name'] ?? 'Unknown'),
                'league_id'  => $competitionId,
                'country'    => $e['tournament']['category']['name'] ?? '',
                'venue'      => $e['venue']['name'] ?? null,
            ];
        }
        return $fixtures;
    }

    private function mapStatus(string $srStatus): string
    {
        return match ($srStatus) {
            'not_started'  => 'NS',
            'live', 'inprogress' => '1H',
            'halftime'     => 'HT',
            'pause'        => 'HT',
            'closed', 'ended' => 'FT',
            'postponed'    => 'PST',
            'cancelled'    => 'CANC',
            default        => 'NS',
        };
    }
}
