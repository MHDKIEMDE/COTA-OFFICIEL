<?php

declare(strict_types=1);

namespace App\Services\Football;

use App\Models\FootballMatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Provider tertiaire — base de données locale (données stalles).
 * Toujours disponible. Sert les matchs stockés en DB lors des appels précédents.
 * Utile quand TOUS les providers externes sont indisponibles.
 */
class CacheProvider implements FootballProviderInterface
{
    public function name(): string
    {
        return 'local-cache';
    }

    public function isAvailable(): bool
    {
        return true; // Toujours disponible
    }

    public function getFixtures(?string $date = null): array
    {
        $date ??= Carbon::today()->format('Y-m-d');

        Log::warning('CacheProvider: utilisation des données locales (fallback niveau 3)', [
            'date' => $date,
        ]);

        $matches = FootballMatch::whereDate('match_date', $date)
            ->orderBy('match_date')
            ->get();

        if ($matches->isEmpty()) {
            // Essai sur ±1 jour si rien aujourd'hui
            $matches = FootballMatch::whereBetween('match_date', [
                Carbon::parse($date)->subDay(),
                Carbon::parse($date)->addDay(),
            ])->orderBy('match_date')->limit(50)->get();
        }

        return $matches->map(fn (FootballMatch $m) => [
            '_source'    => $this->name(),
            '_stale'     => true,
            '_raw'       => $m->toArray(),
            'fixture_id' => $m->match_id,
            'date'       => $m->match_date?->toIso8601String(),
            'status'     => $m->status ?? 'NS',
            'elapsed'    => $m->elapsed_time,
            'home_team'  => $m->home_team,
            'away_team'  => $m->away_team,
            'home_score' => $m->home_score,
            'away_score' => $m->away_score,
            'league'     => $m->competition,
            'league_id'  => $m->competition_id,
            'country'    => $m->country,
            'venue'      => $m->venue_name,
        ])->all();
    }

    public function getLiveMatches(): array
    {
        Log::warning('CacheProvider: live matches depuis DB (fallback niveau 3)');

        $matches = FootballMatch::whereIn('status', ['1H', '2H', 'HT', 'ET', 'P'])
            ->orderBy('match_date')
            ->get();

        return $matches->map(fn (FootballMatch $m) => [
            '_source'    => $this->name(),
            '_stale'     => true,
            '_raw'       => $m->toArray(),
            'fixture_id' => $m->match_id,
            'date'       => $m->match_date?->toIso8601String(),
            'status'     => $m->status,
            'elapsed'    => $m->elapsed_time,
            'home_team'  => $m->home_team,
            'away_team'  => $m->away_team,
            'home_score' => $m->home_score,
            'away_score' => $m->away_score,
            'league'     => $m->competition,
            'league_id'  => $m->competition_id,
            'country'    => $m->country,
            'venue'      => $m->venue_name,
        ])->all();
    }
}
