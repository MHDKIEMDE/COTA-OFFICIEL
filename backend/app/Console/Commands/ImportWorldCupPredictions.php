<?php

namespace App\Console\Commands;

use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Services\FootballApiService;
use App\Services\OddsApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Importe les pronostics Coupe du monde depuis API-Football (/predictions).
 *
 * RapidAPI (football-prediction-api) ne couvre PAS la Coupe du monde / les
 * sélections nationales. API-Football, lui, fournit un pronostic officiel par
 * fixture (winner + comment + pourcentages + advice). C'est la source propre
 * pour la CDM — à la place de l'algo maison, qui produisait des pronos inventés.
 *
 * Règle métier : la Coupe du monde doit toujours être affichée (J/J+1).
 */
class ImportWorldCupPredictions extends Command
{
    /** ID API-Football de la FIFA World Cup. */
    private const WORLD_CUP_LEAGUE_ID = 1;

    protected $signature = 'predictions:import-worldcup
        {--days=2 : Fenêtre en jours (J + J+1 par défaut)}
        {--include-expired : Importer aussi les matchs déjà commencés}';

    protected $description = 'Importe les pronostics Coupe du monde depuis API-Football (source officielle /predictions)';

    public function handle(FootballApiService $api, OddsApiService $oddsApi): int
    {
        // Charge les vraies cotes 1xBet (The Odds API) — inclut soccer_fifa_world_cup.
        $oddsApi->loadDailyOdds();

        $response = $api->getUpcomingMatches((int) $this->option('days'));
        $fixtures = $response['response'] ?? [];

        $wc = array_filter(
            $fixtures,
            fn (array $f) => (int) ($f['league']['id'] ?? 0) === self::WORLD_CUP_LEAGUE_ID
        );

        if (empty($wc)) {
            $this->warn('Aucune fixture Coupe du monde sur la fenêtre demandée.');
            return self::SUCCESS;
        }

        $imported = 0;
        $skipped  = 0;

        foreach ($wc as $fixture) {
            $kickoff = Carbon::parse($fixture['fixture']['date'] ?? now());

            if ($kickoff->isPast() && !$this->option('include-expired')) {
                $skipped++;
                continue;
            }

            $preds = $api->getApiPredictions((int) $fixture['fixture']['id']);
            $block = $preds['response'][0]['predictions'] ?? null;

            if (!$block) {
                $this->line('  Pas de prono API-Football pour fixture ' . ($fixture['fixture']['id'] ?? '?'));
                $skipped++;
                continue;
            }

            // Ne JAMAIS publier un prono sans données réelles : API-Football renvoie
            // parfois "No predictions available" + 33/33/33 (valeurs neutres). Publier
            // dessus = prono inventé → on saute (même logique que la suppression algo maison).
            if ($this->hasNoRealData($block)) {
                $home = $fixture['teams']['home']['name'] ?? '?';
                $away = $fixture['teams']['away']['name'] ?? '?';
                $this->line("  Données API-Football insuffisantes pour $home vs $away → ignoré");
                $this->unpublishIfExists((string) $fixture['fixture']['id']);
                $skipped++;
                continue;
            }

            $this->importOne($fixture, $block, $kickoff, $oddsApi);
            $imported++;
        }

        $this->invalidateCaches();

        $this->info("Importés : $imported pronostic(s) Coupe du monde ($skipped ignorés).");
        $this->line('Caches pronostics + coupon invalidés.');
        return self::SUCCESS;
    }

    /**
     * Vrai si API-Football n'a pas de prédiction exploitable :
     * advice "No predictions available", pas de winner, ou pourcentages neutres
     * (33/33/33) qui ne distinguent aucune issue.
     */
    private function hasNoRealData(array $block): bool
    {
        $advice = strtolower($block['advice'] ?? '');
        if ($advice === '' || str_contains($advice, 'no predictions')) {
            return true;
        }

        if (empty($block['winner']['name'])) {
            return true;
        }

        $h = (float) str_replace('%', '', $block['percent']['home'] ?? '0');
        $d = (float) str_replace('%', '', $block['percent']['draw'] ?? '0');
        $a = (float) str_replace('%', '', $block['percent']['away'] ?? '0');

        // Aucune issue ne se détache (écart max < 5 pts) → données neutres.
        return (max($h, $d, $a) - min($h, $d, $a)) < 5.0;
    }

    /**
     * Dépublie un prono CDM existant devenu non fiable (données API-Football
     * disparues). Garde la ligne pour l'historique mais la retire de l'affichage.
     */
    private function unpublishIfExists(string $matchId): void
    {
        Prediction::where('match_id', $matchId)
            ->where('competition', 'World Cup')
            ->update(['is_published' => false]);
    }

