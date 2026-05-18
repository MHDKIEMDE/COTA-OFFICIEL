# 🧮 SPRINT 03 - Algorithme v3.0 (LE COEUR DU PRODUIT)

> **Phase** : 1 - MVP | **Durée** : 2 semaines (60h) | **Priorité** : 🔴🔴🔴 CRITIQUE ABSOLUE
> **Prérequis** : Sprint 01-02 terminés, données API-Football accessibles

---

## 🎯 OBJECTIF DU SPRINT

Implémenter l'**algorithme de scoring v3.0** capable d'atteindre **55-60%+ de win rate** :

- ✅ Système de scoring sur 6 critères pondérés (100 pts total)
- ✅ Récupération automatique des matchs via API-Football
- ✅ Génération de pronostics quotidiens
- ✅ Génération du combiné quotidien premium
- ✅ **Backtest sur 1 saison historique** (validation cruciale)
- ✅ Logging détaillé pour analyse

**Livrable final** : Algorithme prouvé statistiquement, capable de générer 10+ pronostics quotidiens.

---

## ⚠️ RAPPEL CRITIQUE

> **Ce sprint définit le succès ou l'échec du projet.**
> 
> Si à la fin du sprint le backtest montre < 55% de win rate, **STOP** :
> - Itère sur les pondérations
> - Ajoute des critères
> - Consulte un expert data si nécessaire
> 
> **Ne PAS lancer en production avec un algo < 55%.**

---

## 📊 ARCHITECTURE DE L'ALGORITHME

### Vue d'ensemble

```
┌──────────────────────────────────────────────────┐
│ 1. FETCH MATCHS DU JOUR (API-Football)           │
│    → Job: FetchDailyMatchesJob (03h00)           │
└──────────────────────────────────────────────────┘
                   ↓
┌──────────────────────────────────────────────────┐
│ 2. POUR CHAQUE MATCH                             │
│    → Calculer score 0-100 (6 critères)           │
└──────────────────────────────────────────────────┘
                   ↓
┌──────────────────────────────────────────────────┐
│ 3. DÉCISION                                       │
│    Si score >= 50 → Publier prono                 │
│    Si score >= 65 → Éligible combiné              │
│    Si score >= 70 → Combiné bienvenue             │
└──────────────────────────────────────────────────┘
                   ↓
┌──────────────────────────────────────────────────┐
│ 4. GÉNÉRATION OUTPUT                              │
│    - 10-15 pronostics simples                     │
│    - 1 combiné premium (5 matchs)                 │
└──────────────────────────────────────────────────┘
```

---

## 🧮 LES 6 CRITÈRES DU SCORING

> 📖 **Détails complets formules** : voir `ANNEXE-Algorithme-Detail.md`

| # | Critère | Poids | Points Max | Description |
|---|---------|-------|-----------|-------------|
| 1 | 🔥 **Forme récente** | 28% | 28 pts | 5 derniers matchs |
| 2 | ⚔️ **H2H** | 23% | 23 pts | Confrontations directes |
| 3 | 🏟️ **Dom/Ext** | 18% | 18 pts | Performance dom vs ext |
| 4 | 📍 **Classement** | 13% | 13 pts | Écart championnat |
| 5 | ⚽ **Stats buts** | 10% | 10 pts | Moyenne, BTTS, clean sheets |
| 6 | 🌙 **Horaire** | 8% | 8 pts | Plage horaire match (NOUVEAU) |
| | **TOTAL** | **100%** | **100 pts** | |

---

## 📋 TÂCHES DÉTAILLÉES

### Semaine 1 - Récupération données + Critères

#### J1 (6h) - Service API-Football

```bash
php artisan make:command FetchDailyMatches
```

