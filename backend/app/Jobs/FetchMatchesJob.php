<?php

namespace App\Jobs;

use App\Models\ApiSourceLog;
use App\Services\FootballApiService;
use App\Services\TheSportsDbService;
use App\Models\FootballMatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Récupère les matchs avec fallback automatique multi-sources :
 *   1. API-Football  (si quota > QUOTA_MIN_REMAINING)
 *   2. TheSportsDB   (gratuit, sans quota)
 *
 * La source utilisée est transparente pour les utilisateurs.
 * L'admin voit le détail dans le widget "Sources API" du dashboard.
 */
class FetchMatchesJob implements ShouldQueue
{
    use Queueable;

    // Quota minimum avant de basculer sur le fallback
    private const QUOTA_MIN_REMAINING = 5;

    protected int $daysAhead;

    public function __construct(int $daysAhead = 2)
    {
        $this->daysAhead = $daysAhead;
    }

    public function handle(FootballApiService $footballApi, TheSportsDbService $sportsDb): void
    {
        Log::info('FetchMatchesJob: Démarrage — détection source optimale');

        $source = $this->pickSource($footballApi);

        Log::info("FetchMatchesJob: Source sélectionnée → {$source}");

        match ($source) {
            'api_football' => $this->fetchFromApiFootball($footballApi, $source),
            default        => $this->fetchFromTheSportsDb($sportsDb, $source),
        };
    }

    // ── Sélection de la source ──────────────────────────────────────────────

    private function pickSource(FootballApiService $footballApi): string
    {
        $apiKey = config('football-api.api_key');

        // Pas de clé configurée → TheSportsDB directement
        if (empty($apiKey)) {
            return 'thesportsdb';
        }

        try {
            $stats     = $footballApi->getUsageStats();
            $remaining = $stats['daily']['remaining'] ?? 0;

            if ($remaining > self::QUOTA_MIN_REMAINING) {
                return 'api_football';
            }

            Log::info("FetchMatchesJob: Quota API-Football bas ({$remaining} restants) → bascule TheSportsDB");
            return 'thesportsdb';
        } catch (\Throwable $e) {
            Log::warning('FetchMatchesJob: Impossible de lire le quota API-Football', ['error' => $e->getMessage()]);
            return 'thesportsdb';
        }
    }

    // ── Fetch API-Football ──────────────────────────────────────────────────

    private function fetchFromApiFootball(FootballApiService $footballApi, string $source): void
    {
        $totalSaved   = 0;
        $totalUpdated = 0;

        for ($i = 0; $i < $this->daysAhead; $i++) {
            $date = Carbon::today()->addDays($i)->format('Y-m-d');

            try {
                $response = $footballApi->getUpcomingMatches(1);
                $fixtures = $response['response'] ?? [];

                foreach ($fixtures as $fixture) {
                    $this->storeApiFootballMatch($fixture, $totalSaved, $totalUpdated);
                }
            } catch (\Throwable $e) {
                Log::error("FetchMatchesJob (API-Football): erreur pour {$date}", ['error' => $e->getMessage()]);
            }
        }

        $stats       = $footballApi->getUsageStats();
        $quotaUsed   = $stats['daily']['used'] ?? null;
        $quotaLeft   = $stats['daily']['remaining'] ?? null;

        ApiSourceLog::record(
            date: Carbon::today()->format('Y-m-d'),
            source: 'api_football',
            saved: $totalSaved,
            updated: $totalUpdated,
            status: 'success',
            quotaUsed: $quotaUsed,
            quotaRemaining: $quotaLeft,
            notes: "Quota restant : {$quotaLeft}/100"
        );

        Log::info("FetchMatchesJob (API-Football): {$totalSaved} créés, {$totalUpdated} mis à jour — quota restant: {$quotaLeft}");
    }

    // ── Fetch TheSportsDB ───────────────────────────────────────────────────

    private function fetchFromTheSportsDb(TheSportsDbService $sportsDb, string $source): void
    {
        $totalSaved   = 0;
        $totalUpdated = 0;

        for ($i = 0; $i < $this->daysAhead; $i++) {
            $date   = Carbon::today()->addDays($i)->format('Y-m-d');
            $result = $sportsDb->fetchAndStoreMatches($date);
            $totalSaved   += $result['saved'];
            $totalUpdated += $result['updated'];
        }

        $status = ($source === 'thesportsdb' && !empty(config('football-api.api_key')))
            ? 'fallback'   // clé présente mais quota épuisé → fallback
            : 'success';   // pas de clé → TheSportsDB est la source principale

        ApiSourceLog::record(
            date: Carbon::today()->format('Y-m-d'),
            source: 'thesportsdb',
            saved: $totalSaved,
            updated: $totalUpdated,
            status: $status,
            quotaUsed: null,
            quotaRemaining: null,
            notes: $status === 'fallback' ? 'Quota API-Football épuisé — bascule automatique TheSportsDB' : 'Source principale (gratuite)'
        );

        Log::info("FetchMatchesJob (TheSportsDB): {$totalSaved} créés, {$totalUpdated} mis à jour [status={$status}]");
    }

    // ── Stockage match API-Football ─────────────────────────────────────────

    private function storeApiFootballMatch(array $fixture, int &$saved, int &$updated): void
    {
        $fixtureData = $fixture['fixture'] ?? [];
        $teams       = $fixture['teams']   ?? [];
        $goals       = $fixture['goals']   ?? [];
        $league      = $fixture['league']  ?? [];

        $matchId = 'apf_' . ($fixtureData['id'] ?? null);
        if ($matchId === 'apf_') return;

        $data = [
            'match_id'       => $matchId,
            'home_team'      => $teams['home']['name'] ?? 'Unknown',
            'away_team'      => $teams['away']['name'] ?? 'Unknown',
            'competition'    => $league['name'] ?? 'Unknown',
            'country'        => $league['country'] ?? 'Unknown',
            'match_date'     => Carbon::parse($fixtureData['date'] ?? now()),
            'match_time'     => Carbon::parse($fixtureData['date'] ?? now())->format('H:i'),
            'timezone'       => $fixtureData['timezone'] ?? 'UTC',
            'home_score'     => $goals['home'] ?? null,
            'away_score'     => $goals['away'] ?? null,
            'status'         => $this->mapApiFootballStatus($fixtureData['status']['short'] ?? ''),
            'venue_name'     => $fixtureData['venue']['name'] ?? null,
            'last_api_fetch' => now(),
        ];

        $existing = FootballMatch::where('match_id', $matchId)->first();
        if ($existing) {
            $existing->update($data);
            $updated++;
        } else {
            FootballMatch::create($data);
            $saved++;
        }
    }

    private function mapApiFootballStatus(string $short): string
    {
        return match (strtoupper($short)) {
            'FT', 'AET', 'PEN'       => 'finished',
            '1H', '2H', 'HT', 'LIVE' => 'live',
            'PST'                     => 'postponed',
            'CANC', 'ABD'             => 'cancelled',
            default                   => 'scheduled',
        };
    }
}