    private function importOne(array $fixture, array $block, Carbon $kickoff, OddsApiService $oddsApi): void
    {
        $matchId = (string) $fixture['fixture']['id'];
        $home    = $fixture['teams']['home']['name'] ?? 'Unknown';
        $away    = $fixture['teams']['away']['name'] ?? 'Unknown';
        $league  = $fixture['league'] ?? [];

        [$betType, $outcome, $odds, $stars] = $this->mapPrediction($block, $home, $away);

        // Priorité aux vraies cotes 1xBet (The Odds API). On ne remplace que si
        // une cote réelle existe pour l'outcome ; sinon on garde l'estimation.
        [$odds, $oddsSource] = $this->resolveRealOdds($oddsApi, $home, $away, $betType, $outcome, $odds);

        FootballMatch::updateOrCreate(
            ['match_id' => $matchId],
            [
                'home_team'      => $home,
                'away_team'      => $away,
                'home_team_id'   => $fixture['teams']['home']['id'] ?? 0,
                'away_team_id'   => $fixture['teams']['away']['id'] ?? 0,
                'competition_id' => self::WORLD_CUP_LEAGUE_ID,
                'competition'    => $league['name'] ?? 'World Cup',
                'country'        => $league['country'] ?? 'World',
                'match_date'     => $kickoff,
                'match_time'     => $kickoff->format('H:i'),
                'status'         => $kickoff->isPast() ? 'finished' : 'scheduled',
            ]
        );

        Prediction::updateOrCreate(
            ['match_id' => $matchId],
            [
                'home_team'        => $home,
                'away_team'        => $away,
                'home_team_logo'   => $fixture['teams']['home']['logo'] ?? null,
                'away_team_logo'   => $fixture['teams']['away']['logo'] ?? null,
                'home_team_id'     => $fixture['teams']['home']['id'] ?? 0,
                'away_team_id'     => $fixture['teams']['away']['id'] ?? 0,
                'competition'      => $league['name'] ?? 'World Cup',
                'competition_id'   => self::WORLD_CUP_LEAGUE_ID,
                'competition_logo' => $league['logo'] ?? null,
                'league_tier'      => 1, // Coupe du monde = grande envergure (coupon dédié)
                'country'          => $league['country'] ?? 'World',
                'match_date'       => $kickoff,
                'match_time'       => $kickoff->format('H:i'),
                'bet_type'         => $betType,
                'prediction'       => $outcome,
                'odds'             => $odds,
                'confidence_stars' => $stars,
                'total_score'      => $this->scoreFromOdds($odds, $stars),
                'score_algo'       => $this->scoreFromOdds($odds, $stars),
                'engine_used'      => 'api-football',
                'analysis_source'  => 'api-football',
                'analysis_details' => json_encode([
                    'odds_source' => $oddsSource,
                    'percent'     => $block['percent'] ?? null,
                    'advice'      => $block['advice'] ?? null,
                    'winner'      => $block['winner'] ?? null,
                ]),
                'analysis_text'    => $this->buildAnalysis($block, $betType, $outcome),
                'status'           => 'pending',
                'is_published'     => true,
                'is_premium'       => $stars >= 3,
            ]
        );
    }

    /**
     * Mappe le bloc predictions d'API-Football vers bet_type/outcome/odds/stars.
     *
     * winner.comment : "Win" → 1X2 sec ; "Win or draw" → Double Chance.
     * percent : confiance dérivée du % le plus fort.
     *
     * @return array{0:string,1:string,2:float,3:int}
     */
    private function mapPrediction(array $block, string $home, string $away): array
    {
        $winnerName = $block['winner']['name']    ?? null;
        $comment    = strtolower($block['winner']['comment'] ?? '');
        $advice     = strtolower($block['advice'] ?? '');

        $homePct = (float) str_replace('%', '', $block['percent']['home'] ?? '0');
        $drawPct = (float) str_replace('%', '', $block['percent']['draw'] ?? '0');
        $awayPct = (float) str_replace('%', '', $block['percent']['away'] ?? '0');

        $isHome = $winnerName !== null && $winnerName === ($block['teams']['home']['name'] ?? $home);
        // À défaut du nom exact, on déduit du % dominant.
        $sense  = match (true) {
            $homePct >= $drawPct && $homePct >= $awayPct => 'home',
            $awayPct >= $homePct && $awayPct >= $drawPct => 'away',
            default                                       => 'draw',
        };
        if ($winnerName === $home) $sense = 'home';
        if ($winnerName === $away) $sense = 'away';

        $topPct = max($homePct, $drawPct, $awayPct);

        // "win or draw" ou advice "double chance" → Double Chance
        $isDoubleChance = str_contains($comment, 'draw') || str_contains($advice, 'double chance');

        if ($isDoubleChance) {
            [$outcome, $coverPct] = $sense === 'away'
                ? ["Nul ou $away", $drawPct + $awayPct]
                : ["$home ou nul", $homePct + $drawPct];

            return ['Double Chance', $outcome, $this->oddsFromPct($coverPct), $this->starsFromPct($coverPct)];
        }

        $outcome = match ($sense) {
            'home'  => $home,
            'away'  => $away,
            default => 'Match nul',
        };

        return ['1X2', $outcome, $this->oddsFromPct($topPct), $this->starsFromPct($topPct)];
    }

