<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service d'algorithme de prediction COTA v3.0
 *
 * Algorithme a 9 criteres ponderes (total 100 points):
 * 1. Forme recente (25%) - 10 derniers matchs avec bonus series
 * 2. Confrontations H2H (20%) - 8 dernieres confrontations
 * 3. Performance domicile/exterieur (15%) - Stats separees
 * 4. Position classement (12%) - Classement + ecart
 * 5. Statistiques buts (10%) - Buts marques/encaisses
 * 6. Horaire match (8%) - Bonus prime time
 * 7. Conditions meteo (5%) - Temperature, precipitation, vent
 * 8. Tirs cadres (3%) - Precision offensive
 * 9. Forme physique (2%) - Fatigue (matchs recents)
 *
 * Input: fixture au format API-Football
 * {fixture: {id, date, venue}, league: {id, name, season}, teams: {home: {id, name}, away: {id, name}}}
 */
class PredictionAlgorithmService
{
    private FootballApiService $footballApi;

    private ?RapidApiService $rapidApi;

    private MarketScoringService $marketScoring;

    const WEIGHTS = [
        'form' => 25,
        'h2h' => 20,
        'home_away' => 15,
        'league' => 12,
        'goals' => 10,
        'time' => 8,
        'weather' => 5,
        'shots' => 3,
        'physical' => 2,
    ];

    const CONFIDENCE_THRESHOLDS = [
        'very_high' => 85,
        'high' => 70,
        'medium' => 60,
        'low' => 50,
        'min_publish' => 50,
    ];

    // Bandes de cotes valides par marché — min 1.50 partout pour garantir de la valeur
    const ODDS_BANDS = [
        '1X2' => ['min' => 1.50, 'max' => 4.00],
        'Double Chance' => ['min' => 1.50, 'max' => 3.00],
        'Handicap' => ['min' => 1.55, 'max' => 2.80],
        'Over/Under' => ['min' => 1.55, 'max' => 2.50],
        'BTTS' => ['min' => 1.55, 'max' => 2.50],
        'Score Exact' => ['min' => 5.00, 'max' => 15.00],
        'Team Goals' => ['min' => 1.50, 'max' => 2.80],
        'Corners' => ['min' => 1.60, 'max' => 2.80],
        'Cards' => ['min' => 1.65, 'max' => 2.80],
        'Shots' => ['min' => 1.60, 'max' => 2.60],
    ];

    public function __construct(FootballApiService $footballApi, ?RapidApiService $rapidApi = null, ?MarketScoringService $marketScoring = null)
    {
        $this->footballApi = $footballApi;
        $this->rapidApi = $rapidApi;
        $this->marketScoring = $marketScoring ?? new MarketScoringService;
    }

    /**
     * Generer une prediction complete pour un match
     *
     * @param  array  $fixture  Fixture au format API-Football
     */
    public function generatePrediction(array $fixture): array
    {
        $homeTeam = $fixture['teams']['home'] ?? [];
        $awayTeam = $fixture['teams']['away'] ?? [];
        $homeTeamId = $homeTeam['id'] ?? null;
        $awayTeamId = $awayTeam['id'] ?? null;
        $leagueId = $fixture['league']['id'] ?? null;
        $season = $fixture['league']['season'] ?? (int) Carbon::now()->year;

        if (! $homeTeamId || ! $awayTeamId) {
            return $this->getDefaultPrediction();
        }

        $homeName = $homeTeam['name'] ?? '';
        $awayName = $awayTeam['name'] ?? '';

        $scores = [
            'form' => $this->calculateFormScore($homeTeamId, $awayTeamId, $leagueId, $season, $homeName, $awayName),
            'h2h' => $this->calculateH2HScore($homeTeamId, $awayTeamId, $homeName, $awayName),
            'home_away' => $this->calculateHomeAwayScore($homeTeamId, $awayTeamId, $leagueId, $season, $homeName, $awayName),
            'league' => $this->calculateLeagueScore($homeTeamId, $awayTeamId, $leagueId, $season, $homeName, $awayName),
            'goals' => $this->calculateGoalsScore($homeTeamId, $awayTeamId, $leagueId, $season, $homeName, $awayName),
            'time' => $this->calculateTimeScore($fixture['fixture'] ?? []),
            'weather' => $this->calculateWeatherScore($fixture['fixture']['venue'] ?? []),
            'shots' => $this->calculateShotsScore($homeTeamId, $awayTeamId, $leagueId, $season, $homeName, $awayName),
            'physical' => $this->calculatePhysicalScore($homeTeamId, $awayTeamId, $homeName, $awayName),
            // 10ème critère — calculé après determineBetType pour connaître notre outcome
            'third_party' => 0.0,
        ];

        // Signaux supplémentaires utilisés par les moteurs
        $cornersAvg = $this->getAverageCornersPerMatch($homeTeamId, $awayTeamId, $homeName, $awayName);
        $cardsAvg = $this->getAverageCardsPerMatch($homeTeamId, $awayTeamId);
        $topScorers = $this->getTopScorersForMatch($homeTeamId, $awayTeamId, $leagueId, $season);

        $totalScore = array_sum($scores);

        // Stats buts moyens pour les nouveaux marchés
        $homeGoalsFor = $this->extractGoalsFor($this->getTeamStats($homeTeamId, $leagueId, $season, $homeName));
        $awayGoalsFor = $this->extractGoalsFor($this->getTeamStats($awayTeamId, $leagueId, $season, $awayName));

        // Cascade multi-marchés (§7 CDC V2) — sélecteur retourne le meilleur marché
        $isTournament = $this->marketScoring->isTournament($fixture);
        $allCandidates = $this->buildAllCandidates(
            $scores, $totalScore, $homeTeam, $awayTeam,
            $cornersAvg, $cardsAvg, $homeGoalsFor, $awayGoalsFor
        );
        $selected = $this->marketScoring->bestMarketFor(
            $allCandidates,
            $totalScore,
            $isTournament,
            fifaGap: 0.0,
            homeName: $homeName,
            awayName: $awayName
        );
        // Tous les marchés switchables (multi-marchés cascade)
        $markets = $this->marketScoring->allMarketsFor(
            $allCandidates,
            $isTournament,
            fifaGap: 0.0,
            homeName: $homeName,
            awayName: $awayName
        );
        $stars = $this->calculateStars($totalScore);
        $analysis = $this->generateAnalysis($scores, $homeTeam, $awayTeam, $totalScore, $topScorers, $cornersAvg, $cardsAvg);

        return [
            'type' => $selected['type'],
            'outcome' => $selected['outcome'],
            'confidence' => round($totalScore, 2),
            'stars' => $stars,
            'odds' => $selected['odds'],
            'engine' => $selected['engine'],
            'market_value' => $selected['market_value'],
            'reasoning' => $analysis,
            'scores' => array_map(fn ($s) => round($s, 2), $scores),
            'is_premium' => $stars >= 3,
            'should_publish' => $totalScore >= self::CONFIDENCE_THRESHOLDS['min_publish'],
            'top_scorers' => $topScorers,
            'corners_avg' => $cornersAvg,
            'cards_avg' => $cardsAvg,
            // Champs A1 CDC v3.1
            'market_selection' => $selected['market_selection'],
            'market_score' => $selected['market_score'],
            'score_tier' => $selected['score_tier'],
            'active_side' => $selected['active_side'],
            'is_tournament' => $isTournament,
            // Marchés switchables (cascade multi-marchés)
            'markets' => $markets,
        ];
    }

    // ==================== CRITERE 1: FORME RECENTE (25 pts) ====================

