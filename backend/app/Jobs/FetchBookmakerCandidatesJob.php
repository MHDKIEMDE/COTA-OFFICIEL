<?php

namespace App\Jobs;

use App\Models\BookmakerCandidate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Scrape automatique des bookmakers depuis les APIs disponibles.
 * Résultat stocké dans bookmaker_candidates avec status='pending'.
 * L'admin valide ou rejette depuis le dashboard.
 *
 * Sources :
 *   - API-Football  : /odds/bookmakers
 *   - The Odds API  : /sports (bookmakers field)
 */
class FetchBookmakerCandidatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function handle(): void
    {
        $imported = 0;

        $imported += $this->fetchFromApiFootball();
        $imported += $this->fetchFromOddsApi();

        Log::info("FetchBookmakerCandidatesJob terminé", ['imported' => $imported]);
    }

    // ── API-Football : /odds/bookmakers ────────────────────────────────────────

    private function fetchFromApiFootball(): int
    {
        $key = config('services.apifootball.key') ?? env('FOOTBALL_API_KEY');
        if (!$key) {
            Log::warning('FetchBookmakerCandidates: FOOTBALL_API_KEY manquant');
            return 0;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(['x-apisports-key' => $key])
                ->get('https://v3.football.api-sports.io/odds/bookmakers');

            if (!$response->successful()) {
                Log::warning('FetchBookmakerCandidates: API-Football échec', ['status' => $response->status()]);
                return 0;
            }

            $items = $response->json()['response'] ?? [];
            $count = 0;

            foreach ($items as $item) {
                $apiId = (string) ($item['id'] ?? '');
                $name  = trim($item['name'] ?? '');
                if (!$apiId || !$name) continue;

                $created = BookmakerCandidate::firstOrCreate(
                    ['api_source' => 'apifootball', 'api_id' => $apiId],
                    [
                        'name'     => $name,
                        'slug'     => Str::slug($name),
                        'raw_data' => $item,
                        'status'   => 'pending',
                    ]
                );

                if ($created->wasRecentlyCreated) $count++;
            }

            return $count;
        } catch (\Exception $e) {
            Log::error('FetchBookmakerCandidates: API-Football erreur', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    // ── The Odds API : /v4/sports ──────────────────────────────────────────────

    private function fetchFromOddsApi(): int
    {
        $key = config('services.oddsapi.key') ?? env('ODDS_API_KEY');
        if (!$key) {
            return 0; // clé optionnelle
        }

        try {
            $response = Http::timeout(15)
                ->get('https://api.the-odds-api.com/v4/sports', [
                    'apiKey' => $key,
                ]);

            if (!$response->successful()) return 0;

            // The Odds API liste les sports, pas les bookmakers directement.
            // On récupère les bookmakers disponibles via un appel dédié.
            $bmResponse = Http::timeout(15)
                ->get('https://api.the-odds-api.com/v4/sports/soccer_epl/odds', [
                    'apiKey'  => $key,
                    'regions' => 'af',
                    'markets' => 'h2h',
                    'oddsFormat' => 'decimal',
                ]);

            if (!$bmResponse->successful()) return 0;

            $events = $bmResponse->json() ?? [];
            $seen   = [];
            $count  = 0;

            foreach ($events as $event) {
                foreach ($event['bookmakers'] ?? [] as $bm) {
                    $key_ = $bm['key'] ?? '';
                    $name = $bm['title'] ?? '';
                    if (!$key_ || !$name || isset($seen[$key_])) continue;
                    $seen[$key_] = true;

                    $created = BookmakerCandidate::firstOrCreate(
                        ['api_source' => 'oddsapi', 'api_id' => $key_],
                        [
                            'name'     => $name,
                            'slug'     => Str::slug($name),
                            'raw_data' => $bm,
                            'status'   => 'pending',
                        ]
                    );

                    if ($created->wasRecentlyCreated) $count++;
                }
            }

            return $count;
        } catch (\Exception $e) {
            Log::error('FetchBookmakerCandidates: OddsAPI erreur', ['error' => $e->getMessage()]);
            return 0;
        }
    }
}