```php
// app/Services/External/ApiFootballService.php
namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Models\Competition;

class ApiFootballService
{
    private string $apiKey;
    private string $baseUrl = 'https://v3.football.api-sports.io';
    
    public function __construct()
    {
        $this->apiKey = config('services.api_football.key');
    }
    
    /**
     * Récupère les matchs du jour pour les championnats Top 5
     */
    public function fetchTodayMatches(): array
    {
        return Cache::remember(
            'api-football:today-matches:' . now()->format('Y-m-d'),
            now()->addHours(2),
            fn() => $this->callApi('/fixtures', [
                'date' => now()->format('Y-m-d'),
                'league' => '39,140,135,78,61', // EPL, La Liga, Serie A, Bundesliga, Ligue 1
                'season' => now()->year,
            ])
        );
    }
    
    /**
     * Stats des 10 derniers matchs d'une équipe
     */
    public function getTeamLastMatches(int $teamId, int $count = 10): array
    {
        return Cache::remember(
            "api-football:team:{$teamId}:last:{$count}",
            now()->addHours(6),
            fn() => $this->callApi('/fixtures', [
                'team' => $teamId,
                'last' => $count,
            ])
        );
    }
    
    /**
     * H2H entre 2 équipes (5 dernières confrontations)
     */
    public function getH2H(int $team1Id, int $team2Id): array
    {
        return Cache::remember(
            "api-football:h2h:{$team1Id}-{$team2Id}",
            now()->addDays(1),
            fn() => $this->callApi('/fixtures/headtohead', [
                'h2h' => "{$team1Id}-{$team2Id}",
                'last' => 5,
            ])
        );
    }
    
    /**
     * Classement actuel d'une compétition
     */
    public function getStandings(int $leagueId): array
    {
        return Cache::remember(
            "api-football:standings:{$leagueId}",
            now()->addHours(12),
            fn() => $this->callApi('/standings', [
                'league' => $leagueId,
                'season' => now()->year,
            ])
        );
    }
    
    /**
     * Stats équipe sur la saison (xG si disponible, sinon stats classiques)
     */
    public function getTeamStatistics(int $teamId, int $leagueId): array
    {
        return Cache::remember(
            "api-football:stats:team:{$teamId}:league:{$leagueId}",
            now()->addHours(12),
            fn() => $this->callApi('/teams/statistics', [
                'team' => $teamId,
                'league' => $leagueId,
                'season' => now()->year,
            ])
        );
    }
    
    private function callApi(string $endpoint, array $params = []): array
    {
        $response = Http::withHeaders([
            'x-rapidapi-host' => 'v3.football.api-sports.io',
            'x-rapidapi-key' => $this->apiKey,
        ])->get($this->baseUrl . $endpoint, $params);
        
        if (!$response->successful()) {
            \Log::error('API-Football error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);
            throw new \Exception('API-Football call failed');
        }
        
        return $response->json('response') ?? [];
    }
}
```

#### J2 (6h) - Job Récupération matchs quotidienne

```php
// app/Jobs/FetchDailyMatchesJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\External\ApiFootballService;
use App\Models\{FootballMatch, Team, Competition};

class FetchDailyMatchesJob implements ShouldQueue
{
    use Queueable;
    
    public int $tries = 3;
    public int $backoff = 60;
    
    public function handle(ApiFootballService $apiService): void
    {
        $matches = $apiService->fetchTodayMatches();
        
        foreach ($matches as $matchData) {
            $this->saveMatch($matchData);
        }
        
        \Log::info('Daily matches fetched', ['count' => count($matches)]);
    }
    
    private function saveMatch(array $data): void
    {
        // Compétition
        $competition = Competition::firstOrCreate(
            ['external_id' => $data['league']['id']],
            [
                'name' => $data['league']['name'],
                'country' => $data['league']['country'],
                'logo' => $data['league']['logo'],
            ]
        );
        
        // Équipes
        $homeTeam = Team::firstOrCreate(
            ['external_id' => $data['teams']['home']['id']],
            [
                'name' => $data['teams']['home']['name'],
                'logo' => $data['teams']['home']['logo'],
            ]
        );
        
        $awayTeam = Team::firstOrCreate(
            ['external_id' => $data['teams']['away']['id']],
            [
                'name' => $data['teams']['away']['name'],
                'logo' => $data['teams']['away']['logo'],
            ]
        );
        
        // Match
        FootballMatch::updateOrCreate(
            ['external_id' => $data['fixture']['id']],
            [
                'competition_id' => $competition->id,
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $awayTeam->id,
                'match_date' => $data['fixture']['date'],
                'venue' => $data['fixture']['venue']['name'] ?? null,
                'status' => $data['fixture']['status']['short'],
            ]
        );
    }
}
```

#### J3-J4 (12h) - Implémentation des 6 critères

##### Critère 1 : Forme récente (28 points)