    /**
     * Cote estimée à partir d'une probabilité (%).
     *
     * cote = 1/p ajustée d'une marge bookmaker (~6%). On borne la probabilité
     * à 80% max : une double chance très probable garde une cote crédible
     * (plancher 1.10) au lieu de tomber à ~1.0 (impossible côté bookmaker).
     */
    private function oddsFromPct(float $pct): float
    {
        $p    = max(min($pct / 100.0, 0.80), 0.05);
        $odds = (1 / $p) * 1.06;

        return round(max($odds, 1.10), 2);
    }

    /**
     * Récupère la vraie cote 1xBet (The Odds API) pour l'outcome prédit.
     * Retourne [cote, source]. Source 'estimated' si aucune cote réelle trouvée
     * (match absent du calendrier The Odds API, fréquent car affiches ≠ API-Football).
     *
     * @return array{0:float,1:string}
     */
    private function resolveRealOdds(
        OddsApiService $oddsApi,
        string $home,
        string $away,
        string $betType,
        string $outcome,
        float $estimatedOdds
    ): array {
        // Matching STRICT : les deux équipes doivent correspondre. Une mauvaise
        // cote (faux positif fuzzy) est pire qu'une estimation pour la crédibilité.
        $real = $oddsApi->findStrict($home, $away);
        if (!$real) {
            return [$estimatedOdds, 'estimated'];
        }

        $h = (float) ($real['home'] ?? 0);
        $d = (float) ($real['draw'] ?? 0);
        $a = (float) ($real['away'] ?? 0);

        if ($betType === '1X2') {
            $odd = match (true) {
                $outcome === $home          => $h,
                $outcome === $away          => $a,
                $outcome === 'Match nul'    => $d,
                default                     => 0.0,
            };
            return $odd >= 1.01 ? [round($odd, 2), '1xbet'] : [$estimatedOdds, 'estimated'];
        }

        // Double Chance : pas de marché h2h direct → on la dérive des vraies cotes 1X2.
        // P(DC) = somme des probas des deux issues couvertes ; cote = 1/P, marge incluse.
        if ($betType === 'Double Chance' && $h > 1 && $d > 1 && $a > 1) {
            $covered = str_contains($outcome, 'Nul ou')
                ? [$d, $a]                       // X2
                : [$h, $d];                      // 1X (cas "X ou Y" générique → 1X par défaut)
            $prob = 0.0;
            foreach ($covered as $c) { $prob += 1 / $c; }
            $odd = $prob > 0 ? (1 / $prob) * 1.04 : 0.0;
            return $odd >= 1.05 ? [round($odd, 2), '1xbet'] : [$estimatedOdds, 'estimated'];
        }

        return [$estimatedOdds, 'estimated'];
    }

    private function starsFromPct(float $pct): int
    {
        if ($pct >= 75) return 4;
        if ($pct >= 62) return 3;
        if ($pct >= 50) return 2;
        return 1;
    }

    private function scoreFromStars(int $stars): float
    {
        return match ($stars) {
            4 => 88.0,
            3 => 75.0,
            2 => 64.0,
            default => 55.0,
        };
    }

    /**
     * Score continu (50–95) dérivé de la cote réelle, pour éviter les paliers
     * fixes (tous à 88). Proba implicite (1/cote) mappée sur 50–95.
     */
    private function scoreFromOdds(?float $odds, int $stars): float
    {
        if ($odds === null || $odds <= 1.0) {
            return $this->scoreFromStars($stars);
        }
        $prob  = 1.0 / $odds;
        $score = max(50.0, min(95.0, 50.0 + ($prob * 50.0)));
        return round($score, 1);
    }

    private function buildAnalysis(array $block, string $betType, string $outcome): string
    {
        $advice = $block['advice'] ?? null;
        $suffix = $advice ? sprintf(' Conseil API-Football : %s.', $advice) : '';

        return sprintf(
            'Pronostic %s : %s. Analyse officielle API-Football d\'après les statistiques des deux sélections.%s Pariez de façon responsable.',
            $betType,
            $outcome,
            $suffix
        );
    }

    private function invalidateCaches(): void
    {
        Cache::increment('predictions_cache_version');
        $date = now()->format('Y-m-d');
        foreach (['free', 'premium'] as $tier) {
            Cache::forget("coupon_v3_{$date}_{$tier}");
        }
    }
}