    private function calculateFormScore(int $homeTeamId, int $awayTeamId, ?int $leagueId, int $season, string $homeName = '', string $awayName = ''): float
    {
        $maxScore = self::WEIGHTS['form'];

        $homeMatches = $this->getRecentMatches($homeTeamId, 10, $homeName);
        $awayMatches = $this->getRecentMatches($awayTeamId, 10, $awayName);

        $homeFormPoints = $this->calculateFormPoints($homeMatches, $homeTeamId);
        $awayFormPoints = $this->calculateFormPoints($awayMatches, $awayTeamId);

        $maxFormPoints = 30; // 10 wins × 3pts
        $homeRatio = min($homeFormPoints / $maxFormPoints, 1);
        $awayRatio = min($awayFormPoints / $maxFormPoints, 1);

        $formDiff = $homeRatio - $awayRatio;
        $baseScore = ($maxScore / 2) + ($formDiff * ($maxScore / 2));

        $streakBonus = $this->calculateStreakBonus($homeMatches, $awayMatches, $homeTeamId, $awayTeamId);

        return min(max($baseScore + $streakBonus, 0), $maxScore);
    }

    // ==================== CRITERE 2: H2H (20 pts) ====================

    private function calculateH2HScore(int $homeTeamId, int $awayTeamId, string $homeName = '', string $awayName = ''): float
    {
        $maxScore = self::WEIGHTS['h2h'];

        $dbMeetings = \App\Models\FootballMatch::where(function ($q) use ($homeTeamId, $awayTeamId, $homeName, $awayName) {
            // Cherche par IDs
            $q->where(fn ($s) => $s->where('home_team_id', $homeTeamId)->where('away_team_id', $awayTeamId))
                ->orWhere(fn ($s) => $s->where('home_team_id', $awayTeamId)->where('away_team_id', $homeTeamId));
            // Fallback par noms (matchs TheSportsDB)
            if ($homeName !== '' && $awayName !== '') {
                $q->orWhere(fn ($s) => $s->where('home_team', $homeName)->where('away_team', $awayName))
                    ->orWhere(fn ($s) => $s->where('home_team', $awayName)->where('away_team', $homeName));
            }
        })
            ->where('status', 'finished')
            ->whereNotNull('home_score')
            ->orderByDesc('match_date')
            ->limit(8)
            ->get();

        $meetings = $dbMeetings->map(fn ($m) => [
            'teams' => [
                'home' => ['id' => $m->home_team_id ?? 0, 'name' => $m->home_team],
                'away' => ['id' => $m->away_team_id ?? 0, 'name' => $m->away_team],
            ],
            'goals' => ['home' => $m->home_score, 'away' => $m->away_score],
        ])->toArray();

        if (empty($meetings)) {
            return $maxScore * 0.50; // neutre si pas de données H2H
        }

        $meetings = array_slice($meetings, 0, 8);
        $homeWins = 0;
        $awayWins = 0;
        $draws = 0;
        $totalWeight = 0;

        foreach ($meetings as $index => $meeting) {
            $weight = 8 - $index;
            $totalWeight += $weight;
            $result = $this->extractH2HResult($meeting, $homeTeamId, $homeName);

            if ($result === 'home') {
                $homeWins += $weight;
            } elseif ($result === 'away') {
                $awayWins += $weight;
            } else {
                $draws += $weight;
            }
        }

        if ($totalWeight === 0) {
            return $maxScore * 0.50;
        }

        // Score centré sur 0.5 : reflète réellement l'historique des deux équipes
        $homeRatio = ($homeWins + ($draws * 0.5)) / $totalWeight;

        return min(max($homeRatio * $maxScore, 0), $maxScore);
    }

    // ==================== CRITERE 3: DOMICILE/EXTERIEUR (15 pts) ====================

    private function calculateHomeAwayScore(int $homeTeamId, int $awayTeamId, ?int $leagueId, int $season, string $homeName = '', string $awayName = ''): float
    {
        $maxScore = self::WEIGHTS['home_away'];

        if (! $leagueId) {
            return $maxScore * 0.50;
        }

        $homeStats = $this->getTeamStats($homeTeamId, $leagueId, $season, $homeName);
        $awayStats = $this->getTeamStats($awayTeamId, $leagueId, $season, $awayName);

        $homeHomeRatio = $this->extractHomeWinRatio($homeStats);
        $awayAwayRatio = $this->extractAwayWinRatio($awayStats);

        // Comparaison directe : performance dom. de l'équipe locale vs perf. ext. de l'équipe visiteuse
        // Suppression du +0.10 artificiel qui favorisait toujours le domicile
        $diff = $homeHomeRatio - $awayAwayRatio;
        $baseScore = ($maxScore / 2) + ($diff * ($maxScore / 2));

        return min(max($baseScore, 0), $maxScore);
    }

    // ==================== CRITERE 4: CLASSEMENT (12 pts) ====================

    private function calculateLeagueScore(int $homeTeamId, int $awayTeamId, ?int $leagueId, int $season, string $homeName = '', string $awayName = ''): float
    {
        $maxScore = self::WEIGHTS['league'];

        if (! $leagueId) {
            return $maxScore * 0.5;
        }

        $data = Cache::remember("standings:{$leagueId}:{$season}", 3600, function () use ($leagueId, $season) {
            return $this->footballApi->getStandings($leagueId, $season);
        });

        $standings = $data['response'][0]['league']['standings'][0] ?? [];

        if (empty($standings)) {
            return $maxScore * 0.5;
        }

        $homePosition = $this->findTeamPosition($standings, $homeTeamId);
        $awayPosition = $this->findTeamPosition($standings, $awayTeamId);

        if ($homePosition === null || $awayPosition === null) {
            return $maxScore * 0.5;
        }

        $totalTeams = count($standings) ?: 20;
        $homeNormalized = 1 - (($homePosition - 1) / $totalTeams);
        $awayNormalized = 1 - (($awayPosition - 1) / $totalTeams);
        $positionDiff = $homeNormalized - $awayNormalized;
        $baseScore = ($maxScore / 2) + ($positionDiff * ($maxScore / 2));

        if (abs($awayPosition - $homePosition) > 10) {
            $baseScore += ($positionDiff > 0 ? 2 : -2);
        }

        return min(max($baseScore, 0), $maxScore);
    }

    // ==================== CRITERE 5: BUTS (10 pts) ====================

    private function calculateGoalsScore(int $homeTeamId, int $awayTeamId, ?int $leagueId, int $season, string $homeName = '', string $awayName = ''): float
    {
        $maxScore = self::WEIGHTS['goals'];

        if (! $leagueId) {
            return $maxScore * 0.5;
        }

        $homeStats = $this->getTeamStats($homeTeamId, $leagueId, $season, $homeName);
        $awayStats = $this->getTeamStats($awayTeamId, $leagueId, $season, $awayName);

        $homeGoalsFor = $this->extractGoalsFor($homeStats);
        $awayGoalsFor = $this->extractGoalsFor($awayStats);
        $homeGoalsAgainst = $this->extractGoalsAgainst($homeStats);
        $awayGoalsAgainst = $this->extractGoalsAgainst($awayStats);

        $homeOffense = min($homeGoalsFor / 2.5, 1);
        $awayDefense = 1 - min($awayGoalsAgainst / 2.0, 1);
        $homeDefense = 1 - min($homeGoalsAgainst / 2.0, 1);
        $awayOffense = min($awayGoalsFor / 2.5, 1);

        $homeAdvantage = ($homeOffense + $homeDefense) / 2;
        $awayAdvantage = ($awayOffense + $awayDefense) / 2;

        $diff = $homeAdvantage - $awayAdvantage;
        $baseScore = ($maxScore / 2) + ($diff * ($maxScore / 2));

        return min(max($baseScore, 0), $maxScore);
    }

    // ==================== CRITERE 6: HORAIRE (8 pts) ====================