```php
// app/Services/Algorithm/Criteria/RecentFormCriterion.php
namespace App\Services\Algorithm\Criteria;

use App\Models\Team;
use App\Services\External\ApiFootballService;

class RecentFormCriterion
{
    private const POINTS_WIN = 5;
    private const POINTS_DRAW = 2.5;
    private const POINTS_LOSE = 0;
    private const MAX_POINTS = 25; // 5 matchs × 5 pts
    private const WEIGHT = 28;
    
    public function __construct(
        private ApiFootballService $apiService
    ) {}
    
    public function calculate(Team $homeTeam, Team $awayTeam): array
    {
        $homeForm = $this->getTeamForm($homeTeam);
        $awayForm = $this->getTeamForm($awayTeam);
        
        // Différentiel pondéré sur 28 pts
        $difference = $homeForm['points'] - $awayForm['points'];
        $score = ($difference / self::MAX_POINTS) * self::WEIGHT;
        
        // Bonus consécutifs
        if ($homeForm['streak'] === 'W5') $score += 2;
        if ($awayForm['streak'] === 'L5') $score -= 2;
        
        // Clamper entre -28 et +28
        $score = max(-self::WEIGHT, min(self::WEIGHT, $score));
        
        return [
            'score' => round($score, 2),
            'home' => $homeForm,
            'away' => $awayForm,
            'difference' => $difference,
        ];
    }
    
    private function getTeamForm(Team $team): array
    {
        $matches = $this->apiService->getTeamLastMatches($team->external_id, 5);
        
        $points = 0;
        $results = [];
        
        foreach ($matches as $match) {
            $isHome = $match['teams']['home']['id'] === $team->external_id;
            $homeGoals = $match['goals']['home'];
            $awayGoals = $match['goals']['away'];
            
            if ($homeGoals === null || $awayGoals === null) continue; // Match annulé
            
            // Résultat du point de vue de team
            if ($isHome) {
                if ($homeGoals > $awayGoals) {
                    $points += self::POINTS_WIN;
                    $results[] = 'W';
                } elseif ($homeGoals === $awayGoals) {
                    $points += self::POINTS_DRAW;
                    $results[] = 'D';
                } else {
                    $results[] = 'L';
                }
            } else {
                if ($awayGoals > $homeGoals) {
                    $points += self::POINTS_WIN;
                    $results[] = 'W';
                } elseif ($homeGoals === $awayGoals) {
                    $points += self::POINTS_DRAW;
                    $results[] = 'D';
                } else {
                    $results[] = 'L';
                }
            }
        }
        
        // Détecter streak
        $streak = $this->detectStreak($results);
        
        return [
            'points' => $points,
            'results' => $results,
            'streak' => $streak,
        ];
    }
    
    private function detectStreak(array $results): string
    {
        if (count($results) < 5) return 'N/A';
        
        $allWin = count(array_filter($results, fn($r) => $r === 'W')) === 5;
        $allLose = count(array_filter($results, fn($r) => $r === 'L')) === 5;
        
        if ($allWin) return 'W5';
        if ($allLose) return 'L5';
        return 'mixed';
    }
}
```

##### Critère 2 : H2H (23 points)

```php
// app/Services/Algorithm/Criteria/HeadToHeadCriterion.php
class HeadToHeadCriterion
{
    private const WEIGHT = 23;
    private const MAX_DIFFERENCE = 25; // 5 victoires × 5 pts
    
    public function calculate(Team $homeTeam, Team $awayTeam): array
    {
        $h2hMatches = $this->apiService->getH2H(
            $homeTeam->external_id,
            $awayTeam->external_id
        );
        
        if (count($h2hMatches) < 3) {
            // Pas assez de données → désactiver critère
            return [
                'score' => 0,
                'reason' => 'Insufficient H2H data',
                'matches_count' => count($h2hMatches),
            ];
        }
        
        $homeWins = 0;
        $awayWins = 0;
        $draws = 0;
        
        foreach ($h2hMatches as $match) {
            $homeGoals = $match['goals']['home'];
            $awayGoals = $match['goals']['away'];
            $isHomeTeamPlayingHome = $match['teams']['home']['id'] === $homeTeam->external_id;
            
            if ($homeGoals === $awayGoals) {
                $draws++;
                continue;
            }
            
            $winner = $homeGoals > $awayGoals ? 'home' : 'away';
            
            if ($winner === 'home') {
                $isHomeTeamPlayingHome ? $homeWins++ : $awayWins++;
            } else {
                $isHomeTeamPlayingHome ? $awayWins++ : $homeWins++;
            }
        }
        
        $difference = ($homeWins * 5) - ($awayWins * 5);
        $score = ($difference / self::MAX_DIFFERENCE) * self::WEIGHT;
        
        return [
            'score' => round($score, 2),
            'home_wins' => $homeWins,
            'away_wins' => $awayWins,
            'draws' => $draws,
            'matches_count' => count($h2hMatches),
        ];
    }
}
```

