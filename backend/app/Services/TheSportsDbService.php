<?php

namespace App\Services;

use App\Models\FootballMatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Source de matchs 100% gratuite, sans quota.
 * Remplace API-Football pour la récupération des fixtures du jour.
 */
class TheSportsDbService
{
    private const BASE = 'https://www.thesportsdb.com/api/v1/json/3';
    private const CACHE_TTL = 86400; // 24h

    // Ligues prioritaires (nom TheSportsDB → tier)
    private const LEAGUES = [
        'English Premier League' => 1,
        'UEFA Champions League'  => 1,
        'Spanish La Liga'        => 1,
        'Italian Serie A'        => 1,
        'German Bundesliga'      => 1,
        'French Ligue 1'         => 1,
        'UEFA Europa League'     => 2,
        'Portuguese Primeira Liga' => 2,
        'Dutch Eredivisie'       => 2,
        'Belgian Pro League'     => 2,
        'UEFA Conference League' => 2,
        'Scottish Premiership'   => 2,
        'Saudi Professional League' => 2,
        'MLS'                    => 3,
        'Brazilian Serie A'      => 3,
        'Turkish Süper Lig'      => 3,
        'Mexican Liga MX'        => 3,
        'AFCON'                  => 4,
        'CAF Champions League'   => 4,
    ];

    public function fetchAndStoreMatches(string $date): array
    {
        $cacheKey = "thesportsdb_matches_{$date}";
        $events   = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($date) {
            return $this->fetchFromApi($date);
        });

        $saved   = 0;
        $updated = 0;

        foreach ($events as $event) {
            try {
                $this->storeMatch($event, $saved, $updated);
            } catch (\Throwable $e) {
                Log::warning('TheSportsDb: erreur stockage match', [
                    'event' => $event['idEvent'] ?? '?',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("TheSportsDb: {$date} — {$saved} créés, {$updated} mis à jour");
        return compact('saved', 'updated', 'events');
    }

    public function getMatchesForDate(string $date): array
    {
        $cacheKey = "thesportsdb_matches_{$date}";
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($date) {
            return $this->fetchFromApi($date);
        });
    }

    private function fetchFromApi(string $date): array
    {
        try {
            $response = Http::timeout(15)
                ->get(self::BASE . '/eventsday.php', [
                    'd' => $date,
                    's' => 'Soccer',
                ]);

            if (!$response->successful()) {
                Log::error('TheSportsDb: API error', ['status' => $response->status()]);
                return [];
            }

            $events = $response->json('events') ?? [];
            Log::info("TheSportsDb: {$date} → " . count($events) . " matchs récupérés");
            return $events;
        } catch (\Throwable $e) {
            Log::error('TheSportsDb: exception', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function storeMatch(array $event, int &$saved, int &$updated): void
    {
        $eventId = $event['idEvent'] ?? null;
        if (!$eventId) return;

        $league   = $event['strLeague']   ?? 'Unknown';
        $date     = Carbon::parse(($event['dateEvent'] ?? now()->format('Y-m-d')) . ' ' . ($event['strTime'] ?? '12:00:00'));
        $tier     = self::LEAGUES[$league] ?? 5;
        $status   = $this->mapStatus($event['strStatus'] ?? '');

        $data = [
            'match_id'         => 'tsdb_' . $eventId,
            'home_team'        => $event['strHomeTeam'] ?? 'Unknown',
            'away_team'        => $event['strAwayTeam'] ?? 'Unknown',
            'competition'      => $league,
            'country'          => $event['strCountry'] ?? 'Unknown',
            'match_date'       => $date,
            'match_time'       => $date->format('H:i'),
            'timezone'         => 'UTC',
            'home_score'       => isset($event['intHomeScore']) && $event['intHomeScore'] !== '' ? (int) $event['intHomeScore'] : null,
            'away_score'       => isset($event['intAwayScore']) && $event['intAwayScore'] !== '' ? (int) $event['intAwayScore'] : null,
            'status'           => $status,
            'venue_name'       => $event['strVenue'] ?? null,
            'last_api_fetch'   => now(),
        ];

        $existing = FootballMatch::where('match_id', 'tsdb_' . $eventId)->first();
        if ($existing) {
            $existing->update($data);
            $updated++;
        } else {
            FootballMatch::create($data);
            $saved++;
        }
    }

    private function mapStatus(string $status): string
    {
        return match (strtolower($status)) {
            'ft', 'aet', 'pen', 'finished' => 'finished',
            '1h', '2h', 'ht', 'live'       => 'live',
            'pst', 'postponed'             => 'postponed',
            'canc', 'cancelled'            => 'cancelled',
            default                        => 'scheduled',
        };
    }
}