    private function calculateTimeScore(array $fixtureInfo): float
    {
        $maxScore = self::WEIGHTS['time'];
        $date = $fixtureInfo['date'] ?? null;

        if (! $date) {
            return $maxScore * 0.5;
        }

        try {
            $matchTime = Carbon::parse($date);
            $hour = $matchTime->hour;
            $dow = $matchTime->dayOfWeek;

            if ($hour >= 19 && $hour <= 22) {
                $timeBonus = 1.0;
            } elseif ($hour >= 14 && $hour < 19) {
                $timeBonus = 0.75;
            } elseif ($hour >= 10 && $hour < 14) {
                $timeBonus = 0.5;
            } else {
                $timeBonus = 0.4;
            }

            if ($dow === 0 || $dow === 6) {
                $timeBonus = min($timeBonus + 0.1, 1);
            }

            return $maxScore * $timeBonus;
        } catch (\Exception $e) {
            return $maxScore * 0.5;
        }
    }

    // ==================== CRITERE 7: METEO (5 pts) ====================

    private function calculateWeatherScore(array $venue): float
    {
        $maxScore = self::WEIGHTS['weather'];
        $city = $venue['city'] ?? null;

        if (! $city) {
            return $maxScore * 0.6;
        }

        $weather = $this->getWeatherData($city);

        if (! $weather) {
            return $maxScore * 0.6;
        }

        $score = $maxScore;
        $temp = $weather['temp'] ?? 18;
        $rain = $weather['rain'] ?? 0;
        $wind = $weather['wind'] ?? 0;

        if ($temp < 5 || $temp > 30) {
            $score -= 2;
        } elseif ($temp < 10 || $temp > 25) {
            $score -= 1;
        }

        if ($rain > 5) {
            $score -= 2;
        } elseif ($rain > 2) {
            $score -= 1;
        }

        if ($wind > 40) {
            $score -= 1.5;
        } elseif ($wind > 25) {
            $score -= 0.5;
        }

        return max($score, 0);
    }

    // ==================== CRITERE 8: TIRS CADRES (3 pts) ====================

    private function calculateShotsScore(int $homeTeamId, int $awayTeamId, ?int $leagueId, int $season, string $homeName = '', string $awayName = ''): float
    {
        $maxScore = self::WEIGHTS['shots'];

        if (! $leagueId) {
            return $maxScore * 0.5;
        }

        $homeStats = $this->getTeamStats($homeTeamId, $leagueId, $season, $homeName);
        $awayStats = $this->getTeamStats($awayTeamId, $leagueId, $season, $awayName);

        $homeShotsOnTarget = $this->extractShotsOnTarget($homeStats);
        $awayShotsOnTarget = $this->extractShotsOnTarget($awayStats);

        $homeRatio = min($homeShotsOnTarget / 5, 1);
        $awayRatio = min($awayShotsOnTarget / 5, 1);

        $diff = $homeRatio - $awayRatio;
        $baseScore = ($maxScore / 2) + ($diff * ($maxScore / 2));

        return min(max($baseScore, 0), $maxScore);
    }

    // ==================== CRITERE 9: FORME PHYSIQUE (2 pts) ====================

    private function calculatePhysicalScore(int $homeTeamId, int $awayTeamId, string $homeName = '', string $awayName = ''): float
    {
        $maxScore = self::WEIGHTS['physical'];

        $homeMatches = $this->getRecentMatches($homeTeamId, 10, $homeName);
        $awayMatches = $this->getRecentMatches($awayTeamId, 10, $awayName);

        $homeRecentCount = $this->countMatchesInLastDays($homeMatches, 7);
        $awayRecentCount = $this->countMatchesInLastDays($awayMatches, 7);

        $homeFatigue = min($homeRecentCount / 3, 1);
        $awayFatigue = min($awayRecentCount / 3, 1);
        $fatigueDiff = $awayFatigue - $homeFatigue;
        $baseScore = ($maxScore / 2) + ($fatigueDiff * ($maxScore / 2));

        return min(max($baseScore, 0), $maxScore);
    }

    // ==================== CASCADE MULTI-MARCHÉS (§7 CDC V2) ====================

    /**
     * Construit la liste complète de candidats marchés — délègue le choix final
     * à MarketScoringService::bestMarketFor() (A1 CDC v3.1).
     *
     * @return array Liste de candidats format makeCandidate()
     */
    private function buildAllCandidates(
        array $scores,
        float $totalScore,
        array $homeTeam,
        array $awayTeam,
        float $cornersAvg = 0.0,
        float $cardsAvg = 0.0,
        float $homeGoalsFor = 1.2,
        float $awayGoalsFor = 1.2
    ): array {
        $candidates = [];

        // ── Ratios normalisés pour les deux moteurs ───────────────────────────
        $formRatio = $scores['form'] / self::WEIGHTS['form'];
        $h2hRatio = $scores['h2h'] / self::WEIGHTS['h2h'];
        $homeAwayRatio = $scores['home_away'] / self::WEIGHTS['home_away'];
        $goalsRatio = $scores['goals'] / self::WEIGHTS['goals'];
        $shotsRatio = $scores['shots'] / self::WEIGHTS['shots'];
        $leagueRatio = $scores['league'] / self::WEIGHTS['league'];

        $homeName = $homeTeam['name'] ?? 'Home';
        $awayName = $awayTeam['name'] ?? 'Away';

        // Avantage domicile composite (Moteur Force)
        $homeAdv = ($formRatio * 0.40) + ($h2hRatio * 0.35) + ($homeAwayRatio * 0.25);
        $awayAdv = 1.0 - $homeAdv;

        // ── MOTEUR FORCE — 1X2, Double Chance, Handicap ──────────────────────
        $candidates = array_merge($candidates, $this->scoreForceEngine($homeAdv, $awayAdv, $leagueRatio));

        // ── MOTEUR BUTS TOTAL — Over/Under, BTTS ─────────────────────────────
        $goalsEngineScore = ($goalsRatio * 0.70) + ($shotsRatio * 0.30);
        $candidates = array_merge($candidates, $this->scoreGoalsEngine($goalsEngineScore));

        // ── MOTEUR BUTS PAR ÉQUIPE — Team Goals ──────────────────────────────
        $candidates = array_merge($candidates, $this->scoreTeamGoalsEngine(
            $homeName, $awayName, $homeGoalsFor, $awayGoalsFor, $homeAdv, $awayAdv
        ));

        // ── MOTEUR CORNERS ───────────────────────────────────────────────────
        if ($cornersAvg > 0) {
            $candidates = array_merge($candidates, $this->scoreCornersEngine($cornersAvg, $goalsEngineScore));
        }

        // ── MOTEUR CARTONS ────────────────────────────────────────────────────
        if ($cardsAvg > 0) {
            $candidates = array_merge($candidates, $this->scoreCardsEngine($cardsAvg));
        }

        // ── MOTEUR TIRS CADRÉS ────────────────────────────────────────────────
        $candidates = array_merge($candidates, $this->scoreShotsEngine(
            $homeName, $awayName, $shotsRatio, $homeAdv, $awayAdv
        ));

        // ── HAUTE VARIANCE — Score Exact (signal exceptionnel) ───────────────
        if ($homeAdv >= 0.80 || $awayAdv >= 0.80) {
            $winner = $homeAdv >= 0.80 ? '1-0' : '0-1';
            $candidates[] = $this->makeCandidate('Score Exact', $winner, 7.00, 'high_variance', $totalScore);
        }

        // ── MOTEUR UNDERDOG — paris tentés sur l'équipe censée perdre ────────
        // Au lieu d'ignorer le faible, on propose des marchés "risqués" à valeur :
        // l'équipe défavorisée marque (+0.5 / +1.5) ou double chance défensive.
        $candidates = array_merge($candidates, $this->scoreUnderdogEngine(
            $homeName, $awayName, $homeAdv, $awayAdv, $homeGoalsFor, $awayGoalsFor
        ));

        // Retourner les candidats valides dans leur bande de cote
        // Le choix final est délégué à MarketScoringService::bestMarketFor()
        return array_values(
            array_filter($candidates, fn ($c) => $this->isOddsInBand($c['type'], $c['odds']))
        );
    }

