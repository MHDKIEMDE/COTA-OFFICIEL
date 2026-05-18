<?php

declare(strict_types=1);

namespace App\Services\ApiGateway\Adapters;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class MatchDataAdapter
{
    public static function fromApiFootball(array $rawData): Collection
    {
        return collect($rawData['response'] ?? [])->map(fn (array $match): array => [
            'external_id'  => (string) ($match['fixture']['id'] ?? ''),
            'source'       => 'api_football',
            'home_team'    => $match['teams']['home']['name'] ?? '',
            'away_team'    => $match['teams']['away']['name'] ?? '',
            'home_logo'    => $match['teams']['home']['logo'] ?? null,
            'away_logo'    => $match['teams']['away']['logo'] ?? null,
            'competition'  => $match['league']['name'] ?? '',
            'league_id'    => $match['league']['id'] ?? null,
            'kickoff_time' => isset($match['fixture']['date'])
                ? Carbon::parse($match['fixture']['date'])
                : null,
            'status'       => self::normalizeStatus($match['fixture']['status']['short'] ?? 'NS'),
            'elapsed'      => $match['fixture']['status']['elapsed'] ?? null,
            'home_score'   => $match['goals']['home'] ?? null,
            'away_score'   => $match['goals']['away'] ?? null,
            'venue'        => $match['fixture']['venue']['name'] ?? null,
            'venue_lat'    => $match['fixture']['venue']['lat'] ?? null,
            'venue_lng'    => $match['fixture']['venue']['lng'] ?? null,
        ]);
    }

    public static function fromFootballDataOrg(array $rawData): Collection
    {
        return collect($rawData['matches'] ?? [])->map(fn (array $match): array => [
            'external_id'  => 'fdo_' . ($match['id'] ?? ''),
            'source'       => 'football_data_org',
            'home_team'    => $match['homeTeam']['name'] ?? '',
            'away_team'    => $match['awayTeam']['name'] ?? '',
            'home_logo'    => $match['homeTeam']['crest'] ?? null,
            'away_logo'    => $match['awayTeam']['crest'] ?? null,
            'competition'  => $match['competition']['name'] ?? '',
            'league_id'    => null,
            'kickoff_time' => isset($match['utcDate'])
                ? Carbon::parse($match['utcDate'])
                : null,
            'status'       => self::normalizeStatus($match['status'] ?? 'SCHEDULED'),
            'elapsed'      => null,
            'home_score'   => $match['score']['fullTime']['home'] ?? null,
            'away_score'   => $match['score']['fullTime']['away'] ?? null,
            'venue'        => null,
            'venue_lat'    => null,
            'venue_lng'    => null,
        ]);
    }

    public static function normalizeStatus(string $status): string
    {
        return match($status) {
            'NS', 'SCHEDULED', 'TIMED'                   => 'scheduled',
            '1H', '2H', 'HT', 'LIVE', 'IN_PLAY', 'PAUSED' => 'live',
            'FT', 'AET', 'PEN', 'FINISHED'               => 'finished',
            'PST', 'CANC', 'POSTPONED', 'CANCELED', 'SUSPENDED' => 'postponed',
            default                                       => 'unknown',
        };
    }
}
