<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Services\FootballApiService;
use App\Services\PredictionAlgorithmService;

class PredictionController extends Controller
{
    public function __construct(
        private readonly FootballApiService $footballApi,
        private readonly PredictionAlgorithmService $predictionAlgorithm,
    ) {
    }

    /**
     * Récupérer les pronostics du jour
     * OPTIMISÉ: Utilise d'abord la base de données (rapide), puis API-Football si nécessaire
     * Accessible sans authentification (mode invité)
     *
     * Cache: 5 minutes pour optimiser les performances
     */
    public function today(Request $request)
    {
        // Utiliser auth()->user() pour permettre l'accès invité
        $user = auth('sanctum')->user();
        $isPremium = $user && $user->is_premium;

        // Accepter un paramètre date (format: YYYY-MM-DD) ou utiliser aujourd'hui par défaut
        $dateParam = $request->query('date');
        $selectedDate = $dateParam
            ? Carbon::parse($dateParam)->startOfDay()
            : Carbon::today();

        $dateString = $selectedDate->format('Y-m-d');
        $competition = $request->query('competition', 'all');

        // Clé de cache unique basée sur date, compétition et statut premium
        $cacheKey = "predictions_today_{$dateString}_{$competition}_" . ($isPremium ? 'premium' : 'free');
        $cacheTtl = 300; // 5 minutes

        // Vérifier le cache
        $cached = Cache::get($cacheKey);
        if ($cached) {
            Log::info("📦 Cache hit pour predictions du {$dateString}");
            return response()->json($cached);
        }

        // OPTIMISATION: Essayer d'abord la base de données (BEAUCOUP plus rapide)
        Log::info("📊 Vérification des prédictions en base de données pour le {$dateString}");
        
        $popularLeagueNames = array_column(config('football-api.popular_leagues', []), 'name');

        $query = DB::table('predictions')
            ->where('is_published', true)
            ->whereDate('match_date', $dateString)
            ->where(fn($q) =>
                $q->where('league_tier', '<=', 3)
                  ->orWhereIn('competition', $popularLeagueNames)
            )
            ->orderBy('league_tier', 'asc')
            ->orderBy('confidence_stars', 'desc')
            ->orderBy('match_time', 'asc');
        
        // Filtrer par compétition si fourni
        if ($competition && $competition !== 'all') {
            $query->where('competition', $competition);
        }
        
        // Si l'utilisateur n'est pas premium, exclure les pronostics premium
        if (!$isPremium) {
            $query->where('is_premium', false);
        }
        
        $dbPredictions = $query->get();
        
        // Si on a des prédictions en base (au moins 3), les utiliser directement
        if ($dbPredictions->count() >= 3) {
            Log::info("✅ " . $dbPredictions->count() . " prédictions trouvées en base de données (source: database) - RÉPONSE RAPIDE");
            
            $formattedPredictions = $dbPredictions->map(function ($prediction) use ($isPremium) {
                return $this->formatPrediction($prediction, $isPremium);
            })->values();
            
            // Mettre en cache et retourner
            $responseData = [
                'success' => true,
                'data' => $formattedPredictions,
                'source' => 'database',
                'cached_at' => now()->toIso8601String(),
            ];
            
            Cache::put($cacheKey, $responseData, $cacheTtl);
            return response()->json($responseData);
        }
        
        // Base vide — les prédictions sont générées exclusivement par le job schedulé.
        // On ne touche jamais l'API-Football depuis un endpoint utilisateur (quota 100 req/jour).
        Log::warning("Predictions: base vide pour {$dateString}, job schedulé non encore exécuté");

        $responseData = [
            'success' => true,
            'data'    => $dbPredictions->map(fn($p) => $this->formatPrediction($p, $isPremium))->values(),
            'source'  => 'database',
            'cached_at' => now()->toIso8601String(),
        ];

        Cache::put($cacheKey, $responseData, 60); // TTL court : 1 min pour réessayer rapidement
        return response()->json($responseData);
    }

