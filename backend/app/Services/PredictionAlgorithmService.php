<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

    const WEIGHTS = [
        'form'      => 25,
        'h2h'       => 20,
        'home_away' => 15,
        'league'    => 12,
        'goals'     => 10,
        'time'      => 8,
        'weather'   => 5,
        'shots'     => 3,
        'physical'  => 2,
    ];

    const CONFIDENCE_THRESHOLDS = [
        'very_high'   => 85,
        'high'        => 70,
        'medium'      => 60,
        'low'         => 50,
        'min_publish' => 50,
    ];

    public function __construct(FootballApiService $footballApi)
    {
        $this->footballApi = $footballApi;
    }

    /**
     * Generer une prediction complete pour un match
     *
     * @param array $fixture Fixture au format API-Football
     */
    public function generatePrediction(array $fixture): array
    {
        $homeTeam   = $fixture['teams']['home'] ?? [];
        $awayTeam   = $fixture['teams']['away'] ?? [];
        $homeTeamId = $homeTeam['id'] ?? null;
        $awayTeamId = $awayTeam['id'] ?? null;
        $leagueId   = $fixture['league']['id'] ?? null;
        $season     = $fixture['league']['season'] ?? (int) Carbon::now()->year;

        if (!$homeTeamId || !$awayTeamId) {
            return $this->getDefaultPrediction();
        }

        $scores = [
            'form'      => $this->calculateFormScore($homeTeamId, $awayTeamId, $leagueId, $season),
            'h2h'       => $this->calculateH2HScore($homeTeamId, $awayTeamId),
            'home_away' => $this->calculateHomeAwayScore($homeTeamId, $awayTeamId, $leagueId, $season),
            'league'    => $this->calculateLeagueScore($homeTeamId, $awayTeamId, $leagueId, $season),
            'goals'     => $this->calculateGoalsScore($homeTeamId, $awayTeamId, $leagueId, $season),
            'time'      => $this->calculateTimeScore($fixture['fixture'] ?? []),
            'weather'   => $this->calculateWeatherScore($fixture['fixture']['venue'] ?? []),
            'shots'     => $this->calculateShotsScore($homeTeamId, $awayTeamId, $leagueId, $season),
            'physical'  => $this->calculatePhysicalScore($homeTeamId, $awayTeamId),
        ];

        $totalScore = array_sum($scores);

        // Signaux supplémentaires (pas dans le score total, mais utilisés pour le choix du marché)
        $cornersAvg  = $this->getAverageCornersPerMatch($homeTeamId, $awayTeamId);
        $cardsAvg    = $this->getAverageCardsPerMatch($homeTeamId, $awayTeamId);
        $topScorers  = $this->getTopScorersForMatch($homeTeamId, $awayTeamId, $leagueId, $season);

        $prediction = $this->determineBetType($scores, $totalScore, $homeTeam, $awayTeam, $cornersAvg, $cardsAvg);
        $stars       = $this->calculateStars($totalScore);
        $analysis    = $this->generateAnalysis($scores, $homeTeam, $awayTeam, $totalScore, $topScorers, $cornersAvg, $cardsAvg);

        return [
            'type'           => $prediction['type'],
            'outcome'        => $prediction['outcome'],
            'confidence'     => round($totalScore, 2),
            'stars'          => $stars,
            'odds'           => $prediction['odds'],
            'reasoning'      => $analysis,
            'scores'         => array_map(fn($s) => round($s, 2), $scores),
            'is_premium'     => $stars >= 3,
            'should_publish' => $totalScore >= self::CONFIDENCE_THRESHOLDS['min_publish'],
            'top_scorers'    => $topScorers,
            'corners_avg'    => $cornersAvg,
            'cards_avg'      => $cardsAvg,
        ];
    }

    // ==================== CRITERE 1: FORME RECENTE (25 pts) ====================

    private function calculateFormScore(int $homeTeamId, int $awayTeamId, ?int $leagueId, int $season): float
    {
        $maxScore = self::WEIGHTS['form'];

        $homeMatches = $this->getRecentMatches($homeTeamId, 10);
        $awayMatches = $this->getRecentMatches($awayTeamId, 10);

        $homeFormPoints = $this->calculateFormPoints($homeMatches, $homeTeamId);
        $awayFormPoints = $this->calculateFormPoints($awayMatches, $awayTeamId);

        $maxFormPoints = 30; // 10 wins × 3pts
        $homeRatio = min($homeFormPoints / $maxFormPoints, 1);
        $awayRatio = min($awayFormPoints / $maxFormPoints, 1);

        $formDiff  = $homeRatio - $awayRatio;
        $baseScore = ($maxScore / 2) + ($formDiff * ($maxScore / 2));

        $streakBonus = $this->calculateStreakBonus($homeMatches, $awayMatches, $homeTeamId, $awayTeamId);

        return min(max($baseScore + $streakBonus, 0), $maxScore);
    }

    // ==================== CRITERE 2: H2H (20 pts) ====================

    private function calculateH2HScore(int $homeTeamId, int $awayTeamId): float
    {
        $maxScore = self::WEIGHTS['h2h'];

        $data = Cache::remember("h2h:{$homeTeamId}:{$awayTeamId}", 3600, function () use ($homeTeamId, $awayTeamId) {
            return $this->footballApi->getHeadToHead($homeTeamId, $awayTeamId, 8);
        });

        $meetings = $data['response'] ?? [];

        if (empty($meetings)) {
            return $maxScore * 0.50; // neutre si pas de données H2H
        }

        $meetings = array_slice($meetings, 0, 8);
        $homeWins = 0;
        $awayWins = 0;
        $draws    = 0;
        $totalWeight = 0;

        foreach ($meetings as $index => $meeting) {
            $weight = 8 - $index;
            $totalWeight += $weight;
            $result = $this->extractH2HResult($meeting, $homeTeamId);

            if ($result === 'home')      $homeWins += $weight;
            elseif ($result === 'away')  $awayWins += $weight;
            else                         $draws    += $weight;
        }

        if ($totalWeight === 0) {
            return $maxScore * 0.50;
        }

        // Score centré sur 0.5 : reflète réellement l'historique des deux équipes
        $homeRatio = ($homeWins + ($draws * 0.5)) / $totalWeight;

        return min(max($homeRatio * $maxScore, 0), $maxScore);
    }

    // ==================== CRITERE 3: DOMICILE/EXTERIEUR (15 pts) ====================

    private function calculateHomeAwayScore(int $homeTeamId, int $awayTeamId, ?int $leagueId, int $season): float
    {
        $maxScore = self::WEIGHTS['home_away'];

        if (!$leagueId) return $maxScore * 0.50; // neutre si pas de ligue

        $homeStats = $this->getTeamStats($homeTeamId, $leagueId, $season);
        $awayStats = $this->getTeamStats($awayTeamId, $leagueId, $season);

        $homeHomeRatio = $this->extractHomeWinRatio($homeStats);
        $awayAwayRatio = $this->extractAwayWinRatio($awayStats);

        // Comparaison directe : performance dom. de l'équipe locale vs perf. ext. de l'équipe visiteuse
        // Suppression du +0.10 artificiel qui favorisait toujours le domicile
        $diff      = $homeHomeRatio - $awayAwayRatio;
        $baseScore = ($maxScore / 2) + ($diff * ($maxScore / 2));

        return min(max($baseScore, 0), $maxScore);
    }

    // ==================== CRITERE 4: CLASSEMENT (12 pts) ====================

    private function calculateLeagueScore(int $homeTeamId, int $awayTeamId, ?int $leagueId, int $season): float
    {
        $maxScore = self::WEIGHTS['league'];

        if (!$leagueId) return $maxScore * 0.5;

        $data = Cache::remember("standings:{$leagueId}:{$season}", 3600, function () use ($leagueId, $season) {
            return $this->footballApi->getStandings($leagueId, $season);
        });

        $standings = $data['response'][0]['league']['standings'][0] ?? [];

        if (empty($standings)) return $maxScore * 0.5;

        $homePosition = $this->findTeamPosition($standings, $homeTeamId);
        $awayPosition = $this->findTeamPosition($standings, $awayTeamId);

        if ($homePosition === null || $awayPosition === null) return $maxScore * 0.5;

        $totalTeams     = count($standings) ?: 20;
        $homeNormalized = 1 - (($homePosition - 1) / $totalTeams);
        $awayNormalized = 1 - (($awayPosition - 1) / $totalTeams);
        $positionDiff   = $homeNormalized - $awayNormalized;
        $baseScore      = ($maxScore / 2) + ($positionDiff * ($maxScore / 2));

        if (abs($awayPosition - $homePosition) > 10) {
            $baseScore += ($positionDiff > 0 ? 2 : -2);
        }

        return min(max($baseScore, 0), $maxScore);
    }

    // ==================== CRITERE 5: BUTS (10 pts) ====================

    private function calculateGoalsScore(int $homeTeamId, int $awayTeamId, ?int $leagueId, int $season): float
    {
        $maxScore = self::WEIGHTS['goals'];

        if (!$leagueId) return $maxScore * 0.5;

        $homeStats = $this->getTeamStats($homeTeamId, $leagueId, $season);
        $awayStats = $this->getTeamStats($awayTeamId, $leagueId, $season);

        $homeGoalsFor     = $this->extractGoalsFor($homeStats);
        $awayGoalsFor     = $this->extractGoalsFor($awayStats);
        $homeGoalsAgainst = $this->extractGoalsAgainst($homeStats);
        $awayGoalsAgainst = $this->extractGoalsAgainst($awayStats);

        $homeOffense = min($homeGoalsFor / 2.5, 1);
        $awayDefense = 1 - min($awayGoalsAgainst / 2.0, 1);
        $homeDefense = 1 - min($homeGoalsAgainst / 2.0, 1);
        $awayOffense = min($awayGoalsFor / 2.5, 1);

        $homeAdvantage = ($homeOffense + $homeDefense) / 2;
        $awayAdvantage = ($awayOffense + $awayDefense) / 2;

        $diff      = $homeAdvantage - $awayAdvantage;
        $baseScore = ($maxScore / 2) + ($diff * ($maxScore / 2));

        return min(max($baseScore, 0), $maxScore);
    }

    // ==================== CRITERE 6: HORAIRE (8 pts) ====================

    private function calculateTimeScore(array $fixtureInfo): float
    {
        $maxScore = self::WEIGHTS['time'];
        $date     = $fixtureInfo['date'] ?? null;

        if (!$date) return $maxScore * 0.5;

        try {
            $matchTime = Carbon::parse($date);
            $hour      = $matchTime->hour;
            $dow       = $matchTime->dayOfWeek;

            if ($hour >= 19 && $hour <= 22)     $timeBonus = 1.0;
            elseif ($hour >= 14 && $hour < 19)  $timeBonus = 0.75;
            elseif ($hour >= 10 && $hour < 14)  $timeBonus = 0.5;
            else                                 $timeBonus = 0.4;

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
        $city     = $venue['city'] ?? null;

        if (!$city) return $maxScore * 0.6;

        $weather = $this->getWeatherData($city);

        if (!$weather) return $maxScore * 0.6;

        $score = $maxScore;
        $temp  = $weather['temp'] ?? 18;
        $rain  = $weather['rain'] ?? 0;
        $wind  = $weather['wind'] ?? 0;

        if ($temp < 5 || $temp > 30)      $score -= 2;
        elseif ($temp < 10 || $temp > 25) $score -= 1;

        if ($rain > 5)     $score -= 2;
        elseif ($rain > 2) $score -= 1;

        if ($wind > 40)     $score -= 1.5;
        elseif ($wind > 25) $score -= 0.5;

        return max($score, 0);
    }

    // ==================== CRITERE 8: TIRS CADRES (3 pts) ====================

    private function calculateShotsScore(int $homeTeamId, int $awayTeamId, ?int $leagueId, int $season): float
    {
        $maxScore = self::WEIGHTS['shots'];

        if (!$leagueId) return $maxScore * 0.5;

        $homeStats = $this->getTeamStats($homeTeamId, $leagueId, $season);
        $awayStats = $this->getTeamStats($awayTeamId, $leagueId, $season);

        $homeShotsOnTarget = $this->extractShotsOnTarget($homeStats);
        $awayShotsOnTarget = $this->extractShotsOnTarget($awayStats);

        $homeRatio = min($homeShotsOnTarget / 5, 1);
        $awayRatio = min($awayShotsOnTarget / 5, 1);

        $diff      = $homeRatio - $awayRatio;
        $baseScore = ($maxScore / 2) + ($diff * ($maxScore / 2));

        return min(max($baseScore, 0), $maxScore);
    }

    // ==================== CRITERE 9: FORME PHYSIQUE (2 pts) ====================

    private function calculatePhysicalScore(int $homeTeamId, int $awayTeamId): float
    {
        $maxScore = self::WEIGHTS['physical'];

        $homeMatches = $this->getRecentMatches($homeTeamId, 10);
        $awayMatches = $this->getRecentMatches($awayTeamId, 10);

        $homeRecentCount = $this->countMatchesInLastDays($homeMatches, 7);
        $awayRecentCount = $this->countMatchesInLastDays($awayMatches, 7);

        $homeFatigue   = min($homeRecentCount / 3, 1);
        $awayFatigue   = min($awayRecentCount / 3, 1);
        $fatigueDiff   = $awayFatigue - $homeFatigue;
        $baseScore     = ($maxScore / 2) + ($fatigueDiff * ($maxScore / 2));

        return min(max($baseScore, 0), $maxScore);
    }

    // ==================== BET TYPE & ANALYSE ====================

    private function determineBetType(
        array $scores,
        float $totalScore,
        array $homeTeam,
        array $awayTeam,
        float $cornersAvg,
        float $cardsAvg
    ): array {
        // Ratios normalisés 0–1, 0.5 = neutre, >0.5 = avantage domicile
        $formRatio     = $scores['form']      / self::WEIGHTS['form'];
        $h2hRatio      = $scores['h2h']       / self::WEIGHTS['h2h'];
        $homeAwayRatio = $scores['home_away'] / self::WEIGHTS['home_away'];
        $goalsRatio    = $scores['goals']     / self::WEIGHTS['goals'];
        $leagueRatio   = $scores['league']    / self::WEIGHTS['league'];

        // Avantage composite domicile (3 critères pondérés)
        $homeAdv = ($formRatio * 0.4) + ($h2hRatio * 0.35) + ($homeAwayRatio * 0.25);
        $awayAdv = 1 - $homeAdv;

        // ── 1. DOMICILE NETTEMENT SUPÉRIEUR (avantage > 0.62) ────────────────
        if ($homeAdv >= 0.70) {
            return ['type' => '1X2', 'outcome' => '1', 'odds' => round(1.35 + (mt_rand(0, 25) / 100), 2)];
        }
        if ($homeAdv >= 0.62) {
            return ['type' => '1X2', 'outcome' => '1', 'odds' => round(1.55 + (mt_rand(0, 35) / 100), 2)];
        }

        // ── 2. EXTÉRIEUR NETTEMENT SUPÉRIEUR (avantage > 0.62) ───────────────
        if ($awayAdv >= 0.70) {
            return ['type' => '1X2', 'outcome' => '2', 'odds' => round(1.75 + (mt_rand(0, 45) / 100), 2)];
        }
        if ($awayAdv >= 0.62) {
            return ['type' => '1X2', 'outcome' => '2', 'odds' => round(2.00 + (mt_rand(0, 60) / 100), 2)];
        }

        // ── 3. CORNERS — Over/Under si signal fort ────────────────────────────
        // (priorité sur les buts car marché moins exploité par les parieurs)
        if ($cornersAvg > 0 && $cornersAvg >= 10.5) {
            return ['type' => 'Corners', 'outcome' => 'Over 9.5', 'odds' => round(1.80 + (mt_rand(0, 30) / 100), 2)];
        }
        if ($cornersAvg > 0 && $cornersAvg <= 7.5) {
            return ['type' => 'Corners', 'outcome' => 'Under 8.5', 'odds' => round(1.85 + (mt_rand(0, 30) / 100), 2)];
        }

        // ── 4. CARTONS — Over/Under si les deux équipes sont agressives ──────
        if ($cardsAvg > 0 && $cardsAvg >= 4.5) {
            return ['type' => 'Cartons', 'outcome' => 'Over 3.5', 'odds' => round(1.75 + (mt_rand(0, 35) / 100), 2)];
        }

        // ── 5. BUTS — match offensif (les deux équipes marquent beaucoup) ────
        if ($goalsRatio >= 0.65) {
            return ['type' => 'BTTS', 'outcome' => 'Oui', 'odds' => round(1.72 + (mt_rand(0, 30) / 100), 2)];
        }
        if ($goalsRatio >= 0.55) {
            return ['type' => 'Over/Under', 'outcome' => 'Over 2.5', 'odds' => round(1.68 + (mt_rand(0, 35) / 100), 2)];
        }

        // ── 6. BUTS — match défensif ──────────────────────────────────────────
        if ($goalsRatio <= 0.35) {
            return ['type' => 'Over/Under', 'outcome' => 'Under 2.5', 'odds' => round(1.75 + (mt_rand(0, 30) / 100), 2)];
        }

        // ── 7. DOMICILE LÉGÈREMENT FAVORI ─────────────────────────────────────
        if ($homeAdv >= 0.53) {
            return ['type' => 'Double Chance', 'outcome' => '1X', 'odds' => round(1.22 + (mt_rand(0, 25) / 100), 2)];
        }

        // ── 8. EXTÉRIEUR LÉGÈREMENT FAVORI ────────────────────────────────────
        if ($awayAdv >= 0.53) {
            return ['type' => 'Double Chance', 'outcome' => 'X2', 'odds' => round(1.32 + (mt_rand(0, 30) / 100), 2)];
        }

        // ── 9. MATCH TRÈS ÉQUILIBRÉ → classement comme tie-breaker ───────────
        if ($leagueRatio >= 0.55) {
            return ['type' => 'Double Chance', 'outcome' => '1X', 'odds' => round(1.28 + (mt_rand(0, 25) / 100), 2)];
        }

        // ── 10. DÉFAUT — BTTS neutre ──────────────────────────────────────────
        return ['type' => 'BTTS', 'outcome' => 'Oui', 'odds' => round(1.78 + (mt_rand(0, 30) / 100), 2)];
    }

    // ── Corners : moyenne corners/match des 10 derniers matchs des 2 équipes ──

    private function getAverageCornersPerMatch(int $homeTeamId, int $awayTeamId): float
    {
        $cacheKey = "corners_avg:{$homeTeamId}:{$awayTeamId}";
        return Cache::remember($cacheKey, 7200, function () use ($homeTeamId, $awayTeamId) {
            $homeCorners = $this->extractAverageCorners($this->getRecentMatches($homeTeamId, 10), $homeTeamId);
            $awayCorners = $this->extractAverageCorners($this->getRecentMatches($awayTeamId, 10), $awayTeamId);
            if ($homeCorners === null || $awayCorners === null) return 0.0;
            return round($homeCorners + $awayCorners, 1);
        });
    }

    private function extractAverageCorners(array $fixtures, int $teamId): ?float
    {
        $total  = 0;
        $count  = 0;
        foreach (array_slice($fixtures, 0, 10) as $fixture) {
            $stats = $fixture['statistics'] ?? null;
            if (!$stats) continue;
            foreach ($stats as $teamStats) {
                if (($teamStats['team']['id'] ?? null) !== $teamId) continue;
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

    private function getAverageCardsPerMatch(int $homeTeamId, int $awayTeamId): float
    {
        $cacheKey = "cards_avg:{$homeTeamId}:{$awayTeamId}";
        return Cache::remember($cacheKey, 7200, function () use ($homeTeamId, $awayTeamId) {
            $homeCards = $this->extractAverageCards($this->getRecentMatches($homeTeamId, 10), $homeTeamId);
            $awayCards = $this->extractAverageCards($this->getRecentMatches($awayTeamId, 10), $awayTeamId);
            if ($homeCards === null || $awayCards === null) return 0.0;
            return round($homeCards + $awayCards, 1);
        });
    }

    private function extractAverageCards(array $fixtures, int $teamId): ?float
    {
        $total = 0;
        $count = 0;
        foreach (array_slice($fixtures, 0, 10) as $fixture) {
            $events = $fixture['events'] ?? null;
            if (!is_array($events)) continue;
            $matchCards = 0;
            foreach ($events as $event) {
                if (($event['team']['id'] ?? null) !== $teamId) continue;
                $type   = $event['type'] ?? '';
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
        if (!$leagueId) return [];

        $cacheKey = "topscorers:{$leagueId}:{$season}";
        $data     = Cache::remember($cacheKey, 86400, function () use ($leagueId, $season) {
            try {
                return $this->footballApi->makeRequest('/players/topscorers', [
                    'league' => $leagueId,
                    'season' => $season,
                ], 86400);
            } catch (\Throwable $e) {
                return null;
            }
        });

        if (!$data) return [];

        $scorers = [];
        foreach ($data['response'] ?? [] as $entry) {
            $teamId = $entry['statistics'][0]['team']['id'] ?? null;
            if (!in_array($teamId, [$homeTeamId, $awayTeamId])) continue;

            $goals   = $entry['statistics'][0]['goals']['total'] ?? 0;
            if (!$goals) continue;

            $scorers[] = [
                'name'   => $entry['player']['name'] ?? 'Inconnu',
                'team'   => $entry['statistics'][0]['team']['name'] ?? '',
                'goals'  => (int) $goals,
                'side'   => $teamId === $homeTeamId ? 'home' : 'away',
            ];

            if (count($scorers) >= 4) break;
        }

        return $scorers;
    }

    private function calculateStars(float $totalScore): int
    {
        if ($totalScore >= self::CONFIDENCE_THRESHOLDS['very_high']) return 4;
        if ($totalScore >= self::CONFIDENCE_THRESHOLDS['high'])      return 3;
        if ($totalScore >= self::CONFIDENCE_THRESHOLDS['medium'])    return 2;
        return 1;
    }

    private function generateAnalysis(
        array  $scores,
        array  $homeTeam,
        array  $awayTeam,
        float  $totalScore,
        array  $topScorers  = [],
        float  $cornersAvg  = 0.0,
        float  $cardsAvg    = 0.0
    ): string {
        $home     = $homeTeam['name'] ?? 'Domicile';
        $away     = $awayTeam['name'] ?? 'Exterieur';
        $analysis = [];

        if ($scores['form'] >= self::WEIGHTS['form'] * 0.7)
            $analysis[] = "{$home} affiche une excellente forme recente";
        elseif ($scores['form'] <= self::WEIGHTS['form'] * 0.3)
            $analysis[] = "{$away} est en meilleure forme actuellement";

        if ($scores['h2h'] >= self::WEIGHTS['h2h'] * 0.7)
            $analysis[] = "L'historique des confrontations favorise {$home}";
        elseif ($scores['h2h'] <= self::WEIGHTS['h2h'] * 0.3)
            $analysis[] = "{$away} a l'avantage dans les confrontations directes";

        if ($scores['league'] >= self::WEIGHTS['league'] * 0.7)
            $analysis[] = "{$home} est mieux classe au championnat";

        if ($scores['goals'] >= self::WEIGHTS['goals'] * 0.7)
            $analysis[] = "Les statistiques de buts favorisent {$home}";
        elseif ($scores['goals'] <= self::WEIGHTS['goals'] * 0.3)
            $analysis[] = "{$away} presente de meilleures statistiques offensives";

        if ($scores['time'] >= self::WEIGHTS['time'] * 0.8)
            $analysis[] = "Match en prime time avec conditions optimales";

        if ($cornersAvg >= 10.5)
            $analysis[] = "Moyenne elevee de corners ({$cornersAvg}/match) — attendez-vous a beaucoup d'actions sur les ailes";
        elseif ($cornersAvg > 0 && $cornersAvg <= 7.5)
            $analysis[] = "Peu de corners en moyenne ({$cornersAvg}/match) — jeu central predominant";

        if ($cardsAvg >= 4.5)
            $analysis[] = "Match physique attendu : moyenne de {$cardsAvg} cartons par match";
        elseif ($cardsAvg > 0 && $cardsAvg <= 2.0)
            $analysis[] = "Match propre attendu : peu de cartons en moyenne ({$cardsAvg}/match)";

        if (!empty($topScorers)) {
            $names = array_map(fn($s) => $s['name'] . ' (' . $s['goals'] . ' buts)', array_slice($topScorers, 0, 2));
            $analysis[] = "Buteurs a surveiller : " . implode(', ', $names);
        }

        if (empty($analysis))
            $analysis[] = "Match equilibre, aucun avantage clair d'un cote";

        $confidenceText = $totalScore >= 80 ? "Confiance elevee" :
                         ($totalScore >= 65 ? "Confiance moyenne" : "Confiance moderee");

        return $confidenceText . " ({$totalScore}/100). " . implode(". ", $analysis) . ".";
    }

    // ==================== METHODES UTILITAIRES ====================

    private function getTeamStats(int $teamId, int $leagueId, int $season): ?array
    {
        return Cache::remember("team_stats:{$teamId}:{$leagueId}:{$season}", 3600, function () use ($teamId, $leagueId, $season) {
            $data = $this->footballApi->getTeamStatistics($teamId, $season, $leagueId);
            return $data['response'] ?? null;
        });
    }

    private function getRecentMatches(int $teamId, int $last = 10): array
    {
        $data = Cache::remember("team_recent:{$teamId}:{$last}", 1800, function () use ($teamId, $last) {
            return $this->footballApi->getTeamRecentMatches($teamId, $last);
        });
        return $data['response'] ?? [];
    }

    private function calculateFormPoints(array $fixtures, int $teamId): float
    {
        $points   = 0;
        $fixtures = array_slice($fixtures, 0, 10);

        foreach ($fixtures as $fixture) {
            $result = $this->getMatchResultForTeam($fixture, $teamId);
            if ($result === 'win')  $points += 3;
            elseif ($result === 'draw') $points += 1;
        }

        return $points;
    }

    private function getMatchResultForTeam(array $fixture, int $teamId): string
    {
        $homeId    = $fixture['teams']['home']['id'] ?? null;
        $awayId    = $fixture['teams']['away']['id'] ?? null;
        $homeGoals = $fixture['goals']['home'] ?? null;
        $awayGoals = $fixture['goals']['away'] ?? null;

        if ($homeGoals === null || $awayGoals === null) return 'unknown';
        if ($homeGoals === $awayGoals) return 'draw';

        $homeWon = $homeGoals > $awayGoals;

        if ($teamId === $homeId) return $homeWon ? 'win' : 'loss';
        if ($teamId === $awayId) return $homeWon ? 'loss' : 'win';

        return 'unknown';
    }

    private function calculateStreakBonus(array $homeFixtures, array $awayFixtures, int $homeTeamId, int $awayTeamId): float
    {
        $homeStreak = $this->calculateWinStreak($homeFixtures, $homeTeamId);
        $awayStreak = $this->calculateWinStreak($awayFixtures, $awayTeamId);

        $bonus = 0;
        if ($homeStreak >= 5)      $bonus += 4;
        elseif ($homeStreak >= 3)  $bonus += 2;
        if ($awayStreak >= 5)      $bonus -= 3;
        elseif ($awayStreak >= 3)  $bonus -= 1.5;

        return $bonus;
    }

    private function calculateWinStreak(array $fixtures, int $teamId): int
    {
        $streak = 0;
        foreach ($fixtures as $fixture) {
            if ($this->getMatchResultForTeam($fixture, $teamId) === 'win') {
                $streak++;
            } else {
                break;
            }
        }
        return $streak;
    }

    private function extractH2HResult(array $fixture, int $homeTeamId): string
    {
        $homeId    = $fixture['teams']['home']['id'] ?? null;
        $awayId    = $fixture['teams']['away']['id'] ?? null;
        $homeGoals = $fixture['goals']['home'] ?? null;
        $awayGoals = $fixture['goals']['away'] ?? null;

        if ($homeGoals === null || $awayGoals === null || $homeGoals === $awayGoals) return 'draw';

        $fixtureHomeWon = $homeGoals > $awayGoals;

        // Est-ce que l'equipe "home" dans ce H2H match est notre equipe home locale ?
        if ($homeId === $homeTeamId) {
            return $fixtureHomeWon ? 'home' : 'away';
        }
        if ($awayId === $homeTeamId) {
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
        if (!$stats) return 0.5;
        $played = $stats['fixtures']['played']['home'] ?? 0;
        if ($played === 0) return 0.5;
        $wins  = $stats['fixtures']['wins']['home'] ?? 0;
        $draws = $stats['fixtures']['draws']['home'] ?? 0;
        return ($wins * 3 + $draws) / ($played * 3);
    }

    private function extractAwayWinRatio(?array $stats): float
    {
        if (!$stats) return 0.5;
        $played = $stats['fixtures']['played']['away'] ?? 0;
        if ($played === 0) return 0.5;
        $wins  = $stats['fixtures']['wins']['away'] ?? 0;
        $draws = $stats['fixtures']['draws']['away'] ?? 0;
        return ($wins * 3 + $draws) / ($played * 3);
    }

    private function extractGoalsFor(?array $stats): float
    {
        if (!$stats) return 1.2;
        return (float) ($stats['goals']['for']['average']['total'] ?? 1.2);
    }

    private function extractGoalsAgainst(?array $stats): float
    {
        if (!$stats) return 1.2;
        return (float) ($stats['goals']['against']['average']['total'] ?? 1.2);
    }

    private function extractShotsOnTarget(?array $stats): float
    {
        if (!$stats) return 3.0;
        $total  = $stats['shots']['on']['total'] ?? 0;
        $played = $stats['fixtures']['played']['total'] ?? 1;
        return $played > 0 ? $total / $played : 3.0;
    }

    private function countMatchesInLastDays(array $fixtures, int $days): int
    {
        $cutoff = Carbon::now()->subDays($days);
        $count  = 0;
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
        if (!$apiKey) return null;

        return Cache::remember("weather:{$city}", 1800, function () use ($city, $apiKey) {
            try {
                $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                    'q'     => $city,
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
                Log::warning("Weather API error: " . $e->getMessage());
            }
            return null;
        });
    }

    // ==================== COUPON IA ====================

    /**
     * Generer un coupon combiné à partir d'une liste de fixtures.
     *
     * Logique :
     * - Génère une prédiction pour chaque fixture
     * - Filtre celles avec confidence >= $minConfidence
     * - Sélectionne les meilleures en privilégiant 1 match par ligue
     * - Calcule la cote totale et le gain potentiel pour une mise de 1 000 FCFA
     *
     * @param array $fixtures      Liste de fixtures au format API-Football
     * @param int   $minPicks      Nombre minimum de sélections requises
     * @param int   $maxPicks      Nombre maximum de sélections dans le coupon
     * @param float $minConfidence Score minimum de confiance pour être inclus
     */
    public function generateDailyCoupon(
        array $fixtures,
        int   $minPicks      = 4,
        int   $maxPicks      = 5,
        float $minConfidence = 60.0
    ): array {
        $candidates = [];

        foreach ($fixtures as $fixture) {
            $prediction = $this->generatePrediction($fixture);

            if ($prediction['confidence'] >= $minConfidence && $prediction['should_publish']) {
                $candidates[] = [
                    'fixture'    => $fixture,
                    'prediction' => $prediction,
                ];
            }
        }

        if (count($candidates) < $minPicks) {
            return [
                'success'   => false,
                'message'   => 'Pas assez de matchs avec une confiance suffisante (' . count($candidates) . '/' . $minPicks . ' requis)',
                'analyzed'  => count($fixtures),
                'qualified' => count($candidates),
            ];
        }

        // Trier par confiance décroissante
        usort($candidates, fn($a, $b) => $b['prediction']['confidence'] <=> $a['prediction']['confidence']);

        // Sélectionner en prioritisant 1 match par ligue
        $selected    = [];
        $usedLeagues = [];

        foreach ($candidates as $c) {
            if (count($selected) >= $maxPicks) break;
            $leagueId = $c['fixture']['league']['id'] ?? 'unknown';
            if (!in_array($leagueId, $usedLeagues)) {
                $selected[]    = $c;
                $usedLeagues[] = $leagueId;
            }
        }

        // Compléter si on n'a pas encore atteint $minPicks
        if (count($selected) < $minPicks) {
            foreach ($candidates as $c) {
                if (count($selected) >= $minPicks) break;
                $alreadyIn = array_filter($selected, fn($s) => $s === $c);
                if (empty($alreadyIn)) {
                    $selected[] = $c;
                }
            }
        }

        // Calculs coupon
        $totalOdds     = array_reduce($selected, fn($carry, $p) => $carry * (float) $p['prediction']['odds'], 1.0);
        $avgConfidence = array_sum(array_map(fn($p) => $p['prediction']['confidence'], $selected)) / count($selected);
        $couponStars   = $this->calculateStars($avgConfidence);

        $picks = array_map(function (array $item): array {
            $f = $item['fixture'];
            $p = $item['prediction'];
            return [
                'match'       => ($f['teams']['home']['name'] ?? '?') . ' vs ' . ($f['teams']['away']['name'] ?? '?'),
                'league'      => $f['league']['name'] ?? 'Unknown',
                'date'        => $f['fixture']['date'] ?? null,
                'prediction'  => $p['outcome'],
                'type'        => $p['type'],
                'odds'        => $p['odds'],
                'confidence'  => round($p['confidence'], 1),
                'stars'       => $p['stars'],
                'is_premium'  => $p['is_premium'],
            ];
        }, $selected);

        return [
            'success'            => true,
            'picks'              => $picks,
            'matches_count'      => count($selected),
            'total_odds'         => round($totalOdds, 2),
            'avg_confidence'     => round($avgConfidence, 1),
            'stars'              => $couponStars,
            'potential_gain_1000'=> (int) round($totalOdds * 1000),
            'analyzed'           => count($fixtures),
            'qualified'          => count($candidates),
            'generated_at'       => Carbon::now()->toIso8601String(),
        ];
    }

    private function getDefaultPrediction(): array
    {
        return [
            'type'           => 'Over/Under',
            'outcome'        => 'Over 2.5',
            'confidence'     => 52,
            'stars'          => 1,
            'odds'           => 1.75,
            'reasoning'      => 'Donnees insuffisantes pour une analyse complete.',
            'scores'         => array_fill_keys(array_keys(self::WEIGHTS), 0),
            'is_premium'     => false,
            'should_publish' => false,
        ];
    }
}