##### Critère 3 : Dom/Ext (18 points)

```php
// app/Services/Algorithm/Criteria/HomeAwayCriterion.php
class HomeAwayCriterion
{
    private const WEIGHT = 18;
    
    public function calculate(Team $homeTeam, Team $awayTeam): array
    {
        $homeStats = $this->getHomeStats($homeTeam);
        $awayStats = $this->getAwayStats($awayTeam);
        
        $homeWinRate = $homeStats['wins'] / max($homeStats['played'], 1);
        $awayWinRate = $awayStats['wins'] / max($awayStats['played'], 1);
        
        $score = ($homeWinRate - $awayWinRate) * self::WEIGHT;
        
        // Bonus invincibilité domicile
        if ($homeStats['played'] >= 10 && $homeStats['losses'] === 0) {
            $score += 3;
        }
        
        // Malus aucune victoire extérieur
        if ($awayStats['played'] >= 10 && $awayStats['wins'] === 0) {
            $score -= 3;
        }
        
        $score = max(-self::WEIGHT, min(self::WEIGHT, $score));
        
        return [
            'score' => round($score, 2),
            'home' => $homeStats,
            'away' => $awayStats,
        ];
    }
    
    // Implémentation getHomeStats et getAwayStats
    // (à partir de getTeamLastMatches filtré par home/away)
}
```

##### Critère 4 : Classement (13 points)

```php
class LeaguePositionCriterion
{
    private const WEIGHT = 13;
    
    public function calculate(Team $homeTeam, Team $awayTeam, Competition $competition): array
    {
        // Si début saison (<5 journées), réduire poids
        $standings = $this->apiService->getStandings($competition->external_id);
        
        if (empty($standings)) {
            return ['score' => 0, 'reason' => 'No standings available'];
        }
        
        $homePosition = $this->findPosition($standings, $homeTeam->external_id);
        $awayPosition = $this->findPosition($standings, $awayTeam->external_id);
        
        if (!$homePosition || !$awayPosition) {
            return ['score' => 0, 'reason' => 'Team not found in standings'];
        }
        
        // Diff = position éloignée - position rapprochée
        // Position basse = mieux (1 = 1er)
        $gap = $awayPosition - $homePosition;
        
        // Échelle progressive
        $score = match (true) {
            abs($gap) >= 10 => 13 * sign($gap),
            abs($gap) >= 5 => 8 * sign($gap),
            abs($gap) >= 3 => 5 * sign($gap),
            abs($gap) >= 1 => 2 * sign($gap),
            default => 0,
        };
        
        return [
            'score' => $score,
            'home_position' => $homePosition,
            'away_position' => $awayPosition,
            'gap' => $gap,
        ];
    }
}
```

##### Critère 5 : Stats buts (10 points)

```php
class GoalStatsCriterion
{
    private const WEIGHT = 10;
    
    public function calculate(Team $homeTeam, Team $awayTeam): array
    {
        $homeStats = $this->getGoalStats($homeTeam);
        $awayStats = $this->getGoalStats($awayTeam);
        
        // Sub-critère 1 : Moyenne buts (4 pts)
        $avgGoalsScore = (($homeStats['avg_scored'] - $awayStats['avg_scored']) / 4) * 4;
        
        // Sub-critère 2 : BTTS (3 pts)
        $bttsScore = ($homeStats['btts_rate'] > 0.6 && $awayStats['btts_rate'] > 0.6) ? 3 : 0;
        
        // Sub-critère 3 : Clean sheets (3 pts)
        $cleanSheetsScore = ($homeStats['cs_rate'] - $awayStats['cs_rate']) * 3;
        
        $totalScore = $avgGoalsScore + $bttsScore + $cleanSheetsScore;
        $totalScore = max(-self::WEIGHT, min(self::WEIGHT, $totalScore));
        
        return [
            'score' => round($totalScore, 2),
            'avg_goals' => round($avgGoalsScore, 2),
            'btts' => $bttsScore,
            'clean_sheets' => round($cleanSheetsScore, 2),
        ];
    }
}
```