    /**
     * Moteur Force — produit les candidats 1X2 / Double Chance / Handicap
     */
    private function scoreForceEngine(float $homeAdv, float $awayAdv, float $leagueRatio): array
    {
        $c = [];

        if ($homeAdv >= 0.70) {
            $c[] = $this->makeCandidate('1X2', '1', 1.70, 'force', $homeAdv * 100);
        } elseif ($homeAdv >= 0.62) {
            $c[] = $this->makeCandidate('1X2', '1', 2.00, 'force', $homeAdv * 100);
            $c[] = $this->makeCandidate('Double Chance', '1X', 1.42, 'force', $homeAdv * 90);
        } elseif ($homeAdv >= 0.55) {
            $c[] = $this->makeCandidate('Double Chance', '1X', 1.50, 'force', $homeAdv * 85);
        } elseif ($awayAdv >= 0.70) {
            $c[] = $this->makeCandidate('1X2', '2', 2.10, 'force', $awayAdv * 100);
        } elseif ($awayAdv >= 0.62) {
            $c[] = $this->makeCandidate('1X2', '2', 2.40, 'force', $awayAdv * 100);
            $c[] = $this->makeCandidate('Double Chance', 'X2', 1.52, 'force', $awayAdv * 90);
        } elseif ($awayAdv >= 0.55) {
            $c[] = $this->makeCandidate('Double Chance', 'X2', 1.60, 'force', $awayAdv * 85);
        } else {
            // Très équilibré : classement comme tie-breaker
            $outcome = $leagueRatio >= 0.55 ? '1X' : 'X2';
            $c[] = $this->makeCandidate('Double Chance', $outcome, 1.55, 'force', 55.0);
        }

        return $c;
    }

    /**
     * Moteur Buts — produit les candidats Over/Under / BTTS
     * goalsEngineScore = 0–1 (>0.5 = offensive, <0.5 = défensive)
     */
    private function scoreGoalsEngine(float $goalsEngineScore): array
    {
        $c = [];

        if ($goalsEngineScore >= 0.68) {
            $c[] = $this->makeCandidate('BTTS', 'Oui', 1.75, 'goals', $goalsEngineScore * 100);
            $c[] = $this->makeCandidate('Over/Under', 'Over 2.5', 1.72, 'goals', $goalsEngineScore * 95);
        } elseif ($goalsEngineScore >= 0.58) {
            $c[] = $this->makeCandidate('Over/Under', 'Over 2.5', 1.80, 'goals', $goalsEngineScore * 100);
        } elseif ($goalsEngineScore <= 0.32) {
            $c[] = $this->makeCandidate('Over/Under', 'Under 2.5', 1.78, 'goals', (1 - $goalsEngineScore) * 100);
            $c[] = $this->makeCandidate('BTTS', 'Non', 1.90, 'goals', (1 - $goalsEngineScore) * 90);
        } elseif ($goalsEngineScore <= 0.42) {
            $c[] = $this->makeCandidate('Over/Under', 'Under 2.5', 1.82, 'goals', (1 - $goalsEngineScore) * 100);
        }

        return $c;
    }

    /**
     * Moteur Buts par équipe — "Home Over 0.5", "Away Over 1.5", etc.
     * Utilise les moyennes de buts marqués par équipe sur la saison.
     */
    private function scoreTeamGoalsEngine(
        string $homeName, string $awayName,
        float $homeGoalsFor, float $awayGoalsFor,
        float $homeAdv, float $awayAdv
    ): array {
        $c = [];

        // Domicile offensif (>1.5 buts/match en moyenne)
        if ($homeGoalsFor >= 1.8 && $homeAdv >= 0.55) {
            $c[] = $this->makeCandidate('Team Goals', "{$homeName} Over 1.5", 1.75, 'team_goals', $homeGoalsFor * 50);
        } elseif ($homeGoalsFor >= 1.2 && $homeAdv >= 0.50) {
            $c[] = $this->makeCandidate('Team Goals', "{$homeName} Over 0.5", 1.55, 'team_goals', $homeGoalsFor * 45);
        }

        // Extérieur offensif
        if ($awayGoalsFor >= 1.6 && $awayAdv >= 0.50) {
            $c[] = $this->makeCandidate('Team Goals', "{$awayName} Over 1.5", 1.85, 'team_goals', $awayGoalsFor * 50);
        } elseif ($awayGoalsFor >= 1.1 && $awayAdv >= 0.45) {
            $c[] = $this->makeCandidate('Team Goals', "{$awayName} Over 0.5", 1.60, 'team_goals', $awayGoalsFor * 45);
        }

        // Match avec peu de buts (une équipe défensive)
        if ($homeGoalsFor <= 0.8 || $awayGoalsFor <= 0.8) {
            $weakTeam = $homeGoalsFor <= $awayGoalsFor ? $homeName : $awayName;
            $c[] = $this->makeCandidate('Team Goals', "{$weakTeam} Under 1.5", 1.90, 'team_goals', 60.0);
        }

        return $c;
    }

    /**
     * Moteur Corners — "Over/Under X.5 corners"
     * cornersAvg = moyenne de corners par match (home + away combinés)
     */
    private function scoreCornersEngine(float $cornersAvg, float $goalsScore): array
    {
        $c = [];

        if ($cornersAvg >= 11.0) {
            $c[] = $this->makeCandidate('Corners', 'Over 10.5 corners', 1.80, 'corners', $cornersAvg * 7);
        } elseif ($cornersAvg >= 9.5) {
            $c[] = $this->makeCandidate('Corners', 'Over 9.5 corners', 1.75, 'corners', $cornersAvg * 7);
        } elseif ($cornersAvg >= 8.5 && $goalsScore >= 0.55) {
            $c[] = $this->makeCandidate('Corners', 'Over 8.5 corners', 1.70, 'corners', $cornersAvg * 6);
        } elseif ($cornersAvg <= 7.0) {
            $c[] = $this->makeCandidate('Corners', 'Under 8.5 corners', 1.85, 'corners', (10 - $cornersAvg) * 7);
        }

        return $c;
    }

    /**
     * Moteur Cartons — "Over/Under X.5 cards"
     */
    private function scoreCardsEngine(float $cardsAvg): array
    {
        $c = [];

        if ($cardsAvg >= 5.0) {
            $c[] = $this->makeCandidate('Cards', 'Over 4.5 cards', 1.80, 'cards', $cardsAvg * 15);
        } elseif ($cardsAvg >= 4.0) {
            $c[] = $this->makeCandidate('Cards', 'Over 3.5 cards', 1.75, 'cards', $cardsAvg * 15);
        } elseif ($cardsAvg >= 3.0) {
            $c[] = $this->makeCandidate('Cards', 'Over 2.5 cards', 1.70, 'cards', $cardsAvg * 15);
        } elseif ($cardsAvg <= 2.0) {
            $c[] = $this->makeCandidate('Cards', 'Under 3.5 cards', 1.85, 'cards', (5 - $cardsAvg) * 15);
        }

        return $c;
    }

    /**
     * Moteur Tirs cadrés — "Home Over 4.5 shots", "Away Over 3.5 shots"
     * shotsRatio = score tirs / poids_max (0–1)
     */
    private function scoreShotsEngine(
        string $homeName, string $awayName,
        float $shotsRatio,
        float $homeAdv, float $awayAdv
    ): array {
        $c = [];

        // Signal offensif fort côté domicile
        if ($shotsRatio >= 0.70 && $homeAdv >= 0.60) {
            $c[] = $this->makeCandidate('Shots', "{$homeName} Over 4.5 shots", 1.75, 'shots', $shotsRatio * 80);
        } elseif ($shotsRatio >= 0.55 && $homeAdv >= 0.55) {
            $c[] = $this->makeCandidate('Shots', "{$homeName} Over 3.5 shots", 1.65, 'shots', $shotsRatio * 70);
        }

        // Signal offensif fort côté extérieur
        if ($shotsRatio >= 0.65 && $awayAdv >= 0.55) {
            $c[] = $this->makeCandidate('Shots', "{$awayName} Over 3.5 shots", 1.80, 'shots', $shotsRatio * 75);
        }

        // Match avec peu de tirs (les deux défensives)
        if ($shotsRatio <= 0.30) {
            $c[] = $this->makeCandidate('Shots', 'Under 8.5 total shots', 1.85, 'shots', (1 - $shotsRatio) * 70);
        }

        return $c;
    }