    /**
     * Fallback: Récupérer les prédictions depuis la base de données
     */
    private function getPredictionsFromDatabase(Request $request, Carbon $selectedDate, bool $isPremium)
    {
        $startDate = $selectedDate->copy()->startOfDay();
        $endDate = $selectedDate->copy()->endOfDay();

        $popularLeagues = array_keys(config('football-api.popular_leagues', []));

        $popularLeagueNames = array_column(config('football-api.popular_leagues', []), 'name');

        $query = DB::table('predictions')
            ->where('is_published', true)
            ->whereBetween('match_date', [$startDate, $endDate])
            ->where(fn($q) =>
                $q->where('league_tier', '<=', 3)
                  ->orWhereIn('competition', $popularLeagueNames)
            )
            ->orderBy('league_tier', 'asc')
            ->orderBy('confidence_stars', 'desc')
            ->orderBy('match_time', 'asc');

        // Filtrer par compétition si fourni
        $competition = $request->query('competition');
        if ($competition && $competition !== 'all') {
            $query->where('competition', $competition);
        }

        // Si l'utilisateur n'est pas premium, exclure les pronostics premium
        if (!$isPremium) {
            $query->where('is_premium', false);
        }

        $predictions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $predictions->map(function ($prediction) use ($isPremium) {
                return $this->formatPrediction($prediction, $isPremium);
            }),
            'source' => 'database', // Indiquer que les données viennent de la base
        ]);
    }

    /**
     * Récupérer un pronostic par ID
     * Récupère les détails depuis API-Football
     * Accessible sans authentification (mode invité)
     */
    public function show(Request $request, $id)
    {
        // Utiliser auth()->user() pour permettre l'accès invité
        $user = auth('sanctum')->user();
        $isPremium = $user && $user->is_premium;

        // Essayer d'abord de récupérer depuis la base pour avoir le match_id
        $prediction = DB::table('predictions')
            ->where('id', $id)
            ->first();

        // Si pas en base, essayer avec l'ID directement (format: api_matchId)
        $matchId = null;
        if (!$prediction && strpos($id, 'api_') === 0) {
            $matchId = str_replace('api_', '', $id);
        } elseif ($prediction) {
            $matchId = $prediction->match_id;
        }

        if (!$matchId) {
            return response()->json([
                'success' => false,
                'message' => 'Pronostic non trouvé',
            ], 404);
        }

        Log::info("📥 Récupération des détails du match depuis API-Football: {$matchId}");

        try {
            // Récupérer les détails depuis API-Football (ID numérique)
            $fixtureResponse = is_numeric($matchId)
                ? $this->footballApi->getMatchDetails((int) $matchId)
                : null;

            if (!$fixtureResponse || empty($fixtureResponse['response'])) {
                // Fallback sur la base de données
                if ($prediction) {
                    return response()->json([
                        'success' => true,
                        'data'    => $this->formatPrediction($prediction, $isPremium),
                        'source'  => 'database',
                    ]);
                }
                return response()->json(['success' => false, 'message' => 'Détails du match non disponibles'], 404);
            }

            $fixture    = $fixtureResponse['response'][0];
            $homeTeam   = $fixture['teams']['home'] ?? null;
            $awayTeam   = $fixture['teams']['away'] ?? null;

            if (!$homeTeam || !$awayTeam) {
                throw new \Exception("Équipes manquantes dans la réponse API");
            }

            // Construire les données enrichies
            $predictionData = $this->buildPredictionFromApiFootball(
                $fixture,
                $prediction,
                $isPremium
            );

            Log::info("✅ Détails du match récupérés depuis l'API pour {$matchId}");

            return response()->json([
                'success' => true,
                'data' => $predictionData,
                'source' => 'api', // Indiquer que les données viennent de l'API
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Erreur lors de la récupération depuis l'API: " . $e->getMessage(), [
                'match_id' => $matchId,
                'error' => $e->getTraceAsString()
            ]);

            // Fallback sur la base de données
            if ($prediction) {
                return response()->json([
                    'success' => true,
                    'data' => $this->formatPrediction($prediction, $isPremium),
                    'source' => 'database',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer l'historique des pronostics
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $isPremium = $user->is_premium;

        $page = $request->input('page', 1);
        $perPage = 15;
        $status = $request->input('status'); // won, lost, pending
        $competition = $request->input('competition');

        $query = DB::table('predictions')
            ->where('is_published', true)
            ->orderBy('match_date', 'desc');

        // Filtrer par statut si fourni
        if ($status) {
            $query->where('status', $status);
        }

        // Filtrer par compétition si fourni
        if ($competition && $competition !== 'all') {
            $query->where('competition', $competition);
        }

        // Si l'utilisateur n'est pas premium, exclure les pronostics premium
        if (!$isPremium) {
            $query->where('is_premium', false);
        }

        $total = $query->count();
        $predictions = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $predictions->map(function ($prediction) use ($isPremium) {
                return $this->formatPrediction($prediction, $isPremium);
            }),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_more' => $page < ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Récupérer les statistiques de l'utilisateur
     *
     * Cache: 10 minutes par compétition
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        $competition = $request->input('competition', 'all');

        // Cache de 10 minutes basé sur la compétition
        $cacheKey = "statistics_{$competition}";
        $cacheTtl = 600; // 10 minutes

        return Cache::remember($cacheKey, $cacheTtl, function () use ($competition) {
            return $this->getStatisticsData($competition);
        });
    }

    /**
     * Récupérer les données des statistiques (utilisé par le cache)
     */
    private function getStatisticsData(string $competition)
    {
        $query = DB::table('predictions')->where('is_published', true);

        // Filtrer par compétition si fourni
        if ($competition && $competition !== 'all') {
            $query->where('competition', $competition);
        }

        $stats = [
            'total_predictions' => (clone $query)
                ->where('status', '!=', 'pending')
                ->count(),

            'correct_predictions' => (clone $query)
                ->where('status', 'won')
                ->count(),

            'incorrect_predictions' => (clone $query)
                ->where('status', 'lost')
                ->count(),

            'pending_predictions' => (clone $query)
                ->where('status', 'pending')
                ->count(),
        ];

        $successRate = $stats['total_predictions'] > 0
            ? round(($stats['correct_predictions'] / $stats['total_predictions']) * 100, 2)
            : 0;

        // Stats par période
        $today = Carbon::today();
        $weekAgo = Carbon::today()->subDays(7);
        $monthAgo = Carbon::today()->subDays(30);

        $todayStats = $this->getPeriodStats($today, $today->copy()->addDay(), $competition);
        $weekStats = $this->getPeriodStats($weekAgo, $today, $competition);
        $monthStats = $this->getPeriodStats($monthAgo, $today, $competition);

        // Calculer les séries (current_streak et best_streak)
        $streaks = $this->calculateStreaks($competition);

        return response()->json([
            'success' => true,
            'data' => [
                'total_predictions' => $stats['total_predictions'],
                'correct_predictions' => $stats['correct_predictions'],
                'incorrect_predictions' => $stats['incorrect_predictions'],
                'pending_predictions' => $stats['pending_predictions'],
                'success_rate' => $successRate,
                'current_streak' => $streaks['current'],
                'best_streak' => $streaks['best'],
                'today' => $todayStats,
                'this_week' => $weekStats,
                'this_month' => $monthStats,
            ],
        ]);
    }

    /**
     * GET /api/predictions/welcome-combined
     * Combiné de bienvenue gratuit — 3 prédictions 1 étoile pour les nouveaux utilisateurs
     */
    public function welcomeCombined(Request $request)
    {
        $cacheKey = 'welcome_combined_' . Carbon::today()->format('Y-m-d');

        $data = Cache::remember($cacheKey, 3600, function () {
            $predictions = DB::table('predictions')
                ->where('is_published', true)
                ->where('is_premium', false)
                ->whereDate('match_date', Carbon::today())
                ->orderBy('confidence_stars', 'desc')
                ->orderBy('total_score', 'desc')
                ->limit(3)
                ->get();

            if ($predictions->count() < 1) {
                return null;
            }

            $totalOdds = $predictions->reduce(fn ($carry, $p) => $carry * (float) $p->odds, 1.0);

            return [
                'success'     => true,
                'predictions' => $predictions->map(fn ($p) => $this->formatPrediction($p, false))->values(),
                'total_odds'  => round($totalOdds, 2),
                'date'        => Carbon::today()->toDateString(),
                'is_free'     => true,
            ];
        });

        if (!$data) {
            return response()->json([
                'success'     => false,
                'message'     => 'Aucune prédiction disponible pour le combiné de bienvenue aujourd\'hui',
                'predictions' => [],
            ]);
        }

        return response()->json($data);
    }

    /**
     * Envoyer un feedback sur un pronostic
     */
    public function feedback(Request $request)
    {
        $request->validate([
            'prediction_id' => 'required|integer|exists:predictions,id',
            'feedback_type' => 'required|in:helpful,not_helpful',
        ]);

        $user = $request->user();

        // Enregistrer le feedback
        DB::table('feedbacks')->insert([
            'user_id' => $user->id,
            'prediction_id' => $request->prediction_id,
            'feedback_type' => $request->feedback_type,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Merci pour votre retour !',
        ]);
    }

    /**
     * Récupérer la liste des compétitions disponibles
     * Triées par priorité (ligues les plus reconnues en premier)
     *
     * Cache: 1 heure (les compétitions changent rarement)
     */
    public function competitions(Request $request)
    {
        // Cache d'1 heure pour les compétitions
        $cacheKey = 'competitions_list';
        $cacheTtl = 3600; // 1 heure

        return Cache::remember($cacheKey, $cacheTtl, function () {
            return $this->getCompetitionsData();
        });
    }

    /**
     * Récupérer les données des compétitions (utilisé par le cache)
     */
    private function getCompetitionsData()
    {
        // Priorité des compétitions (les plus reconnues en premier)
        // Plus le score est bas, plus la compétition est prioritaire
        $competitionPriorities = [
            // Tier 1 - Ligues majeures européennes (priorité 1-10)
            'Premier League' => 1,
            'La Liga' => 2,
            'Serie A' => 3,
            'Bundesliga' => 4,
            'Ligue 1' => 5,
            'UEFA Champions League' => 6,
            'Champions League' => 6,
            
            // Tier 2 - Compétitions UEFA et ligues secondaires majeures (priorité 11-20)
            'UEFA Europa League' => 11,
            'Europa League' => 11,
            'UEFA Conference League' => 12,
            'Conference League' => 12,
            'Liga Portugal' => 13,
            'Primeira Liga' => 13,
            'Eredivisie' => 14,
            'Saudi Pro League' => 15,
            'MLS' => 16,
            
            // Tier 3 - Compétitions internationales et secondaires (priorité 21-30)
            'Africa Cup of Nations' => 21,
            'Copa America' => 22,
            'Euro' => 23,
            'World Cup' => 24,
            'Championship' => 25,
            'Serie B' => 26,
            'LaLiga2' => 27,
            '2. Bundesliga' => 28,
            'Ligue 2' => 29,
            
            // Tier 4 - Ligues inférieures (priorité 31-40)
            'League One' => 31,
            'League Two' => 32,
            'National League' => 33,
        ];
        
        // Priorité des pays (les plus reconnus en premier)
        $countryPriorities = [
            'England' => 1,
            'Spain' => 2,
            'Italy' => 3,
            'Germany' => 4,
            'France' => 5,
            'International' => 6,
            'Portugal' => 7,
            'Netherlands' => 8,
            'Saudi Arabia' => 9,
            'USA' => 10,
            'Brazil' => 11,
            'Argentina' => 12,
        ];

        $competitions = DB::table('predictions')
            ->select('competition', 'competition_id', 'country', 'competition_logo')
            ->where('is_published', true)
            ->distinct()
            ->get();

        // Trier les compétitions par priorité
        $sortedCompetitions = $competitions->sortBy(function ($item) use ($competitionPriorities, $countryPriorities) {
            $competitionPriority = $competitionPriorities[$item->competition] ?? 100;
            $countryPriority = $countryPriorities[$item->country] ?? 50;
            // Priorité combinée : pays * 1000 + compétition (pour grouper par pays prioritaire)
            return $countryPriority * 1000 + $competitionPriority;
        });

        // Grouper par pays (en gardant l'ordre de priorité)
        $groupedByCountry = $sortedCompetitions->groupBy('country')->map(function ($items, $country) use ($competitionPriorities) {
            // Trier les compétitions au sein de chaque pays par priorité
            $sortedItems = $items->sortBy(function ($item) use ($competitionPriorities) {
                return $competitionPriorities[$item->competition] ?? 100;
            });
            
            return [
                'country' => $country,
                'competitions' => $sortedItems->map(function ($item) {
                    return [
                        'id' => $item->competition_id,
                        'name' => $item->competition,
                        'country' => $item->country,
                        'logo' => $item->competition_logo ?? null,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => $groupedByCountry,
        ]);
    }

    /**
     * Récupérer le combiné premium quotidien (TOP 3-5 matchs)
     * GET /api/predictions/combined-daily?date=2026-01-04
     */
    public function combinedDaily(Request $request)
    {
        $user      = auth('sanctum')->user();
        $isPremium = $user && $user->is_premium;

        $date       = $request->input('date', Carbon::today()->toDateString());
        $targetDate = Carbon::parse($date);

        // ── Chercher d'abord en DB (top 5 par score, publiés aujourd'hui) ──
        $dbPredictions = DB::table('predictions')
            ->where('is_published', true)
            ->whereDate('match_date', $targetDate)
            ->where('total_score', '>=', 65)
            ->orderBy('total_score', 'desc')
            ->limit(5)
            ->get();

        if ($dbPredictions->isNotEmpty()) {
            $totalOdds     = $dbPredictions->reduce(fn($carry, $p) => $carry * (float) $p->odds, 1.0);
            $avgConfidence = $dbPredictions->avg('total_score');

            return response()->json([
                'success' => true,
                'data'    => [
                    'date'           => $targetDate->toDateString(),
                    'total_odds'     => round($totalOdds, 2),
                    'avg_confidence' => round($avgConfidence, 2),
                    'matches'        => $dbPredictions->map(fn($p) => $this->formatPrediction($p, $isPremium))->values()->all(),
                ],
            ]);
        }

        // ── DB vide : premium génère à la demande, free attend ──────────────
        if (!$isPremium) {
            return response()->json([
                'success' => false,
                'message' => 'Le combiné du jour sera disponible après la génération automatique',
            ], 404);
        }

        $cacheKey = 'combined_daily_premium_' . $targetDate->format('Y-m-d');

        $result = Cache::remember($cacheKey, 300, function () use ($targetDate) {
            try {
                $fixtures = $this->footballApi->getPopularMatches($targetDate->format('Y-m-d'), 30);
            } catch (\Throwable $e) {
                Log::error('combinedDaily premium: getPopularMatches failed', ['error' => $e->getMessage()]);
                return null;
            }

            if (empty($fixtures)) {
                return ['success' => false, 'message' => 'Aucun match disponible pour cette date'];
            }

            $coupon = $this->predictionAlgorithm->generateDailyCoupon($fixtures, 3, 5, 60.0);

            if (!($coupon['success'] ?? false)) {
                return $coupon;
            }

            // Convertir les picks en format PredictionModel compatible Flutter
            $matches = array_map(function (array $pick): array {
                return [
                    'id'                => 0,
                    'match_id'          => null,
                    'prediction_type'   => $pick['type'] ?? '1X2',
                    'predicted_outcome' => $pick['prediction'] ?? '',
                    'odds'              => $pick['odds'] ?? 1.0,
                    'confidence_score'  => (int) ($pick['confidence'] ?? 0),
                    'confidence_stars'  => $pick['stars'] ?? 1,
                    'is_premium'        => $pick['is_premium'] ?? true,
                    'result'            => null,
                    'created_at'        => now()->toIso8601String(),
                    'match'             => [
                        'id'             => null,
                        'fixture_id'     => null,
                        'home_team'      => explode(' vs ', $pick['match'] ?? ' vs ')[0] ?? '',
                        'away_team'      => explode(' vs ', $pick['match'] ?? ' vs ')[1] ?? '',
                        'home_team_logo' => null,
                        'away_team_logo' => null,
                        'league'         => $pick['league'] ?? '',
                        'league_logo'    => null,
                        'match_date'     => $pick['date'] ?? now()->toIso8601String(),
                        'status'         => 'NS',
                        'home_score'     => null,
                        'away_score'     => null,
                    ],
                ];
            }, $coupon['picks']);

            return [
                'success' => true,
                'data'    => [
                    'date'           => $targetDate->toDateString(),
                    'total_odds'     => $coupon['total_odds'],
                    'avg_confidence' => $coupon['avg_confidence'],
                    'matches'        => $matches,
                ],
            ];
        });

        if ($result === null) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la génération du combiné'], 500);
        }

        return response()->json($result, ($result['success'] ?? false) ? 200 : 404);
    }

    /**
     * Rechercher des matchs par équipe, compétition ou date
     * GET /api/predictions/search?q=query&date=2026-01-04
     */
    public function search(Request $request)
    {
        $user = auth('sanctum')->user();
        $isPremium = $user && $user->is_premium;

        $query = $request->input('q', '');
        $date = $request->input('date');

        if (empty($query) && empty($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Paramètre de recherche requis (q ou date)',
            ], 400);
        }

        $dbQuery = DB::table('predictions')
            ->where('is_published', true);

        // Recherche par texte (équipe ou compétition)
        if (!empty($query)) {
            $dbQuery->where(function ($q) use ($query) {
                $q->where('home_team', 'LIKE', "%{$query}%")
                  ->orWhere('away_team', 'LIKE', "%{$query}%")
                  ->orWhere('competition', 'LIKE', "%{$query}%");
            });
        }

        // Filtre par date
        if ($date) {
            $dbQuery->whereDate('match_date', $date);
        }

        // Si l'utilisateur n'est pas premium, exclure les pronostics premium
        if (!$isPremium) {
            $dbQuery->where('is_premium', false);
        }

        $predictions = $dbQuery->orderBy('league_tier', 'asc')
            ->orderBy('confidence_stars', 'desc')
            ->orderBy('match_time', 'asc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $predictions->map(function ($prediction) use ($isPremium) {
                return $this->formatPrediction($prediction, $isPremium);
            }),
            'count' => $predictions->count(),
        ]);
    }

    /**
     * Coupon du jour : sélection des meilleurs pronostics combinés
     * GET /api/predictions/coupon
     */
    public function coupon(Request $request)
    {
        $cacheKey = 'coupon_daily_' . Carbon::today()->format('Y-m-d');

        $coupon = Cache::remember($cacheKey, 300, function () use ($request) {
            $limit         = (int) $request->query('limit', 30);
            $minPicks      = (int) $request->query('min', 4);
            $maxPicks      = (int) $request->query('max', 5);
            $minConfidence = (float) $request->query('confidence', 60);

            try {
                $fixtures = $this->footballApi->getPopularMatches(Carbon::today()->format('Y-m-d'), $limit);
            } catch (\Throwable $e) {
                Log::error('coupon: getPopularMatches failed', ['error' => $e->getMessage()]);
                return null;
            }

            if (empty($fixtures)) {
                return ['success' => false, 'message' => 'Aucun match disponible aujourd\'hui', 'picks' => []];
            }

            return $this->predictionAlgorithm->generateDailyCoupon($fixtures, $minPicks, $maxPicks, $minConfidence);
        });

        if ($coupon === null) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la génération du coupon'], 500);
        }

        return response()->json($coupon);
    }

    /**
     * Formater un pronostic pour la réponse
     */
    private function formatPrediction($prediction, $isPremium)
    {
        $isLocked = $prediction->is_premium && !$isPremium;

        // Déterminer le statut du match
        $matchStatus = 'NS'; // Not Started par défaut
        if ($prediction->home_score !== null && $prediction->away_score !== null) {
            // Si le match a des scores et est aujourd'hui, c'est probablement en direct ou terminé
            $matchDate = \Carbon\Carbon::parse($prediction->match_date);
            $now = \Carbon\Carbon::now();
            $diffHours = $matchDate->diffInHours($now, false);
            
            if ($diffHours >= 0 && $diffHours <= 3) {
                // Match dans les 3 dernières heures avec scores = probablement en direct
                $matchStatus = 'live';
            } elseif ($diffHours > 3) {
                // Match terminé depuis plus de 3 heures
                $matchStatus = 'FT';
            }
        } elseif ($prediction->match_date) {
            $matchDate = \Carbon\Carbon::parse($prediction->match_date);
            if ($matchDate->isPast()) {
                $matchStatus = 'FT';
            } else {
                $matchStatus = 'NS';
            }
        }

        $data = [
            'id' => $prediction->id,
            'match' => [
                'id' => $prediction->match_id,
                'home_team' => $prediction->home_team,
                'away_team' => $prediction->away_team,
                'home_team_id' => $prediction->home_team_id,
                'away_team_id' => $prediction->away_team_id,
                'home_team_logo' => $prediction->home_team_logo ?? null,
                'away_team_logo' => $prediction->away_team_logo ?? null,
                'competition' => $prediction->competition,
                'competition_id' => $prediction->competition_id,
                'competition_logo' => $prediction->competition_logo ?? null,
                'country' => $prediction->country,
                'match_date' => $prediction->match_date,
                'match_time' => $prediction->match_time,
                'home_score' => $prediction->home_score,
                'away_score' => $prediction->away_score,
                'status' => $matchStatus,
            ],
            'bet_type' => $prediction->bet_type,
            'prediction' => $isLocked ? null : $prediction->prediction,
            'odds' => $isLocked ? null : $prediction->odds,
            'confidence_stars' => $prediction->confidence_stars,
            'status' => $prediction->status,
            'is_correct' => $prediction->status === 'won' ? true : ($prediction->status === 'lost' ? false : null),
            'is_premium' => $prediction->is_premium,
            'is_locked' => $isLocked,
            'published_at' => $prediction->published_at,
        ];

        // Ajouter les détails complets si non verrouillé
        if (!$isLocked) {
            $data['scores'] = [
                'form' => $prediction->score_form,
                'h2h' => $prediction->score_h2h,
                'home_away' => $prediction->score_home_away,
                'league' => $prediction->score_league,
                'goals' => $prediction->score_goals,
                'time' => $prediction->score_time,
                'total' => $prediction->total_score,
            ];

            $data['analysis_details'] = $prediction->analysis_details
                ? json_decode($prediction->analysis_details, true)
                : null;
        }

        return $data;
    }

    /**
     * Calculer les séries de victoires (current_streak et best_streak)
     * 
     * current_streak : Nombre de victoires consécutives depuis la prédiction la plus récente
     * best_streak : La plus longue série de victoires consécutives dans tout l'historique
     * 
     * @param string|null $competition Filtre par compétition (optionnel)
     * @return array ['current' => int, 'best' => int]
     */
    private function calculateStreaks($competition = null): array
    {
        // Récupérer toutes les prédictions terminées (won ou lost), triées par date décroissante
        $query = DB::table('predictions')
            ->where('is_published', true)
            ->whereIn('status', ['won', 'lost'])
            ->orderBy('match_date', 'desc');

        // Filtrer par compétition si fourni
        if ($competition && $competition !== 'all') {
            $query->where('competition', $competition);
        }

        $predictions = $query->get();

        if ($predictions->isEmpty()) {
            return ['current' => 0, 'best' => 0];
        }

        // Calculer current_streak : victoires consécutives depuis le plus récent
        $currentStreak = 0;
        foreach ($predictions as $prediction) {
            if ($prediction->status === 'won') {
                $currentStreak++;
            } else {
                // Dès qu'on rencontre une défaite, on s'arrête
                break;
            }
        }

        // Calculer best_streak : la plus longue série de victoires dans tout l'historique
        // On trie maintenant du plus ancien au plus récent pour parcourir chronologiquement
        $queryForBest = DB::table('predictions')
            ->where('is_published', true)
            ->whereIn('status', ['won', 'lost'])
            ->orderBy('match_date', 'asc');

        // Filtrer par compétition si fourni
        if ($competition && $competition !== 'all') {
            $queryForBest->where('competition', $competition);
        }

        $predictionsForBest = $queryForBest->get();
        $bestStreak = 0;
        $tempStreak = 0;

        foreach ($predictionsForBest as $prediction) {
            if ($prediction->status === 'won') {
                $tempStreak++;
                if ($tempStreak > $bestStreak) {
                    $bestStreak = $tempStreak;
                }
            } else {
                // Une défaite interrompt la série
                $tempStreak = 0;
            }
        }

        return [
            'current' => $currentStreak,
            'best' => $bestStreak,
        ];
    }

    /**
     * Convertir un match API-Football en format prédiction
     */
    private function convertMatchToPrediction(array $match, Carbon $date): ?array
    {
        // Gérer les deux formats possibles : directement sport_event ou dans schedules
        $sportEvent = $match['sport_event'] ?? $match;
        
        if (!isset($sportEvent['competitors']) || count($sportEvent['competitors']) < 2) {
            return null;
        }

        $homeTeam = $sportEvent['competitors'][0] ?? null;
        $awayTeam = $sportEvent['competitors'][1] ?? null;

        if (!$homeTeam || !$awayTeam) {
            return null;
        }

        $competition = $sportEvent['sport_event_context']['competition']['name'] ?? 'Unknown';
        $competitionId = $sportEvent['sport_event_context']['competition']['id'] ?? '';
        $country = $sportEvent['sport_event_context']['category']['name'] ?? 'Unknown';
        
        $matchDate = isset($sportEvent['start_time']) 
            ? Carbon::parse($sportEvent['start_time']) 
            : $date;

        // Générer une prédiction basique (vous pouvez améliorer avec l'algorithme complet)
        return [
            'id' => 'api_' . ($sportEvent['id'] ?? uniqid()),
            'match_id' => $sportEvent['id'] ?? '',
            'home_team' => $homeTeam['name'] ?? 'Home',
            'away_team' => $awayTeam['name'] ?? 'Away',
            'home_team_id' => $homeTeam['id'] ?? '',
            'away_team_id' => $awayTeam['id'] ?? '',
            'competition' => $competition,
            'competition_id' => $competitionId,
            'country' => $country,
            'match_date' => $matchDate->format('Y-m-d H:i:s'),
            'match_time' => $matchDate->format('H:i'),
            'home_score' => null,
            'away_score' => null,
            'bet_type' => '1X2',
            'prediction' => '1', // Prédiction basique
            'odds' => 1.85,
            'confidence_stars' => 2,
            'status' => 'pending',
            'is_premium' => false,
            'is_published' => true,
            'published_at' => now()->format('Y-m-d H:i:s'),
            'score_form' => 0,
            'score_h2h' => 0,
            'score_home_away' => 0,
            'score_league' => 0,
            'score_goals' => 0,
            'score_time' => 0,
            'total_score' => 50,
            'analysis_details' => null,
        ];
    }

    /**
     * Formater une prédiction depuis un tableau
     */
    private function formatPredictionFromArray(array $prediction, bool $isPremium): array
    {
        $isLocked = isset($prediction['is_premium']) && $prediction['is_premium'] && !$isPremium;

        // Déterminer le statut du match
        $matchStatus = $prediction['status'] ?? 'NS'; // Utiliser le statut si disponible
        if ($matchStatus === 'NS' || !isset($prediction['status'])) {
            // Si pas de statut, déterminer depuis les scores et la date
            if (isset($prediction['home_score']) && isset($prediction['away_score']) 
                && $prediction['home_score'] !== null && $prediction['away_score'] !== null) {
                if (isset($prediction['match_date'])) {
                    $matchDate = \Carbon\Carbon::parse($prediction['match_date']);
                    $now = \Carbon\Carbon::now();
                    $diffHours = $matchDate->diffInHours($now, false);
                    
                    if ($diffHours >= 0 && $diffHours <= 3) {
                        $matchStatus = 'live';
                    } elseif ($diffHours > 3) {
                        $matchStatus = 'FT';
                    }
                } else {
                    $matchStatus = 'live'; // Si scores présents sans date, supposer en direct
                }
            } elseif (isset($prediction['match_date'])) {
                $matchDate = \Carbon\Carbon::parse($prediction['match_date']);
                if ($matchDate->isPast()) {
                    $matchStatus = 'FT';
                } else {
                    $matchStatus = 'NS';
                }
            }
        }

        return [
            'id' => $prediction['id'] ?? 0,
            'match' => [
                'id' => $prediction['match_id'] ?? '',
                'home_team' => $prediction['home_team'] ?? '',
                'away_team' => $prediction['away_team'] ?? '',
                'home_team_id' => $prediction['home_team_id'] ?? '',
                'away_team_id' => $prediction['away_team_id'] ?? '',
                'home_team_logo' => $prediction['home_team_logo'] ?? null,
                'away_team_logo' => $prediction['away_team_logo'] ?? null,
                'competition' => $prediction['competition'] ?? '',
                'competition_id' => $prediction['competition_id'] ?? '',
                'competition_logo' => $prediction['competition_logo'] ?? null,
                'country' => $prediction['country'] ?? '',
                'match_date' => $prediction['match_date'] ?? '',
                'match_time' => $prediction['match_time'] ?? '',
                'home_score' => $prediction['home_score'] ?? null,
                'away_score' => $prediction['away_score'] ?? null,
                'status' => $matchStatus,
            ],
            'bet_type' => $prediction['bet_type'] ?? '1X2',
            'prediction' => $isLocked ? null : ($prediction['prediction'] ?? null),
            'odds' => $isLocked ? null : ($prediction['odds'] ?? null),
            'confidence_stars' => $prediction['confidence_stars'] ?? 0,
            'status' => $prediction['status'] ?? 'pending',
            'is_correct' => null,
            'is_premium' => $prediction['is_premium'] ?? false,
            'is_locked' => $isLocked,
            'published_at' => $prediction['published_at'] ?? now()->format('Y-m-d H:i:s'),
            'scores' => !$isLocked ? [
                'form' => $prediction['score_form'] ?? 0,
                'h2h' => $prediction['score_h2h'] ?? 0,
                'home_away' => $prediction['score_home_away'] ?? 0,
                'league' => $prediction['score_league'] ?? 0,
                'goals' => $prediction['score_goals'] ?? 0,
                'time' => $prediction['score_time'] ?? 0,
                'total' => $prediction['total_score'] ?? 0,
            ] : null,
            'analysis_details' => !$isLocked && isset($prediction['analysis_details']) 
                ? (is_string($prediction['analysis_details']) 
                    ? json_decode($prediction['analysis_details'], true) 
                    : $prediction['analysis_details'])
                : null,
            // Données enrichies depuis API-Football
            'api_statistics' => $prediction['api_statistics'] ?? null,
            'api_summary' => $prediction['api_summary'] ?? null,
            'bookmaker_odds' => $prediction['bookmaker_odds'] ?? null, // Odds des bookmakers
        ];
    }

    /**
     * Construire une prédiction enrichie depuis les données de l'API
     */
    private function buildPredictionFromApi(
        array $sportEvent,
        ?array $matchSummary,
        ?array $matchStatistics,
        $dbPrediction,
        bool $isPremium
    ): array {
        $competitors = $sportEvent['competitors'] ?? [];
        $homeTeam = $competitors[0] ?? null;
        $awayTeam = $competitors[1] ?? null;

        $competition = $sportEvent['sport_event_context']['competition']['name'] ?? 'Unknown';
        $competitionId = $sportEvent['sport_event_context']['competition']['id'] ?? '';
        $country = $sportEvent['sport_event_context']['category']['name'] ?? 'Unknown';
        
        $matchDate = isset($sportEvent['start_time']) 
            ? Carbon::parse($sportEvent['start_time']) 
            : Carbon::now();

        // Extraire les scores si disponibles
        $homeScore = null;
        $awayScore = null;
        $status = 'pending';

        if (isset($matchSummary['sport_event_status'])) {
            $eventStatus = $matchSummary['sport_event_status'];
            $homeScore = $eventStatus['home_score'] ?? null;
            $awayScore = $eventStatus['away_score'] ?? null;
            
            if ($homeScore !== null && $awayScore !== null) {
                $status = $eventStatus['status'] === 'closed' ? 'finished' : 'live';
            }
        }

        // Récupérer les logos depuis l'API ou la base de données
        $homeTeamLogo = $dbPrediction ? ($dbPrediction->home_team_logo ?? null) : ($homeTeam['logo'] ?? null);
        $awayTeamLogo = $dbPrediction ? ($dbPrediction->away_team_logo ?? null) : ($awayTeam['logo'] ?? null);
        $competitionLogo = $dbPrediction ? ($dbPrediction->competition_logo ?? null) : ($sportEvent['sport_event_context']['competition']['logo'] ?? null);

        // Utiliser les données de la base si disponibles, sinon créer depuis l'API
        $predictionData = [
            'id' => $dbPrediction ? $dbPrediction->id : ('api_' . $sportEvent['id']),
            'match_id' => $sportEvent['id'] ?? '',
            'home_team' => $homeTeam['name'] ?? 'Home',
            'away_team' => $awayTeam['name'] ?? 'Away',
            'home_team_id' => $homeTeam['id'] ?? '',
            'away_team_id' => $awayTeam['id'] ?? '',
            'home_team_logo' => $homeTeamLogo,
            'away_team_logo' => $awayTeamLogo,
            'competition' => $competition,
            'competition_id' => $competitionId,
            'competition_logo' => $competitionLogo,
            'country' => $country,
            'match_date' => $matchDate->format('Y-m-d H:i:s'),
            'match_time' => $matchDate->format('H:i'),
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'status' => $status,
            'bet_type' => $dbPrediction ? $dbPrediction->bet_type : '1X2',
            'prediction' => $dbPrediction ? $dbPrediction->prediction : '1',
            'odds' => $dbPrediction ? $dbPrediction->odds : 1.85,
            'confidence_stars' => $dbPrediction ? $dbPrediction->confidence_stars : 2,
            'is_premium' => $dbPrediction ? (bool)$dbPrediction->is_premium : false,
            'is_published' => true,
            'published_at' => $dbPrediction ? $dbPrediction->published_at : now()->format('Y-m-d H:i:s'),
            'score_form' => $dbPrediction ? $dbPrediction->score_form : 0,
            'score_h2h' => $dbPrediction ? $dbPrediction->score_h2h : 0,
            'score_home_away' => $dbPrediction ? $dbPrediction->score_home_away : 0,
            'score_league' => $dbPrediction ? $dbPrediction->score_league : 0,
            'score_goals' => $dbPrediction ? $dbPrediction->score_goals : 0,
            'score_time' => $dbPrediction ? $dbPrediction->score_time : 0,
            'total_score' => $dbPrediction ? $dbPrediction->total_score : 50,
            'analysis_details' => $dbPrediction ? $dbPrediction->analysis_details : null,
        ];

        // Enrichir avec les statistiques de l'API si disponibles
        if ($matchStatistics && isset($matchStatistics['statistics'])) {
            $predictionData['api_statistics'] = $matchStatistics['statistics'];
        }

        // Enrichir avec le résumé si disponible
        if ($matchSummary) {
            $predictionData['api_summary'] = [
                'venue' => $matchSummary['sport_event']['venue'] ?? null,
                'coverage' => $matchSummary['sport_event']['coverage'] ?? null,
            ];
        }

        // Enrichir avec les odds des bookmakers si disponibles (via OddsApiService)
        try {
            $oddsService = app(\App\Services\OddsApiService::class);
            $matchOdds = $oddsService->getMatchOdds('soccer', $sportEvent['id']);
            
            if ($matchOdds && isset($matchOdds['data'])) {
                $predictionData['bookmaker_odds'] = $matchOdds['data'];
            }
        } catch (\Exception $e) {
            // Si les odds ne sont pas disponibles, continuer sans
            Log::debug("Odds non disponibles pour le match {$sportEvent['id']}: " . $e->getMessage());
        }

        return $this->formatPredictionFromArray($predictionData, $isPremium);
    }

    /**
     * Construire une prédiction enrichie depuis les données API-Football
     */
    private function buildPredictionFromApiFootball(array $fixture, $dbPrediction, bool $isPremium): array
    {
        $info    = $fixture['fixture'] ?? [];
        $teams   = $fixture['teams'] ?? [];
        $goals   = $fixture['goals'] ?? [];
        $league  = $fixture['league'] ?? [];
        $status  = $info['status'] ?? [];

        $matchDate  = Carbon::parse($info['date'] ?? now());
        $homeScore  = $goals['home'];
        $awayScore  = $goals['away'];
        $matchStatus = match ($status['short'] ?? 'NS') {
            'FT', 'AET', 'PEN' => 'finished',
            '1H', '2H', 'ET', 'HT' => 'live',
            default => 'pending',
        };

        $predictionData = [
            'id'               => $dbPrediction ? $dbPrediction->id : ('api_' . ($info['id'] ?? '')),
            'match_id'         => (string) ($info['id'] ?? ''),
            'home_team'        => $teams['home']['name'] ?? 'Home',
            'away_team'        => $teams['away']['name'] ?? 'Away',
            'home_team_id'     => $teams['home']['id'] ?? '',
            'away_team_id'     => $teams['away']['id'] ?? '',
            'home_team_logo'   => $teams['home']['logo'] ?? null,
            'away_team_logo'   => $teams['away']['logo'] ?? null,
            'competition'      => $league['name'] ?? 'Unknown',
            'competition_id'   => (string) ($league['id'] ?? ''),
            'competition_logo' => $league['logo'] ?? null,
            'country'          => $league['country'] ?? 'Unknown',
            'match_date'       => $matchDate->format('Y-m-d H:i:s'),
            'match_time'       => $matchDate->format('H:i'),
            'home_score'       => $homeScore,
            'away_score'       => $awayScore,
            'status'           => $matchStatus,
            'bet_type'         => $dbPrediction ? $dbPrediction->bet_type : '1X2',
            'prediction'       => $dbPrediction ? $dbPrediction->prediction : '1',
            'odds'             => $dbPrediction ? $dbPrediction->odds : 1.85,
            'confidence_stars' => $dbPrediction ? $dbPrediction->confidence_stars : 2,
            'is_premium'       => $dbPrediction ? (bool) $dbPrediction->is_premium : false,
            'is_published'     => true,
            'published_at'     => $dbPrediction ? $dbPrediction->published_at : now()->format('Y-m-d H:i:s'),
            'score_form'       => $dbPrediction ? $dbPrediction->score_form : 0,
            'score_h2h'        => $dbPrediction ? $dbPrediction->score_h2h : 0,
            'score_home_away'  => $dbPrediction ? $dbPrediction->score_home_away : 0,
            'score_league'     => $dbPrediction ? $dbPrediction->score_league : 0,
            'score_goals'      => $dbPrediction ? $dbPrediction->score_goals : 0,
            'score_time'       => $dbPrediction ? $dbPrediction->score_time : 0,
            'total_score'      => $dbPrediction ? $dbPrediction->total_score : 50,
            'analysis_details' => $dbPrediction ? $dbPrediction->analysis_details : null,
            'venue'            => $info['venue']['name'] ?? null,
        ];

        return $this->formatPredictionFromArray($predictionData, $isPremium);
    }

    /**
     * Obtenir les stats pour une période donnée
     */
    private function getPeriodStats($startDate, $endDate, $competition = null)
    {
        $query = DB::table('predictions')
            ->where('is_published', true)
            ->whereBetween('match_date', [$startDate, $endDate]);

        // Filtrer par compétition si fourni
        if ($competition && $competition !== 'all') {
            $query->where('competition', $competition);
        }

        $total = (clone $query)
            ->where('status', '!=', 'pending')
            ->count();

        $correct = (clone $query)
            ->where('status', 'won')
            ->count();

        $incorrect = (clone $query)
            ->where('status', 'lost')
            ->count();

        $successRate = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'correct' => $correct,
            'incorrect' => $incorrect,
            'success_rate' => $successRate,
        ];
    }

    /**
     * Génère une prédiction basique pour un match
     */
    protected function generateBasicPrediction(array $match, bool $isPremium, $selectedDate): ?array
    {
        try {
            return [
                'id' => rand(10000, 99999),
                'match_id' => $match['id'] ?? 'unknown',
                'home_team' => $match['home_team'] ?? 'Unknown',
                'away_team' => $match['away_team'] ?? 'Unknown',
                'competition' => $match['competition'] ?? 'Unknown',
                'competition_id' => $match['competition_id'] ?? 0,
                'country' => $match['country'] ?? 'Unknown',
                'match_date' => $match['match_date'] ?? $selectedDate->format('Y-m-d'),
                'match_time' => '15:00', // Heure par défaut
                'bet_type' => '1X2',
                'prediction' => ['1', 'X', '2'][rand(0, 2)], // Aléatoire pour la démo
                'odds' => number_format(rand(140, 350) / 100, 2),
                'confidence_stars' => rand(1, 4),
                'is_premium' => $isPremium && rand(0, 1),
                'is_published' => true,
                'total_score' => rand(50, 85),
                'status' => 'pending',
                'analysis_details' => json_encode([
                    'reasoning' => 'Prédiction générée automatiquement - Analyse complète à venir',
                    'algorithm_version' => 'basic_v1',
                    'generated_at' => now()->toIso8601String(),
                ]),
                'published_at' => now(),
                'source' => 'generated',
            ];
        } catch (\Exception $e) {
            Log::error("Erreur génération prédiction basique: " . $e->getMessage());
            return null;
        }
    }
}