##### Critère 6 : Horaire (8 points) - 🌙 INNOVANT

```php
// app/Services/Algorithm/Criteria/TimeFactorCriterion.php
class TimeFactorCriterion
{
    private const WEIGHT = 8;
    
    public function calculate(FootballMatch $match): array
    {
        $hour = $match->match_date->hour;
        
        $baseScore = 4.0; // Neutre
        
        // Plages horaires
        $plageBonus = match (true) {
            $hour >= 16 && $hour < 20 => 2,  // Prime time
            $hour >= 12 && $hour < 16 => 0,  // Après-midi
            $hour >= 8 && $hour < 12 => -1,  // Matinée
            $hour >= 20 && $hour < 23 => 0,  // Soirée
            $hour >= 23 || $hour < 2 => -3,  // Nuit
            $hour >= 2 && $hour < 8 => -5,   // Nuit profonde
            default => 0,
        };
        
        $score = $baseScore + $plageBonus;
        
        // TODO: Bonus habitude équipe (matchs à cette plage > 5 sur 10)
        // TODO: Malus décalage horaire > 3h (matchs internationaux)
        
        $score = max(0, min(self::WEIGHT, $score));
        
        return [
            'score' => round($score, 2),
            'hour' => $hour,
            'plage' => $this->getPlageLabel($hour),
        ];
    }
    
    private function getPlageLabel(int $hour): string
    {
        return match (true) {
            $hour >= 16 && $hour < 20 => 'Prime time',
            $hour >= 12 && $hour < 16 => 'Après-midi',
            $hour >= 8 && $hour < 12 => 'Matinée',
            $hour >= 20 && $hour < 23 => 'Soirée',
            $hour >= 23 || $hour < 2 => 'Nuit',
            default => 'Nuit profonde',
        };
    }
}
```

#### J5 (4h) - Service principal de scoring

```php
// app/Services/Algorithm/PredictionScoringService.php
namespace App\Services\Algorithm;

use App\Models\FootballMatch;
use App\Services\Algorithm\Criteria\{
    RecentFormCriterion,
    HeadToHeadCriterion,
    HomeAwayCriterion,
    LeaguePositionCriterion,
    GoalStatsCriterion,
    TimeFactorCriterion
};

class PredictionScoringService
{
    public function __construct(
        private RecentFormCriterion $formCriterion,
        private HeadToHeadCriterion $h2hCriterion,
        private HomeAwayCriterion $homeAwayCriterion,
        private LeaguePositionCriterion $positionCriterion,
        private GoalStatsCriterion $goalCriterion,
        private TimeFactorCriterion $timeCriterion
    ) {}
    
    public function calculateScore(FootballMatch $match): array
    {
        $criteria = [
            'recent_form' => $this->formCriterion->calculate($match->homeTeam, $match->awayTeam),
            'head_to_head' => $this->h2hCriterion->calculate($match->homeTeam, $match->awayTeam),
            'home_away' => $this->homeAwayCriterion->calculate($match->homeTeam, $match->awayTeam),
            'league_position' => $this->positionCriterion->calculate($match->homeTeam, $match->awayTeam, $match->competition),
            'goal_stats' => $this->goalCriterion->calculate($match->homeTeam, $match->awayTeam),
            'time_factor' => $this->timeCriterion->calculate($match),
        ];
        
        $totalScore = array_sum(array_column($criteria, 'score'));
        
        // Convertir en score absolu 0-100 (pour le pari le plus probable)
        $absoluteScore = abs($totalScore);
        
        // Déterminer le pari recommandé
        $bestBet = $this->determineBestBet($criteria, $totalScore);
        
        return [
            'total_score' => round($absoluteScore, 2),
            'raw_score' => round($totalScore, 2),
            'best_bet' => $bestBet,
            'criteria' => $criteria,
            'confidence_level' => $this->getConfidenceLevel($absoluteScore),
        ];
    }
    
    private function determineBestBet(array $criteria, float $rawScore): array
    {
        // Logique simple v1 :
        // - Si score très positif → 1 (victoire domicile)
        // - Si score très négatif → 2 (victoire extérieur)
        // - Si proche de 0 → X (match nul) ou Over/Under selon stats buts
        
        if ($rawScore > 30) {
            return ['type' => 'match_winner', 'choice' => '1', 'reason' => 'Victoire domicile'];
        } elseif ($rawScore < -30) {
            return ['type' => 'match_winner', 'choice' => '2', 'reason' => 'Victoire extérieur'];
        } elseif ($criteria['goal_stats']['btts'] >= 3) {
            return ['type' => 'btts', 'choice' => 'yes', 'reason' => 'Both Teams To Score'];
        } else {
            return ['type' => 'over_under', 'choice' => 'over_2.5', 'reason' => 'Over 2.5 buts'];
        }
    }
    
    private function getConfidenceLevel(float $score): string
    {
        return match (true) {
            $score >= 85 => 'TRÈS_ÉLEVÉE',
            $score >= 70 => 'ÉLEVÉE',
            $score >= 60 => 'MOYENNE',
            $score >= 50 => 'FAIBLE',
            default => 'TRÈS_FAIBLE',
        };
    }
}
```