    /**
     * Moteur Underdog — paris "risqués" sur l'équipe censée perdre.
     *
     * Logique : quand une équipe domine nettement (homeAdv ou awayAdv élevé),
     * le marché 1X2 est évident. Pour varier le coupon et offrir de la valeur,
     * on propose à la place des paris tentés sur le faible :
     *  - le faible marque (+0.5 ou +1.5) → cote attractive
     *  - double chance défensive (le faible ne perd pas)
     * Ces candidats sont marqués is_risky pour que l'UI les distingue.
     */
    private function scoreUnderdogEngine(
        string $homeName, string $awayName,
        float $homeAdv, float $awayAdv,
        float $homeGoalsFor, float $awayGoalsFor
    ): array {
        $c = [];

        // Identifier le favori et le faible (écart net requis)
        $gap = abs($homeAdv - $awayAdv);
        if ($gap < 0.30) {
            return $c; // match trop serré → pas de pari underdog pertinent
        }

        $homeIsFavorite = $homeAdv > $awayAdv;
        $underdogName = $homeIsFavorite ? $awayName : $homeName;
        $underdogGoals = $homeIsFavorite ? $awayGoalsFor : $homeGoalsFor;
        // Confiance underdog : modérée par construction (pari tenté), pondérée par sa capacité offensive
        $baseConf = 45.0 + min($underdogGoals * 12.0, 25.0);

        // Le faible marque au moins 1 but (cote attractive)
        $c[] = $this->makeCandidate('Team Goals', "{$underdogName} Over 0.5", 2.10, 'underdog', $baseConf, isRisky: true);

        // Si le faible a une vraie attaque, tenter +1.5
        if ($underdogGoals >= 1.2) {
            $c[] = $this->makeCandidate('Team Goals', "{$underdogName} Over 1.5", 3.40, 'underdog', $baseConf * 0.85, isRisky: true);
        }

        // Double chance défensive : le faible ne perd pas
        $dcOutcome = $homeIsFavorite ? 'X2' : '1X';
        $c[] = $this->makeCandidate('Double Chance', $dcOutcome, 2.30, 'underdog', $baseConf * 0.9, isRisky: true);

        return $c;
    }

    /**
     * Construit un candidat marché avec son score valeur.
     * market_value = (confidence/100) × log(odds) — favorise cotes attractives
     * sans tomber dans les cotes déséquilibrées hors bande.
     */
    private function makeCandidate(
        string $type,
        string $outcome,
        float $odds,
        string $engine,
        float $confidence,
        bool $isRisky = false
    ): array {
        $oddsValue = $odds > 1.0 ? log($odds) : 0.0;
        $marketValue = round(($confidence / 100.0) * $oddsValue, 4);

        return [
            'type' => $type,
            'outcome' => $outcome,
            'odds' => round($odds, 2),
            'engine' => $engine,
            'market_value' => $marketValue,
            'confidence' => round($confidence, 2),
            'is_risky' => $isRisky,
        ];
    }

    /**
     * Vérifie que la cote se trouve dans la bande valide du marché (§10.2 CDC V2).
     */
    private function isOddsInBand(string $type, float $odds): bool
    {
        $band = self::ODDS_BANDS[$type] ?? null;
        if (! $band) {
            return true;
        }

        return $odds >= $band['min'] && $odds <= $band['max'];
    }

    // ── Corners : moyenne corners/match des 10 derniers matchs des 2 équipes ──

    private function getAverageCornersPerMatch(int $homeTeamId, int $awayTeamId, string $homeName = '', string $awayName = ''): float
    {
        $cacheKey = "corners_avg:{$homeTeamId}:{$awayTeamId}";

        return Cache::remember($cacheKey, 7200, function () use ($homeTeamId, $awayTeamId, $homeName, $awayName) {
            $homeCorners = $this->extractAverageCorners($this->getRecentMatches($homeTeamId, 10, $homeName), $homeTeamId);
            $awayCorners = $this->extractAverageCorners($this->getRecentMatches($awayTeamId, 10, $awayName), $awayTeamId);
            if ($homeCorners === null || $awayCorners === null) {
                return 0.0;
            }

            return round($homeCorners + $awayCorners, 1);
        });
    }

    private function extractAverageCorners(array $fixtures, int $teamId): ?float
    {
        $total = 0;
        $count = 0;
        foreach (array_slice($fixtures, 0, 10) as $fixture) {
            $stats = $fixture['statistics'] ?? null;
            if (! $stats) {
                continue;
            }
            foreach ($stats as $teamStats) {
                if (($teamStats['team']['id'] ?? null) !== $teamId) {
                    continue;
                }
                foreach ($teamStats['statistics'] ?? [] as $stat) {
                    if (($stat['type'] ?? '') === 'Corner Kicks') {
                        $total += (int) ($stat['value'] ?? 0);
                        $count++;
                        break;
                    }
                }
            }
        }

        return $count > 0 ? round($total / $count, 1) : null;
    }

    // ── Cartons : moyenne cartons jaunes/match des 2 équipes ──────────────────

    private function getAverageCardsPerMatch(int $homeTeamId, int $awayTeamId, string $homeName = '', string $awayName = ''): float
    {
        $cacheKey = "cards_avg:{$homeTeamId}:{$awayTeamId}";

        return Cache::remember($cacheKey, 7200, function () use ($homeTeamId, $awayTeamId, $homeName, $awayName) {
            $homeCards = $this->extractAverageCards($this->getRecentMatches($homeTeamId, 10, $homeName), $homeTeamId);
            $awayCards = $this->extractAverageCards($this->getRecentMatches($awayTeamId, 10, $awayName), $awayTeamId);
            if ($homeCards === null || $awayCards === null) {
                return 0.0;
            }

            return round($homeCards + $awayCards, 1);
        });
    }

    private function extractAverageCards(array $fixtures, int $teamId): ?float
    {
        $total = 0;
        $count = 0;
        foreach (array_slice($fixtures, 0, 10) as $fixture) {
            $events = $fixture['events'] ?? null;
            if (! is_array($events)) {
                continue;
            }
            $matchCards = 0;
            foreach ($events as $event) {
                if (($event['team']['id'] ?? null) !== $teamId) {
                    continue;
                }
                $type = $event['type'] ?? '';
                $detail = $event['detail'] ?? '';
                if ($type === 'Card' && in_array($detail, ['Yellow Card', 'Red Card'])) {
                    $matchCards++;
                }
            }
            $total += $matchCards;
            $count++;
        }

        return $count > 0 ? round($total / $count, 1) : null;
    }

    // ── Buteurs : top scoreurs via API-Football /players/topscorers ───────────

