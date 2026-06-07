<?php

namespace App\Jobs;

use App\Models\ApiSourceLog;
use App\Services\FootballApiService;
use App\Services\TheSportsDbService;
use App\Models\FootballMatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Récupère les matchs avec fallback automatique multi-sources.
 *
 * Stratégie cascade (23h → 00h05 → 05h00) :
 *   - Chaque exécution marque les ligues déjà fetchées dans le cache Redis
 *   - L'exécution suivante reprend là où la précédente s'est arrêtée
 *   - Le cache est réinitialisé chaque jour à 23h00
 */
class FetchMatchesJob implements ShouldQueue
{
    use Queueable;

    private const QUOTA_MIN_REMAINING = 5;
    private const CACHE_KEY_PROGRESS  = 'fetch_matches_progress_';
    private const CACHE_TTL           = 28800; // 8h — couvre 23h → 05h30

    protected int $daysAhead;

    public function __construct(int $daysAhead = 2)
    {
        $this->daysAhead = $daysAhead;
    }

    public function handle(FootballApiService $footballApi, TheSportsDbService $sportsDb): void
    {
        $today     = Carbon::today('UTC')->format('Y-m-d');
        $cacheKey  = self::CACHE_KEY_PROGRESS . $today;
        $isEveRun  = Carbon::now('UTC')->hour === 23;

        // À 23h00 : réinitialiser la progression pour la nouvelle journée
        if ($isEveRun) {
            Cache::forget($cacheKey);
            Log::info('FetchMatchesJob: Run 23h — réinitialisation progression cascade');
        }

        $progress = Cache::get($cacheKey, ['fetched_dates' => [], 'source' => null]);

        Log::info('FetchMatchesJob: Démarrage — détection source optimale', [
            'run_hour'       => Carbon::now('UTC')->hour,
            'already_fetched'=> $progress['fetched_dates'],
        ]);

        $source = $this->pickSource($footballApi);
        Log::info("FetchMatchesJob: Source sélectionnée → {$source}");

        match ($source) {
            'api_football' => $this->fetchFromApiFootball($footballApi, $source, $cacheKey, $progress),
            default        => $this->fetchFromTheSportsDb($sportsDb, $source, $cacheKey, $progress),
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

    // ── Fetch API-Football avec progression cascade ─────────────────────────

    private function fetchFromApiFootball(FootballApiService $footballApi, string $source, string $cacheKey, array $progress): void
    {
        $totalSaved   = 0;
        $totalUpdated = 0;
        $fetched      = $progress['fetched_dates'] ?? [];

        for ($i = 0; $i < $this->daysAhead; $i++) {
            $date = Carbon::today()->addDays($i)->format('Y-m-d');

            // Skip si déjà fetchée lors d'un run précédent de la cascade
            if (in_array($date, $fetched)) {
                Log::info("FetchMatchesJob: {$date} déjà fetchée (cascade) — skip");
                continue;
            }

            // Vérifier quota avant chaque date — arrêt si insuffisant
            try {
                $stats     = $footballApi->getUsageStats();
                $remaining = $stats['daily']['remaining'] ?? 0;
                if ($remaining <= self::QUOTA_MIN_REMAINING) {
                    Log::info("FetchMatchesJob: quota insuffisant ({$remaining}) pour {$date} — repris au prochain run");
                    break;
                }
            } catch (\Throwable) {}

            try {
                $response = $footballApi->getUpcomingMatches(1);
                $fixtures = $response['response'] ?? [];

                foreach ($fixtures as $fixture) {
                    $this->storeApiFootballMatch($fixture, $totalSaved, $totalUpdated);
                }

                // Marquer cette date comme fetchée dans la progression cascade
                $fetched[] = $date;
                Cache::put($cacheKey, ['fetched_dates' => $fetched, 'source' => 'api_football'], self::CACHE_TTL);

            } catch (\Throwable $e) {
                Log::error("FetchMatchesJob (API-Football): erreur pour {$date}", ['error' => $e->getMessage()]);
            }
        }

        $stats     = $footballApi->getUsageStats();
        $quotaUsed = $stats['daily']['used'] ?? null;
        $quotaLeft = $stats['daily']['remaining'] ?? null;

        ApiSourceLog::record(
            date: Carbon::today()->format('Y-m-d'),
            source: 'api_football',
            saved: $totalSaved,
            updated: $totalUpdated,
            status: 'success',
            quotaUsed: $quotaUsed,
            quotaRemaining: $quotaLeft,
            notes: "Cascade — quota restant : {$quotaLeft}/100 — dates : " . implode(',', $fetched)
        );

        Log::info("FetchMatchesJob (API-Football): {$totalSaved} créés, {$totalUpdated} mis à jour — quota: {$quotaLeft}", [
            'fetched_dates' => $fetched,
        ]);
    }

    // ── Fetch TheSportsDB avec progression cascade ──────────────────────────

    private function fetchFromTheSportsDb(TheSportsDbService $sportsDb, string $source, string $cacheKey, array $progress): void
    {
        $totalSaved   = 0;
        $totalUpdated = 0;
        $fetched      = $progress['fetched_dates'] ?? [];

        for ($i = 0; $i < $this->daysAhead; $i++) {
            $date = Carbon::today()->addDays($i)->format('Y-m-d');

            if (in_array($date, $fetched)) {
                Log::info("FetchMatchesJob (TheSportsDB): {$date} déjà fetchée — skip");
                continue;
            }

            $result        = $sportsDb->fetchAndStoreMatches($date);
            $totalSaved   += $result['saved'];
            $totalUpdated += $result['updated'];

            $fetched[] = $date;
            Cache::put($cacheKey, ['fetched_dates' => $fetched, 'source' => 'thesportsdb'], self::CACHE_TTL);
        }

        $status = ($source === 'thesportsdb' && !empty(config('football-api.api_key')))
            ? 'fallback'
            : 'success';

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
            'match_id'         => $matchId,
            'home_team'        => $teams['home']['name'] ?? 'Unknown',
            'home_team_logo'   => $teams['home']['logo'] ?? null,
            'away_team'        => $teams['away']['name'] ?? 'Unknown',
            'away_team_logo'   => $teams['away']['logo'] ?? null,
            'competition'      => $league['name'] ?? 'Unknown',
            'country'          => $league['country'] ?? 'Unknown',
            'competition_logo' => $league['logo'] ?? null,
            'match_date'       => Carbon::parse($fixtureData['date'] ?? now()),
            'match_time'       => Carbon::parse($fixtureData['date'] ?? now())->format('H:i'),
            'timezone'         => $fixtureData['timezone'] ?? 'UTC',
            'home_score'       => $goals['home'] ?? null,
            'away_score'       => $goals['away'] ?? null,
            'status'           => $this->mapApiFootballStatus($fixtureData['status']['short'] ?? ''),
            'venue_name'       => $fixtureData['venue']['name'] ?? null,
            'last_api_fetch'   => now(),
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