---

### Semaine 2 - Génération + Backtest

#### J6-J7 (12h) - Job de génération quotidienne

```php
// app/Jobs/GeneratePredictionsJob.php
namespace App\Jobs;

class GeneratePredictionsJob implements ShouldQueue
{
    public function handle(PredictionScoringService $scoringService): void
    {
        $matches = FootballMatch::whereDate('match_date', today())
            ->where('status', 'NS') // Not Started
            ->get();
        
        $predictions = [];
        
        foreach ($matches as $match) {
            try {
                $result = $scoringService->calculateScore($match);
                
                if ($result['total_score'] >= 50) {
                    $prediction = Prediction::create([
                        'match_id' => $match->id,
                        'bet_type' => $result['best_bet']['type'],
                        'bet_choice' => $result['best_bet']['choice'],
                        'odds' => $this->fetchOdds($match, $result['best_bet']),
                        'confidence_score' => $result['total_score'],
                        'scoring_details' => $result['criteria'],
                        'analysis' => $this->generateAnalysisText($match, $result),
                        'is_premium_only' => false,
                        'is_featured' => $result['total_score'] >= 80,
                    ]);
                    
                    $predictions[] = $prediction;
                }
            } catch (\Exception $e) {
                \Log::error('Prediction generation failed', [
                    'match_id' => $match->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        \Log::info('Predictions generated', [
            'date' => today()->toDateString(),
            'count' => count($predictions),
        ]);
        
        // Générer le combiné
        $this->generateDailyCombined($predictions);
    }
    
    private function generateDailyCombined(array $predictions): void
    {
        // Filtrer prédictions score >= 65
        $eligible = collect($predictions)
            ->filter(fn($p) => $p->confidence_score >= 65)
            ->sortByDesc('confidence_score')
            ->values();
        
        if ($eligible->count() < 5) {
            \Log::info('Not enough high-confidence predictions for daily combined');
            return;
        }
        
        // Sélectionner 5 matchs (max 2 par compétition)
        $selected = $this->selectDiverseMatches($eligible, 5);
        
        // Calcul cote totale
        $totalOdds = $selected->reduce(fn($carry, $p) => $carry * $p->odds, 1);
        
        if ($totalOdds < 8 || $totalOdds > 30) {
            \Log::info('Daily combined odds out of range', ['odds' => $totalOdds]);
            return;
        }
        
        CombinedBet::create([
            'date' => today(),
            'matches' => $selected->pluck('id')->toArray(),
            'total_odds' => $totalOdds,
            'confidence_avg' => $selected->avg('confidence_score'),
            'is_welcome_bonus' => false,
        ]);
    }
}
```

#### J8-J9 (12h) - **BACKTEST** (CRITIQUE)