    private function getTopScorersForMatch(int $homeTeamId, int $awayTeamId, ?int $leagueId, int $season): array
    {
        if (! $leagueId) {
            return [];
        }

        $cacheKey = "topscorers:{$leagueId}:{$season}";
        $data = Cache::remember($cacheKey, 86400, function () use ($leagueId, $season) {
            try {
                return $this->footballApi->makeRequest('/players/topscorers', [
                    'league' => $leagueId,
                    'season' => $season,
                ], 86400);
            } catch (\Throwable $e) {
                return null;
            }
        });

        if (! $data) {
            return [];
        }

        $scorers = [];
        foreach ($data['response'] ?? [] as $entry) {
            $teamId = $entry['statistics'][0]['team']['id'] ?? null;
            if (! in_array($teamId, [$homeTeamId, $awayTeamId])) {
                continue;
            }

            $goals = $entry['statistics'][0]['goals']['total'] ?? 0;
            if (! $goals) {
                continue;
            }

            $scorers[] = [
                'name' => $entry['player']['name'] ?? 'Inconnu',
                'team' => $entry['statistics'][0]['team']['name'] ?? '',
                'goals' => (int) $goals,
                'side' => $teamId === $homeTeamId ? 'home' : 'away',
            ];

            if (count($scorers) >= 4) {
                break;
            }
        }

        return $scorers;
    }

    private function calculateStars(float $totalScore): int
    {
        if ($totalScore >= self::CONFIDENCE_THRESHOLDS['very_high']) {
            return 4;
        }
        if ($totalScore >= self::CONFIDENCE_THRESHOLDS['high']) {
            return 3;
        }
        if ($totalScore >= self::CONFIDENCE_THRESHOLDS['medium']) {
            return 2;
        }

        return 1;
    }

    private function generateAnalysis(
        array $scores,
        array $homeTeam,
        array $awayTeam,
        float $totalScore,
        array $topScorers = [],
        float $cornersAvg = 0.0,
        float $cardsAvg = 0.0
    ): string {
        $home = $homeTeam['name'] ?? 'Domicile';
        $away = $awayTeam['name'] ?? 'Exterieur';
        $analysis = [];

        if ($scores['form'] >= self::WEIGHTS['form'] * 0.7) {
            $analysis[] = "{$home} affiche une excellente forme recente";
        } elseif ($scores['form'] <= self::WEIGHTS['form'] * 0.3) {
            $analysis[] = "{$away} est en meilleure forme actuellement";
        }

        if ($scores['h2h'] >= self::WEIGHTS['h2h'] * 0.7) {
            $analysis[] = "L'historique des confrontations favorise {$home}";
        } elseif ($scores['h2h'] <= self::WEIGHTS['h2h'] * 0.3) {
            $analysis[] = "{$away} a l'avantage dans les confrontations directes";
        }

        if ($scores['league'] >= self::WEIGHTS['league'] * 0.7) {
            $analysis[] = "{$home} est mieux classe au championnat";
        }

        if ($scores['goals'] >= self::WEIGHTS['goals'] * 0.7) {
            $analysis[] = "Les statistiques de buts favorisent {$home}";
        } elseif ($scores['goals'] <= self::WEIGHTS['goals'] * 0.3) {
            $analysis[] = "{$away} presente de meilleures statistiques offensives";
        }

        if ($scores['time'] >= self::WEIGHTS['time'] * 0.8) {
            $analysis[] = 'Match en prime time avec conditions optimales';
        }

        if ($cornersAvg >= 10.5) {
            $analysis[] = "Moyenne elevee de corners ({$cornersAvg}/match) — attendez-vous a beaucoup d'actions sur les ailes";
        } elseif ($cornersAvg > 0 && $cornersAvg <= 7.5) {
            $analysis[] = "Peu de corners en moyenne ({$cornersAvg}/match) — jeu central predominant";
        }

        if ($cardsAvg >= 4.5) {
            $analysis[] = "Match physique attendu : moyenne de {$cardsAvg} cartons par match";
        } elseif ($cardsAvg > 0 && $cardsAvg <= 2.0) {
            $analysis[] = "Match propre attendu : peu de cartons en moyenne ({$cardsAvg}/match)";
        }

        if (! empty($topScorers)) {
            $names = array_map(fn ($s) => $s['name'].' ('.$s['goals'].' buts)', array_slice($topScorers, 0, 2));
            $analysis[] = 'Buteurs a surveiller : '.implode(', ', $names);
        }

        if (empty($analysis)) {
            $analysis[] = "Match equilibre, aucun avantage clair d'un cote";
        }

        $confidenceText = $totalScore >= 80 ? 'Confiance elevee' :
                         ($totalScore >= 65 ? 'Confiance moyenne' : 'Confiance moderee');

        return $confidenceText." ({$totalScore}/100). ".implode('. ', $analysis).'.';
    }

    // ==================== METHODES UTILITAIRES ====================

    private function getTeamStats(int $teamId, int $leagueId, int $season, string $teamName = ''): ?array
    {
        $matches = \App\Models\FootballMatch::where(function ($q) use ($teamId, $teamName) {
            $q->where(fn ($s) => $s->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId));
            if ($teamName !== '') {
                $q->orWhere(fn ($s) => $s->where('home_team', $teamName)->orWhere('away_team', $teamName));
            }
        })
            ->where('status', 'finished')
            ->orderByDesc('match_date')
            ->limit(20)
            ->get();

        if ($matches->isEmpty()) {
            return null;
        }

        $homeWins = $homePlayed = $awayWins = $awayPlayed = 0;
        $goalsScored = $goalsConceded = 0;

        foreach ($matches as $m) {
            $isHome = ($m->home_team_id && $m->home_team_id === $teamId)
                   || ($teamName !== '' && $m->home_team === $teamName);
            $scored = $isHome ? $m->home_score : $m->away_score;
            $conceded = $isHome ? $m->away_score : $m->home_score;

            if ($scored === null) {
                continue;
            }

            $goalsScored += $scored;
            $goalsConceded += $conceded;

            if ($isHome) {
                $homePlayed++;
                if ($scored > $conceded) {
                    $homeWins++;
                }
            } else {
                $awayPlayed++;
                if ($scored > $conceded) {
                    $awayWins++;
                }
            }
        }

        $total = $matches->count();

