<?php

namespace App\Console\Commands;

use App\Services\FootballApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegeneratePredictions extends Command
{
    protected $signature = 'predictions:regenerate
                            {--date= : Date cible (YYYY-MM-DD, défaut: aujourd\'hui)}
                            {--limit=0 : Limite de prédictions à traiter (0 = toutes)}
                            {--dry-run : Afficher sans sauvegarder}
                            {--no-api : Ne pas appeler API-Football pour les cotes (utiliser cotes calculées)}';

    protected $description = 'Régénère bet_type/prediction/odds — cotes récupérées depuis API-Football (fallback calculé si quota dépassé)';

    private FootballApiService $footballApi;
    private int $apiCallsUsed  = 0;
    private int $apiCallsLimit = 80; // Garder 20 requêtes de marge sur 100/jour

    public function handle(): int
    {
        $date   = $this->option('date') ?? Carbon::today()->toDateString();
        $limit  = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $noApi  = $this->option('no-api');

        $this->footballApi = app(FootballApiService::class);

        $this->info("Régénération des prédictions pour le {$date}" . ($dryRun ? ' [DRY-RUN]' : ''));
        if (!$noApi) {
            $this->info("Mode : cotes réelles API-Football (fallback calculé si quota atteint)");
        }

        $query = DB::table('predictions')
            ->whereDate('match_date', $date)
            ->whereNotNull('home_team_id')
            ->whereNotNull('away_team_id');

        if ($limit > 0) $query->limit($limit);

        $rows  = $query->get();
        $total = $rows->count();

        if ($total === 0) {
            $this->warn("Aucune prédiction trouvée pour le {$date}.");
            return self::FAILURE;
        }

        $this->info("Traitement de {$total} prédictions...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated      = 0;
        $apiHits      = 0;
        $apiFallbacks = 0;
        $typeCounts   = [];

        // Ligues populaires (Tier 1-3) — on priorise les appels API pour celles-ci
        $popularLeagueIds = array_keys(config('football-api.popular_leagues', []));

        foreach ($rows as $row) {
            $result = $this->computeBetType($row);

            // Appeler l'API uniquement pour les ligues populaires et si quota disponible
            $isPopular = in_array((int) $row->competition_id, $popularLeagueIds);
            if (!$noApi && $row->match_id && $this->apiCallsUsed < $this->apiCallsLimit && $isPopular) {
                $apiOdds = $this->fetchApiOdds((int) $row->match_id, $result['type'], $result['outcome']);
                if ($apiOdds !== null) {
                    $result['odds'] = $apiOdds;
                    $apiHits++;
                    $this->apiCallsUsed++;
                } else {
                    $apiFallbacks++;
                }
            }

            $typeCounts[$result['type']] = ($typeCounts[$result['type']] ?? 0) + 1;

            if (!$dryRun) {
                DB::table('predictions')->where('id', $row->id)->update([
                    'bet_type'   => $result['type'],
                    'prediction' => $result['outcome'],
                    'odds'       => $result['odds'],
                    'updated_at' => Carbon::now(),
                ]);
            }

            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Terminé : {$updated}/{$total} mises à jour");
        if (!$noApi) {
            $this->info("Cotes API réelles : {$apiHits} | Cotes calculées (fallback) : {$apiFallbacks}");
        }
        $this->newLine();

        arsort($typeCounts);
        $tableRows = [];
        foreach ($typeCounts as $type => $count) {
            $tableRows[] = [$type, $count, round($count / $updated * 100, 1) . '%'];
        }
        $this->table(['Type', 'Nombre', '%'], $tableRows);

        if (!$dryRun) {
            \Illuminate\Support\Facades\Cache::forget('coupon_daily_' . $date);
            $this->info("Cache coupon invalidé.");
        }

        return self::SUCCESS;
    }

    /**
     * Appelle API-Football /odds pour récupérer la vraie cote bookmaker.
     * Retourne null si l'API ne répond pas ou si la cote n'est pas disponible.
     */
    private function fetchApiOdds(int $fixtureId, string $betType, string $outcome): ?float
    {
        try {
            return $this->footballApi->getFixtureOdds($fixtureId, $betType, $outcome);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Calcule le type de pari à partir des scores existants + signal déterministe basé sur les IDs.
     *
     * Les scores neutres (= 0.5 × poids) sont enrichis par un signal hash basé sur les IDs
     * d'équipe pour simuler une variabilité réaliste sans appel API.
     */
    private function computeBetType(object $row): array
    {
        $scores = [
            'form'      => (float) ($row->score_form ?? 0),
            'h2h'       => (float) ($row->score_h2h ?? 0),
            'home_away' => (float) ($row->score_home_away ?? 0),
            'league'    => (float) ($row->score_league ?? 0),
            'goals'     => (float) ($row->score_goals ?? 0),
        ];

        $weights = ['form' => 25, 'h2h' => 20, 'home_away' => 15, 'league' => 12, 'goals' => 10];

        // Signal déterministe : hash basé sur home_id XOR away_id
        // Donne un biais reproductible entre -0.15 et +0.15 par critère
        $seed = abs(($row->home_team_id * 31) ^ ($row->away_team_id * 17));

        foreach (['form', 'h2h', 'home_away', 'league', 'goals'] as $i => $key) {
            $neutral = $weights[$key] * 0.5;
            // Si le score est strictement neutre, appliquer le signal
            if (abs($scores[$key] - $neutral) < 0.1) {
                $shift = (($seed >> ($i * 4)) % 31 - 15) / 100; // entre -0.15 et +0.15
                $scores[$key] = $neutral + ($shift * $weights[$key]);
                $scores[$key] = max(0, min($scores[$key], (float) $weights[$key]));
            }
        }

        $totalScore = (float) ($row->total_score ?? 50.5);

        // Ratios normalisés
        $formRatio     = $scores['form']      / $weights['form'];
        $h2hRatio      = $scores['h2h']       / $weights['h2h'];
        $homeAwayRatio = $scores['home_away'] / $weights['home_away'];
        $goalsRatio    = $scores['goals']     / $weights['goals'];
        $leagueRatio   = $scores['league']    / $weights['league'];

        $homeAdv = ($formRatio * 0.4) + ($h2hRatio * 0.35) + ($homeAwayRatio * 0.25);
        $awayAdv = 1 - $homeAdv;

        // Signal supplémentaire basé sur competition_id pour les marchés spéciaux
        $compSeed = $row->competition_id ?? 0;

        // ── 1. DOMICILE DOMINANT ──────────────────────────────────────────────
        if ($homeAdv >= 0.70) {
            return ['type' => '1X2', 'outcome' => '1', 'odds' => round(1.35 + (($seed % 26) / 100), 2)];
        }
        if ($homeAdv >= 0.62) {
            return ['type' => '1X2', 'outcome' => '1', 'odds' => round(1.55 + (($seed % 36) / 100), 2)];
        }

        // ── 2. EXTÉRIEUR DOMINANT ─────────────────────────────────────────────
        if ($awayAdv >= 0.70) {
            return ['type' => '1X2', 'outcome' => '2', 'odds' => round(1.75 + (($seed % 46) / 100), 2)];
        }
        if ($awayAdv >= 0.62) {
            return ['type' => '1X2', 'outcome' => '2', 'odds' => round(2.00 + (($seed % 61) / 100), 2)];
        }

        // ── 3. CORNERS (signal compétition) ───────────────────────────────────
        // Les ligues à hauts corners : Premier League, Bundesliga, MLS…
        $highCornerLeagues = [39, 78, 253, 88, 135]; // PL, Bundesliga, MLS, Eredivisie, Serie A
        if (in_array($compSeed, $highCornerLeagues) && $homeAdv >= 0.50) {
            return ['type' => 'Corners', 'outcome' => 'Over 9.5', 'odds' => round(1.80 + (($seed % 31) / 100), 2)];
        }

        // ── 4. CARTONS (signal compétition + équipe agressive) ────────────────
        // Ligues physiques : Serie A, Ligue 1, compétitions africaines
        $physicalLeagues = [135, 61, 29, 17, 12]; // Serie A, Ligue 1, CAF, AFCON Q, AFCON
        if (in_array($compSeed, $physicalLeagues) && ($seed % 3) === 0) {
            return ['type' => 'Cartons', 'outcome' => 'Over 3.5', 'odds' => round(1.75 + (($seed % 36) / 100), 2)];
        }

        // ── 5. BUTS — BTTS ────────────────────────────────────────────────────
        if ($goalsRatio >= 0.62) {
            return ['type' => 'BTTS', 'outcome' => 'Oui', 'odds' => round(1.72 + (($seed % 31) / 100), 2)];
        }

        // ── 6. OVER/UNDER 2.5 ─────────────────────────────────────────────────
        if ($goalsRatio >= 0.55 || ($seed % 4) === 1) {
            return ['type' => 'Over/Under', 'outcome' => 'Over 2.5', 'odds' => round(1.68 + (($seed % 36) / 100), 2)];
        }
        if ($goalsRatio <= 0.40) {
            return ['type' => 'Over/Under', 'outcome' => 'Under 2.5', 'odds' => round(1.75 + (($seed % 31) / 100), 2)];
        }

        // ── 7. DOUBLE CHANCE ──────────────────────────────────────────────────
        if ($homeAdv >= 0.51) {
            return ['type' => 'Double Chance', 'outcome' => '1X', 'odds' => round(1.22 + (($seed % 26) / 100), 2)];
        }
        if ($awayAdv >= 0.51) {
            return ['type' => 'Double Chance', 'outcome' => 'X2', 'odds' => round(1.32 + (($seed % 31) / 100), 2)];
        }

        // ── 8. BTTS DÉFAUT ────────────────────────────────────────────────────
        return ['type' => 'BTTS', 'outcome' => 'Oui', 'odds' => round(1.78 + (($seed % 31) / 100), 2)];
    }
}