```php
// app/Console/Commands/BacktestAlgorithm.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Algorithm\PredictionScoringService;
use App\Models\FootballMatch;

class BacktestAlgorithm extends Command
{
    protected $signature = 'algorithm:backtest 
        {--start=2024-01-01 : Date de début}
        {--end=2024-12-31 : Date de fin}
        {--min-score=50 : Score minimum pour publier}';
    
    protected $description = 'Backtest l\'algorithme sur des données historiques';
    
    public function handle(PredictionScoringService $scoringService): int
    {
        $start = $this->option('start');
        $end = $this->option('end');
        $minScore = (int) $this->option('min-score');
        
        $this->info("Backtest du {$start} au {$end} (score min: {$minScore})");
        
        // Récupérer matchs terminés sur la période
        $matches = FootballMatch::whereBetween('match_date', [$start, $end])
            ->where('status', 'FT') // Finished
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->get();
        
        $this->info("Matchs analysés : " . $matches->count());
        
        $stats = [
            'total' => 0,
            'won' => 0,
            'lost' => 0,
            'by_confidence' => [
                'TRÈS_ÉLEVÉE' => ['won' => 0, 'lost' => 0],
                'ÉLEVÉE' => ['won' => 0, 'lost' => 0],
                'MOYENNE' => ['won' => 0, 'lost' => 0],
                'FAIBLE' => ['won' => 0, 'lost' => 0],
            ],
        ];
        
        $bar = $this->output->createProgressBar($matches->count());
        
        foreach ($matches as $match) {
            try {
                $result = $scoringService->calculateScore($match);
                
                if ($result['total_score'] < $minScore) {
                    continue;
                }
                
                $stats['total']++;
                
                // Vérifier si le pari aurait gagné
                $wouldWin = $this->checkBetResult($match, $result['best_bet']);
                
                if ($wouldWin) {
                    $stats['won']++;
                    $stats['by_confidence'][$result['confidence_level']]['won']++;
                } else {
                    $stats['lost']++;
                    $stats['by_confidence'][$result['confidence_level']]['lost']++;
                }
                
            } catch (\Exception $e) {
                // Skip
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Afficher résultats
        $winRate = $stats['total'] > 0 ? ($stats['won'] / $stats['total']) * 100 : 0;
        
        $this->info("RÉSULTATS BACKTEST");
        $this->info("==================");
        $this->info("Total pronostics : {$stats['total']}");
        $this->info("Gagnés : {$stats['won']}");
        $this->info("Perdus : {$stats['lost']}");
        $this->info("Win rate : " . number_format($winRate, 2) . "%");
        $this->newLine();
        
        $this->info("PAR NIVEAU DE CONFIANCE :");
        foreach ($stats['by_confidence'] as $level => $data) {
            $total = $data['won'] + $data['lost'];
            $rate = $total > 0 ? ($data['won'] / $total) * 100 : 0;
            $this->info("- {$level} : " . number_format($rate, 2) . "% ({$data['won']}/{$total})");
        }
        
        // Verdict
        $this->newLine();
        if ($winRate >= 60) {
            $this->info("✅ EXCELLENT : algo prêt pour production !");
        } elseif ($winRate >= 55) {
            $this->info("✅ BON : algo prêt pour MVP, à optimiser");
        } elseif ($winRate >= 50) {
            $this->warn("⚠️ MOYEN : itérer avant production");
        } else {
            $this->error("🔴 INSUFFISANT : revoir l'algorithme");
        }
        
        return Command::SUCCESS;
    }
    
    private function checkBetResult(FootballMatch $match, array $bet): bool
    {
        $homeGoals = $match->home_score;
        $awayGoals = $match->away_score;
        
        return match ($bet['type']) {
            'match_winner' => match ($bet['choice']) {
                '1' => $homeGoals > $awayGoals,
                'X' => $homeGoals === $awayGoals,
                '2' => $awayGoals > $homeGoals,
            },
            'over_under' => match ($bet['choice']) {
                'over_2.5' => ($homeGoals + $awayGoals) > 2.5,
                'under_2.5' => ($homeGoals + $awayGoals) < 2.5,
            },
            'btts' => match ($bet['choice']) {
                'yes' => $homeGoals > 0 && $awayGoals > 0,
                'no' => $homeGoals === 0 || $awayGoals === 0,
            },
            default => false,
        };
    }
}
```

**Lancer le backtest** :

```bash
# Backtest sur saison 2024
php artisan algorithm:backtest --start=2024-01-01 --end=2024-12-31

# Test sur des matchs très confiants seulement
php artisan algorithm:backtest --start=2024-01-01 --end=2024-12-31 --min-score=75
```

#### J10 (4h) - Génération du texte d'analyse (IA)

Optionnel : utiliser **Claude API** pour générer des analyses plus humaines.