        return [
            'fixtures' => [
                'wins' => ['home' => $homeWins,  'away' => $awayWins],
                'draws' => ['home' => 0,           'away' => 0],
                'played' => ['home' => $homePlayed, 'away' => $awayPlayed, 'total' => $total],
            ],
            'goals' => [
                'for' => ['average' => ['total' => $total > 0 ? round($goalsScored / $total, 2) : 0]],
                'against' => ['average' => ['total' => $total > 0 ? round($goalsConceded / $total, 2) : 0]],
            ],
        ];
    }

    private function getRecentMatches(int $teamId, int $last = 10, string $teamName = ''): array
    {
        $query = \App\Models\FootballMatch::where(function ($q) use ($teamId, $teamName) {
            $q->where(fn ($s) => $s->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId));
            // Fallback par nom si le nom est fourni (matchs TheSportsDB sans IDs)
            if ($teamName !== '') {
                $q->orWhere(fn ($s) => $s->where('home_team', $teamName)->orWhere('away_team', $teamName));
            }
        })
            ->where('status', 'finished')
            ->whereNotNull('home_score')
            ->orderByDesc('match_date')
            ->limit($last)
            ->get();

        return $query->map(fn ($m) => [
            'fixture' => ['id' => $m->match_id, 'date' => $m->match_date],
            'teams' => [
                'home' => ['id' => $m->home_team_id ?? 0, 'name' => $m->home_team],
                'away' => ['id' => $m->away_team_id ?? 0, 'name' => $m->away_team],
            ],
            'goals' => ['home' => $m->home_score, 'away' => $m->away_score],
            'league' => ['id' => $m->competition_id],
            '_team_name' => $teamName,
        ])->toArray();
    }

    private function calculateFormPoints(array $fixtures, int $teamId): float
    {
        $points = 0;
        $fixtures = array_slice($fixtures, 0, 10);

        foreach ($fixtures as $fixture) {
            $teamName = $fixture['_team_name'] ?? '';
            $result = $this->getMatchResultForTeam($fixture, $teamId, $teamName);
            if ($result === 'win') {
                $points += 3;
            } elseif ($result === 'draw') {
                $points += 1;
            }
        }

        return $points;
    }

    private function getMatchResultForTeam(array $fixture, int $teamId, string $teamName = ''): string
    {
        $homeId = $fixture['teams']['home']['id'] ?? null;
        $awayId = $fixture['teams']['away']['id'] ?? null;
        $homeName = $fixture['teams']['home']['name'] ?? '';
        $awayName = $fixture['teams']['away']['name'] ?? '';
        $homeGoals = $fixture['goals']['home'] ?? null;
        $awayGoals = $fixture['goals']['away'] ?? null;

        if ($homeGoals === null || $awayGoals === null) {
            return 'unknown';
        }
        if ($homeGoals === $awayGoals) {
            return 'draw';
        }

        $homeWon = $homeGoals > $awayGoals;

        // Identification par ID (API-Football)
        if ($teamId !== 0 && $teamId === $homeId) {
            return $homeWon ? 'win' : 'loss';
        }
        if ($teamId !== 0 && $teamId === $awayId) {
            return $homeWon ? 'loss' : 'win';
        }

        // Fallback par nom (TheSportsDB — team_id = 0)
        if ($teamName !== '') {
            if ($homeName === $teamName) {
                return $homeWon ? 'win' : 'loss';
            }
            if ($awayName === $teamName) {
                return $homeWon ? 'loss' : 'win';
            }
        }

        return 'unknown';
    }

    private function calculateStreakBonus(array $homeFixtures, array $awayFixtures, int $homeTeamId, int $awayTeamId): float
    {
        $homeStreak = $this->calculateWinStreak($homeFixtures, $homeTeamId);
        $awayStreak = $this->calculateWinStreak($awayFixtures, $awayTeamId);

        $bonus = 0;
        if ($homeStreak >= 5) {
            $bonus += 4;
        } elseif ($homeStreak >= 3) {
            $bonus += 2;
        }
        if ($awayStreak >= 5) {
            $bonus -= 3;
        } elseif ($awayStreak >= 3) {
            $bonus -= 1.5;
        }

        return $bonus;
    }

    private function calculateWinStreak(array $fixtures, int $teamId): int
    {
        $streak = 0;
        foreach ($fixtures as $fixture) {
            $teamName = $fixture['_team_name'] ?? '';
            if ($this->getMatchResultForTeam($fixture, $teamId, $teamName) === 'win') {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    private function extractH2HResult(array $fixture, int $homeTeamId, string $homeName = ''): string
    {
        $homeId = $fixture['teams']['home']['id'] ?? null;
        $awayId = $fixture['teams']['away']['id'] ?? null;
        $homeFN = $fixture['teams']['home']['name'] ?? '';
        $homeGoals = $fixture['goals']['home'] ?? null;
        $awayGoals = $fixture['goals']['away'] ?? null;

        if ($homeGoals === null || $awayGoals === null || $homeGoals === $awayGoals) {
            return 'draw';
        }

        $fixtureHomeWon = $homeGoals > $awayGoals;

        $isHome = ($homeId && $homeId === $homeTeamId)
               || ($homeName !== '' && $homeFN === $homeName);

        if ($isHome) {
            return $fixtureHomeWon ? 'home' : 'away';
        }

        $isAway = ($awayId && $awayId === $homeTeamId)
               || ($homeName !== '' && ($fixture['teams']['away']['name'] ?? '') === $homeName);

        if ($isAway) {
            return $fixtureHomeWon ? 'away' : 'home';
        }

        return 'draw';
    }

    private function findTeamPosition(array $standings, int $teamId): ?int
    {
        foreach ($standings as $entry) {
            if (($entry['team']['id'] ?? null) === $teamId) {
                return $entry['rank'] ?? null;
            }
        }

        return null;
    }

    private function extractHomeWinRatio(?array $stats): float
    {
        if (! $stats) {
            return 0.5;
        }
        $played = $this->safeInt($stats['fixtures']['played']['home'] ?? 0);
        if ($played === 0) {
            return 0.5;
        }
        $wins = $this->safeInt($stats['fixtures']['wins']['home'] ?? 0);
        $draws = $this->safeInt($stats['fixtures']['draws']['home'] ?? 0);

        return ($wins * 3 + $draws) / ($played * 3);
    }

    private function extractAwayWinRatio(?array $stats): float
    {
        if (! $stats) {
            return 0.5;
        }
        $played = $this->safeInt($stats['fixtures']['played']['away'] ?? 0);
        if ($played === 0) {
            return 0.5;
        }
        $wins = $this->safeInt($stats['fixtures']['wins']['away'] ?? 0);
        $draws = $this->safeInt($stats['fixtures']['draws']['away'] ?? 0);

        return ($wins * 3 + $draws) / ($played * 3);
    }

    private function safeInt(mixed $value): int
    {
        if (is_array($value)) {
            $value = $value['total'] ?? $value['home'] ?? $value['away'] ?? 0;
        }

        return (int) $value;
    }

    private function extractGoalsFor(?array $stats): float
    {
        if (! $stats) {
            return 1.2;
        }

        return (float) ($stats['goals']['for']['average']['total'] ?? 1.2);
    }

    private function extractGoalsAgainst(?array $stats): float
    {
        if (! $stats) {
            return 1.2;
        }

        return (float) ($stats['goals']['against']['average']['total'] ?? 1.2);
    }

    private function extractShotsOnTarget(?array $stats): float
    {
        if (! $stats) {
            return 3.0;
        }
        $total = $stats['shots']['on']['total'] ?? 0;
        $played = $stats['fixtures']['played']['total'] ?? 1;

        return $played > 0 ? $total / $played : 3.0;
    }

    private function countMatchesInLastDays(array $fixtures, int $days): int
    {
        $cutoff = Carbon::now()->subDays($days);
        $count = 0;
        foreach ($fixtures as $fixture) {
            $date = $fixture['fixture']['date'] ?? null;
            if ($date && Carbon::parse($date)->gte($cutoff)) {
                $count++;
            }
        }

        return $count;
    }

    private function getWeatherData(string $city): ?array
    {
        $apiKey = config('services.openweathermap.key');
        if (! $apiKey) {
            return null;
        }

        return Cache::remember("weather:{$city}", 1800, function () use ($city, $apiKey) {
            try {
                $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => $city,
                    'appid' => $apiKey,
                    'units' => 'metric',
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    return [
                        'temp' => $data['main']['temp'] ?? 18,
                        'rain' => $data['rain']['1h'] ?? 0,
                        'wind' => $data['wind']['speed'] ?? 10,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Weather API error: '.$e->getMessage());
            }

            return null;
        });
    }

    // ==================== RÉGÉNÉRATION (scores déjà calculés) ====================

    /**
     * Recalcule bet_type/prediction/odds/analysis à partir de scores déjà stockés en DB.
     * Utilisé par la commande predictions:regenerate pour éviter de rappeler l'API-Football.
     *
     * Le fixture doit contenir :
     *   _cached_scores => tableau des 9 scores individuels
     *   _total_score   => float score total
     *   teams.home / teams.away => id + name
     *   league.id + league.season
     */
    public function determineBetTypePublic(array $fixture): array
    {
        $homeTeam = $fixture['teams']['home'] ?? [];
        $awayTeam = $fixture['teams']['away'] ?? [];
        $homeTeamId = $homeTeam['id'] ?? null;
        $awayTeamId = $awayTeam['id'] ?? null;
        $leagueId = $fixture['league']['id'] ?? null;
        $season = $fixture['league']['season'] ?? (int) Carbon::now()->year;

        $scores = $fixture['_cached_scores'] ?? [];
        $totalScore = (float) ($fixture['_total_score'] ?? array_sum($scores));

        // Récupérer les signaux supplémentaires (corners/cartons/buteurs)
        // Depuis le cache s'il existe, sinon 0 (pas d'appel API)
        $cornersAvg = $homeTeamId && $awayTeamId
            ? (float) \Illuminate\Support\Facades\Cache::get("corners_avg:{$homeTeamId}:{$awayTeamId}", 0.0)
            : 0.0;
        $cardsAvg = $homeTeamId && $awayTeamId
            ? (float) \Illuminate\Support\Facades\Cache::get("cards_avg:{$homeTeamId}:{$awayTeamId}", 0.0)
            : 0.0;
        $topScorers = $homeTeamId && $awayTeamId && $leagueId
            ? (array) \Illuminate\Support\Facades\Cache::get("top_scorers:{$homeTeamId}:{$awayTeamId}:{$leagueId}:{$season}", [])
            : [];

        $homeGoalsFor = $homeTeamId
            ? $this->extractGoalsFor($this->getTeamStats($homeTeamId, $leagueId, $season, $homeTeam['name'] ?? ''))
            : 1.2;
        $awayGoalsFor = $awayTeamId
            ? $this->extractGoalsFor($this->getTeamStats($awayTeamId, $leagueId, $season, $awayTeam['name'] ?? ''))
            : 1.2;

        $selected = $this->selectBestMarket(
            $scores, $totalScore, $homeTeam, $awayTeam,
            $cornersAvg, $cardsAvg, $homeGoalsFor, $awayGoalsFor
        );
        $analysis = $this->generateAnalysis($scores, $homeTeam, $awayTeam, $totalScore, $topScorers, $cornersAvg, $cardsAvg);

        return array_merge($selected, ['analysis' => $analysis]);
    }

    // ==================== COUPON IA — 3 VARIANTES (§14.4 CDC V2) ====================

    /**
     * Génère les 3 variantes du coupon quotidien.
     * Retourne un tableau indexé par variante : 'prudent', 'equilibre', 'audacieux'.
     * Si une variante ne peut pas être constituée (volume insuffisant),
     * son entrée est null — ne jamais forcer un pick sous le seuil (§14.4).
     *
     * Règles communes :
     *   - 4–5 picks par variante
     *   - Max 2 picks par compétition
     *   - Picks contradictoires sur un même match exclus
     *   - L'audacieux affiche un avertissement risque élevé
     */
    public function generateAllCouponVariants(array $fixtures): array
    {
        $variants = [
            'prudent' => ['min_confidence' => 75.0, 'odds_min' => 3.00,  'odds_max' => 6.00,  'is_premium' => false, 'label' => 'Prudent'],
            'equilibre' => ['min_confidence' => 65.0, 'odds_min' => 8.00,  'odds_max' => 15.00, 'is_premium' => true,  'label' => 'Équilibré'],
            'audacieux' => ['min_confidence' => 60.0, 'odds_min' => 15.00, 'odds_max' => 40.00, 'is_premium' => true,  'label' => 'Audacieux'],
        ];

        // Générer toutes les prédictions une seule fois
        $allPredictions = [];
        foreach ($fixtures as $fixture) {
            $pred = $this->generatePrediction($fixture);
            if ($pred['should_publish']) {
                $allPredictions[] = ['fixture' => $fixture, 'prediction' => $pred];
            }
        }

        $result = ['analyzed' => count($fixtures), 'generated_at' => Carbon::now()->toIso8601String()];

        foreach ($variants as $key => $config) {
            $result[$key] = $this->buildCouponVariant($allPredictions, $config, $key);
        }

        return $result;
    }

    private function buildCouponVariant(array $allPredictions, array $config, string $variantKey): ?array
    {
        $minConf = $config['min_confidence'];
        $oddsMin = $config['odds_min'];
        $oddsMax = $config['odds_max'];

        // Filtrer par confiance min
        $pool = array_filter($allPredictions, fn ($item) => $item['prediction']['confidence'] >= $minConf
        );

        if (count($pool) < 4) {
            return null;
        }

        // Trier par confiance × valeur marché décroissante
        usort($pool, fn ($a, $b) => ($b['prediction']['confidence'] * ($b['prediction']['market_value'] ?? 1))
            <=>
            ($a['prediction']['confidence'] * ($a['prediction']['market_value'] ?? 1))
        );

        // Sélection : max 2 picks/compétition, pas de contradiction sur même match
        $selected = [];
        $leagueCount = [];
        $usedMatchIds = [];

        foreach ($pool as $item) {
            if (count($selected) >= 5) {
                break;
            }

            $leagueId = $item['fixture']['league']['id'] ?? 'x';
            $matchId = $item['fixture']['fixture']['id'] ?? null;

            if (($leagueCount[$leagueId] ?? 0) >= 2) {
                continue;
            }
            if ($matchId && in_array($matchId, $usedMatchIds)) {
                continue;
            }

            $selected[] = $item;
            $leagueCount[$leagueId] = ($leagueCount[$leagueId] ?? 0) + 1;
            if ($matchId) {
                $usedMatchIds[] = $matchId;
            }
        }

        if (count($selected) < 4) {
            return null;
        }

        // Calcul cote totale et vérification bande cible
        $totalOdds = array_reduce($selected, fn ($c, $i) => $c * (float) $i['prediction']['odds'], 1.0);
        $avgConfidence = array_sum(array_map(fn ($i) => $i['prediction']['confidence'], $selected)) / count($selected);

        $picks = array_map(function (array $item) use ($config): array {
            $f = $item['fixture'];
            $p = $item['prediction'];

            return [
                'match' => ($f['teams']['home']['name'] ?? '?').' vs '.($f['teams']['away']['name'] ?? '?'),
                'home_team' => $f['teams']['home']['name'] ?? '?',
                'away_team' => $f['teams']['away']['name'] ?? '?',
                'league' => $f['league']['name'] ?? 'Unknown',
                'date' => $f['fixture']['date'] ?? null,
                'prediction' => $p['outcome'],
                'type' => $p['type'],
                'engine' => $p['engine'] ?? 'force',
                'odds' => $p['odds'],
                'confidence' => round($p['confidence'], 1),
                'stars' => $p['stars'],
                'is_premium' => $config['is_premium'],
            ];
        }, $selected);

        return [
            'success' => true,
            'variant' => $variantKey,
            'label' => $config['label'],
            'is_premium' => $config['is_premium'],
            'warning' => $variantKey === 'audacieux'
                ? 'Variante à risque élevé — cotes hautes, taux de réussite plus faible.'
                : null,
            'picks' => $picks,
            'picks_count' => count($picks),
            'total_odds' => round($totalOdds, 2),
            'total_odds_target' => ['min' => $oddsMin, 'max' => $oddsMax],
            'avg_confidence' => round($avgConfidence, 1),
            'stars' => $this->calculateStars($avgConfidence),
            'potential_gain_1000' => (int) round($totalOdds * 1000),
        ];
    }

    /**
     * Rétro-compatibilité : génère un seul coupon (variante équilibrée par défaut).
     */
    public function generateDailyCoupon(array $fixtures, int $minPicks = 4, int $maxPicks = 5, float $minConfidence = 60.0): array
    {
        $all = $this->generateAllCouponVariants($fixtures);
        $variant = $all['equilibre'] ?? $all['prudent'] ?? null;

        if (! $variant) {
            return [
                'success' => false,
                'message' => 'Aucune variante de coupon générée — volume insuffisant.',
                'analyzed' => $all['analyzed'] ?? 0,
                'qualified' => 0,
            ];
        }

        return array_merge($variant, ['analyzed' => $all['analyzed'] ?? 0, 'qualified' => $variant['picks_count']]);
    }

    private function getDefaultPrediction(): array
    {
        return [
            'type' => 'Over/Under',
            'outcome' => 'Over 2.5',
            'confidence' => 52,
            'stars' => 1,
            'odds' => 1.75,
            'reasoning' => 'Donnees insuffisantes pour une analyse complete.',
            'scores' => array_fill_keys(array_keys(self::WEIGHTS), 0),
            'is_premium' => false,
            'should_publish' => false,
        ];
    }
}