```php
// app/Services/Algorithm/AnalysisTextGenerator.php
class AnalysisTextGenerator
{
    public function generate(FootballMatch $match, array $scoringResult): string
    {
        // Version 1 : template-based
        return view('analysis.template', [
            'match' => $match,
            'criteria' => $scoringResult['criteria'],
            'confidence' => $scoringResult['confidence_level'],
        ])->render();
        
        // Version 2 : Claude API (si budget)
        // return $this->generateViaClaude($match, $scoringResult);
    }
}
```

#### J11-J12 (8h) - Tests + Itérations

Tests de l'algorithme :

```php
// tests/Feature/Algorithm/PredictionScoringTest.php
test('algorithm calculates score for a match', function () {
    $match = FootballMatch::factory()->create([
        'match_date' => now()->setHour(17), // Prime time
    ]);
    
    $service = app(PredictionScoringService::class);
    $result = $service->calculateScore($match);
    
    expect($result)
        ->toHaveKey('total_score')
        ->toHaveKey('best_bet')
        ->toHaveKey('criteria');
    
    expect($result['total_score'])->toBeFloat()->toBeGreaterThanOrEqual(0);
});

test('time factor gives bonus for prime time matches', function () {
    $primeMatch = FootballMatch::factory()->create([
        'match_date' => now()->setHour(17),
    ]);
    
    $criterion = app(TimeFactorCriterion::class);
    $result = $criterion->calculate($primeMatch);
    
    expect($result['score'])->toBeGreaterThan(4); // Au-dessus du neutre
});
```

---

## ✅ CRITÈRES DE VALIDATION SPRINT 03

### Fonctionnel
- [ ] Job `FetchDailyMatchesJob` récupère les matchs du jour
- [ ] Les 6 critères calculent leur score correctement
- [ ] Service `PredictionScoringService` retourne un score 0-100
- [ ] Job `GeneratePredictionsJob` crée 10+ predictions/jour
- [ ] Combiné quotidien généré si conditions remplies

### Performance
- [ ] Backtest sur 1 saison complétée
- [ ] **Win rate ≥ 55% sur niveau "ÉLEVÉE"** ⚠️
- [ ] **Win rate ≥ 50% sur tous les pronostics**
- [ ] Cache Redis fonctionnel (réduction appels API)

### Tests
- [ ] Tests unitaires pour chaque critère
- [ ] Test du service principal
- [ ] CI GitHub Actions vert

### Documentation
- [ ] Doc des poids et formules dans `ANNEXE-Algorithme-Detail.md`
- [ ] Logs détaillés des décisions

---

## 🚨 CHECKPOINT CRITIQUE

**Avant de passer au Sprint 04, valider** :

```bash
php artisan algorithm:backtest --start=2024-01-01 --end=2024-06-30 --min-score=70
```

**Résultat attendu** : Win rate ≥ 60% sur les pronostics à confiance ÉLEVÉE.

**Si < 55%** :
- Revoir les pondérations
- Ajouter des critères (xG, blessures - prévu Sprint 04)
- Consulter un expert data si possible

**Si > 60%** : 🎉 GO Sprint 04 !

---

## 🎓 PROMPTS IA UTILES

```
"Génère un service Laravel qui calcule la forme récente d'une équipe 
basée sur ses 5 derniers matchs avec : 5 pts/victoire, 2.5/nul, 0/défaite. 
Retourne un score normalisé sur 28 points."

"Crée une commande Artisan 'algorithm:backtest' qui prend une période 
de dates, parcourt les matchs terminés, applique l'algo de scoring, 
et calcule le win rate global ainsi que par niveau de confiance."

"Implémente un système de cache Redis pour les appels API-Football 
avec TTL différents selon le type de donnée (matchs: 2h, stats: 12h, 
classement: 12h, H2H: 24h)."
```

---

## 📊 ESTIMATION TEMPS

| Tâche | Heures |
|-------|--------|
| Service API-Football | 6h |
| Job fetch matchs | 6h |
| 6 critères de scoring | 12h |
| Service principal | 4h |
| Job génération predictions | 12h |
| Backtest command | 12h |
| Tests + itérations | 8h |
| **TOTAL** | **60h** |

---

## 🔗 PROCHAINE ÉTAPE

✅ **Sprint 03 terminé avec win rate ≥ 55%** → `SPRINT-04-Donnees-ML.md`

⚠️ Si win rate < 55% → **STOP, ITÉRER, NE PAS CONTINUER**

---

*L'algorithme = le coeur du produit. Sans lui, rien ne marche.*
