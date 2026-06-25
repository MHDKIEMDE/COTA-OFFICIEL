<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CouponBuilderService;
use App\Services\FootballApiService;
use App\Services\PredictionAlgorithmService;
use App\Services\PredictionSelectionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PredictionController extends Controller
{
    public function __construct(
        private readonly FootballApiService $footballApi,
        private readonly PredictionAlgorithmService $predictionAlgorithm,
        private readonly CouponBuilderService $couponBuilder,
        private readonly PredictionSelectionService $selection,
    ) {}

    /**
     * Statut premium effectif. COTA_DISABLE_LOCKS=true (dev) → tout le monde premium.
     */
    private function resolvePremium(mixed $user): bool
    {
        if (config('cota.disable_locks', false)) {
            return true;
        }

        return (bool) ($user?->is_premium);
    }

    /**
     * Récupérer les pronostics du jour
     * OPTIMISÉ: Utilise d'abord la base de données (rapide), puis API-Football si nécessaire
     * Accessible sans authentification (mode invité)
     *
     * Cache: 24h (clé versionnée, invalidée à chaque génération de prédictions)
     */
    public function today(Request $request)
    {
        // Utiliser auth()->user() pour permettre l'accès invité
        $user = auth('sanctum')->user();
        $isPremium = $this->resolvePremium($user);

        // Accepter un paramètre date (format: YYYY-MM-DD) ou utiliser aujourd'hui par défaut
        $dateParam = $request->query('date');
        $selectedDate = $dateParam
            ? Carbon::parse($dateParam)->startOfDay()
            : Carbon::today();

        $dateString = $selectedDate->format('Y-m-d');
        $competition = $request->query('competition', 'all');

        // Clé de cache versionnée : la version est incrémentée par GenerateAllPredictionsJob,
        // ce qui invalide automatiquement le cache à chaque nouvelle génération (08h/20h)
        $cacheVersion = (int) Cache::get('predictions_cache_version', 1);
        $cacheKey = "predictions_today_{$dateString}_{$competition}_".($isPremium ? 'premium' : 'free')."_v{$cacheVersion}";
        $cacheTtl = 86400; // 24 heures

        // Vérifier le cache
        $cached = Cache::get($cacheKey);
        if ($cached) {
            Log::info("📦 Cache hit pour predictions du {$dateString}");

            return response()->json($cached);
        }

        // OPTIMISATION: Essayer d'abord la base de données (BEAUCOUP plus rapide)
        Log::info("📊 Vérification des prédictions en base de données pour le {$dateString}");

        // Construire un index de priorité par paire (competition, country)
        // Les ligues populaires sont prioritaires, les autres viennent après
        $popularPairs = config('football-api.popular_leagues', []);
        $popularByName = [];
        foreach ($popularPairs as $league) {
            $key = $league['name'].'|'.$league['country'];
            $popularByName[$key] = $league['tier'];
        }

        // Noms des ligues populaires pour tri prioritaire
        $popularNames = array_column($popularPairs, 'name');

        // Exclure les ligues de bas niveau sans données fiables
        $excludedLigues = ['Friendl', 'Women', 'Female', 'Youth', 'U17', 'U18', 'U19', 'U20', 'U21', 'U23', 'Reserve', 'Amateur'];

        // F-04 : tier_max optionnel (défaut 3 — ligues reconnues uniquement)
        $tierMax = (int) $request->get('tier_max', 3);

        // Seuil de base : 40 pts. Le filtrage tierce (élever à 52 si absent) se fait en PHP.
        $query = DB::table('predictions')
            ->where('is_published', true)
            ->whereDate('match_date', $dateString)
            ->where('total_score', '>', 40)
            ->where(function ($q) use ($tierMax) {
                // F-05 : ne montrer que les ligues tier 1–tierMax (ou tier non renseigné = ancienne donnée)
                $q->where('league_tier', '<=', $tierMax)
                    ->orWhereNull('league_tier')
                    ->orWhere('league_tier', 99);
            })
            ->where(function ($q) use ($selectedDate) {
                // Exclure les matchs déjà commencés — uniquement pour la date du jour,
                // sinon les matchs de demain plus tôt que l'heure courante seraient masqués
                if ($selectedDate->isToday()) {
                    $now = Carbon::now()->format('H:i:s');
                    $q->whereNull('match_time')
                        ->orWhere('match_time', '>', $now);
                }
            })
            ->where(function ($q) use ($excludedLigues) {
                foreach ($excludedLigues as $pattern) {
                    $q->where('competition', 'not like', '%'.$pattern.'%');
                }
            })
            // F-05 : tri par tier croissant (tier 1 en premier), puis confiance, puis score
            ->orderByRaw('CASE WHEN league_tier IS NULL OR league_tier = 99 THEN 4 ELSE league_tier END ASC')
            ->orderBy('confidence_stars', 'desc')
            ->orderBy('total_score', 'desc')
            ->orderBy('match_time', 'asc');

        // Filtrer par compétition si fourni
        if ($competition && $competition !== 'all') {
            $query->where('competition', $competition);
        }

        // Toutes les prédictions sont accessibles gratuitement
        $dbPredictions = $query->get()->filter(function ($p) {
            $score = (float) ($p->total_score ?? 0);
            $analysis = $p->analysis_details ? json_decode($p->analysis_details, true) : [];
            $tp = $analysis['third_party'] ?? null;
            // Score ≥ 52 : toujours accepté
            if ($score > 52) {
                return true;
            }

            // Score 40–52 : accepté uniquement si tierce présente et non contradictoire
            return $tp !== null
                && ! empty($tp['prediction'])
                && ($tp['agreement'] ?? '') !== 'contradicts';
        })->values();

        // Si on a au moins 1 prédiction dans les ligues populaires, les retourner
        if ($dbPredictions->count() >= 1) {
            Log::info('✅ '.$dbPredictions->count().' prédictions trouvées en base de données (source: database) - RÉPONSE RAPIDE');

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

        // Base vide — distinguer Niveau 2 (matchs analysés) vs Niveau 3 (aucun match)
        $matchesAnalyzedCount = DB::table('matches')
            ->whereDate('match_date', $dateString)
            ->count();

        $emptyStatus = $matchesAnalyzedCount > 0
            ? 'no_predictions_above_threshold'
            : 'no_matches_today';

        Log::warning("Predictions: empty state ({$emptyStatus}) pour {$dateString}, matchs={$matchesAnalyzedCount}");

        $emptyState = Cache::get('empty_state_data');

        // Cold start : déclencher le calcul immédiatement si le cache est absent
        if ($emptyState === null) {
            \App\Jobs\CacheEmptyStateDataJob::dispatch();
            $emptyState = [
                'win_rate_30d' => 0.0,
                'wins_30d' => 0,
                'total_30d' => 0,
                'last_wins' => [],
                'next_match_at' => null,
            ];
        }

        // Prochain slot de génération (08h ou 20h UTC)
        $now = now()->utc();
        $slots = [
            $now->copy()->setTime(8, 0, 0),
            $now->copy()->setTime(20, 0, 0),
        ];
        $nextSlot = collect($slots)
            ->map(fn ($s) => $s->isPast() ? $s->addDay() : $s)
            ->sortBy(fn ($s) => $s->timestamp)
            ->first();

        $responseData = [
            'success' => true,
            'status' => $emptyStatus,
            'matches_analyzed' => $matchesAnalyzedCount,
            'next_predictions_at' => $nextSlot->toIso8601String(),
            'data' => [],
            'source' => 'database',
            'empty_state' => [
                'win_rate_30d' => $emptyState['win_rate_30d'],
                'wins_30d' => $emptyState['wins_30d'],
                'total_30d' => $emptyState['total_30d'],
                'last_wins' => $emptyState['last_wins'],
                'next_match_at' => $nextSlot->toIso8601String(),
            ],
            'cached_at' => now()->toIso8601String(),
        ];

        Cache::put($cacheKey, $responseData, 60);

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
            ->where(fn ($q) => $q->where('league_tier', '<=', 3)
                ->orWhereIn('competition', $popularLeagueNames)
                ->orWhereNull('league_tier')
                ->orWhere('league_tier', 99)
            )
            ->orderBy('league_tier', 'asc')
            ->orderBy('confidence_stars', 'desc')
            ->orderBy('match_time', 'asc');

        // Filtrer par compétition si fourni
        $competition = $request->query('competition');
        if ($competition && $competition !== 'all') {
            $query->where('competition', $competition);
        }

        // Toutes les prédictions accessibles gratuitement
        $predictions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $predictions->map(function ($prediction) use ($isPremium) {
                return $this->formatPrediction($prediction, $isPremium);
            }),
            'source' => 'database',
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
        $isPremium = $this->resolvePremium($user);

        // Essayer d'abord de récupérer depuis la base pour avoir le match_id
        $prediction = DB::table('predictions')
            ->where('id', $id)
            ->first();

        // Si pas en base, essayer avec l'ID directement (format: api_matchId)
        $matchId = null;
        if (! $prediction && strpos($id, 'api_') === 0) {
            $matchId = str_replace('api_', '', $id);
        } elseif ($prediction) {
            $matchId = $prediction->match_id;
        }

        if (! $matchId) {
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

            if (! $fixtureResponse || empty($fixtureResponse['response'])) {
                // Fallback sur la base de données
                if ($prediction) {
                    return response()->json([
                        'success' => true,
                        'data' => $this->formatPrediction($prediction, $isPremium),
                        'source' => 'database',
                    ]);
                }

                return response()->json(['success' => false, 'message' => 'Détails du match non disponibles'], 404);
            }

            $fixture = $fixtureResponse['response'][0];
            $homeTeam = $fixture['teams']['home'] ?? null;
            $awayTeam = $fixture['teams']['away'] ?? null;

            if (! $homeTeam || ! $awayTeam) {
                throw new \Exception('Équipes manquantes dans la réponse API');
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
            Log::error("❌ Erreur lors de la récupération depuis l'API: ".$e->getMessage(), [
                'match_id' => $matchId,
                'error' => $e->getTraceAsString(),
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
                'message' => 'Erreur lors de la récupération des détails: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer l'historique des pronostics
     */
    // Historique public — accessible sans authentification
    public function historyPublic(Request $request)
    {
        $user = $request->user();
        $isPremium = $this->resolvePremium($user);

        $page = $request->input('page', 1);
        $perPage = 15;
        $status = $request->input('status');
        $competition = $request->input('competition');

        $query = DB::table('predictions')
            ->where('is_published', true)
            ->orderBy('match_date', 'desc');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        if ($competition && $competition !== 'all') {
            $query->where('competition', $competition);
        }
        if ($status === 'pending') {
            $query->where('match_date', '>=', Carbon::now()->startOfDay());
        } else {
            $query->where('match_date', '>=', Carbon::now()->subDays(90)->startOfDay());
        }

        $total = $query->count();
        $predictions = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'success' => true,
            'data' => $predictions->map(fn ($p) => $this->formatPrediction($p, $isPremium)),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_more' => $page < ceil($total / $perPage),
            ],
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $isPremium = $this->resolvePremium($user);

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

        // Pending = matchs futurs, sinon 90 jours en arrière
        if ($status === 'pending') {
            $query->where('match_date', '>=', Carbon::now()->startOfDay());
        } else {
            $query->where('match_date', '>=', Carbon::now()->subDays(90)->startOfDay());
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
    /**
     * GET /api/stats/accuracy  (public, cache 1h)
     * Chiffres affichés sur le slide 2 de l'onboarding :
     *   - accuracy_30d  : taux de réussite sur 30 jours (ex: 74)
     *   - roi_season    : ROI depuis le début de la saison en % (ex: +18)
     *   - total_30d     : nombre de prédictions terminées sur 30 jours
     */
    public function accuracy(): JsonResponse
    {
        $data = Cache::remember('stats:accuracy:public', 3600, function () {
            $now = Carbon::now();
            $month_ago = $now->copy()->subDays(30);
            $season = Carbon::createFromDate($now->month >= 7 ? $now->year : $now->year - 1, 7, 1);

            // ── Taux de réussite 30 jours ─────────────────────────────────────
            $month = DB::table('predictions')
                ->where('is_published', true)
                ->whereIn('status', ['won', 'lost'])
                ->whereBetween('match_date', [$month_ago, $now])
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = \'won\' THEN 1 ELSE 0 END) as won')
                ->first();

            $total30 = (int) ($month->total ?? 0);
            $won30 = (int) ($month->won ?? 0);
            $accuracy = $total30 > 0 ? (int) round($won30 / $total30 * 100) : 0;

            // ── ROI saison (mise fictive 1 unité par pari, cote réelle) ───────
            $season_preds = DB::table('predictions')
                ->where('is_published', true)
                ->whereIn('status', ['won', 'lost'])
                ->where('match_date', '>=', $season)
                ->select('status', 'odds')
                ->get();

            $stakes = $season_preds->count();         // 1 unité par pari
            $returns = $season_preds
                ->where('status', 'won')
                ->sum('odds');                          // retour brut des paris gagnés

            $roi = $stakes > 0
                ? (int) round(($returns - $stakes) / $stakes * 100)
                : 0;

            // ── T-02 : Taux de réussite par étoiles ──────────────────────────
            $byStars = [];
            foreach ([1, 2, 3, 4] as $stars) {
                $row = DB::table('predictions')
                    ->where('is_published', true)
                    ->where('confidence_stars', $stars)
                    ->whereIn('status', ['won', 'lost'])
                    ->whereBetween('match_date', [$month_ago, $now])
                    ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = \'won\' THEN 1 ELSE 0 END) as won')
                    ->first();
                $t = (int) ($row->total ?? 0);
                $w = (int) ($row->won ?? 0);
                $byStars[$stars] = [
                    'total' => $t,
                    'won' => $w,
                    'accuracy' => $t > 0 ? (int) round($w / $t * 100) : null,
                ];
            }

            return [
                'accuracy_30d' => $accuracy,
                'roi_season' => $roi,
                'total_30d' => $total30,
                'by_stars' => $byStars,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * GET /api/user/roi  (auth:sanctum)
     * ROI personnel : si l'utilisateur avait misé 1 000 FCFA sur chaque prédiction
     * publiée pendant la période où il était inscrit, combien aurait-il gagné/perdu ?
     */
    public function personalRoi(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = Cache::remember("personal_roi:{$user->id}", 600, function () use ($user) {
            $since = $user->created_at ?? Carbon::now()->subDays(30);

            $preds = DB::table('predictions')
                ->where('is_published', true)
                ->whereIn('status', ['won', 'lost'])
                ->where('match_date', '>=', $since)
                ->select('status', 'odds', 'confidence_stars', 'match_date')
                ->orderBy('match_date')
                ->get();

            $stake = 1000; // FCFA par pari
            $totalStaked = $preds->count() * $stake;
            $totalReturns = (int) round($preds->where('status', 'won')->sum('odds') * $stake);
            $netGain = $totalReturns - $totalStaked;
            $roi = $totalStaked > 0 ? round($netGain / $totalStaked * 100, 1) : 0;

            // Courbe sparkline : gain cumulé semaine par semaine
            $sparkline = [];
            $cumulative = 0;
            foreach ($preds->groupBy(fn ($p) => Carbon::parse($p->match_date)->startOfWeek()->toDateString()) as $week => $group) {
                $weekReturn = (int) round($group->where('status', 'won')->sum('odds') * $stake);
                $weekStaked = $group->count() * $stake;
                $cumulative += ($weekReturn - $weekStaked);
                $sparkline[] = ['week' => $week, 'cumulative' => $cumulative];
            }

            return [
                'stake_per_bet' => $stake,
                'total_bets' => $preds->count(),
                'total_staked' => $totalStaked,
                'total_returns' => $totalReturns,
                'net_gain' => $netGain,
                'roi_pct' => $roi,
                'since' => $since->toDateString(),
                'sparkline' => $sparkline,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

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
        $cacheKey = 'welcome_combined_'.Carbon::today()->format('Y-m-d');

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
                'success' => true,
                'predictions' => $predictions->map(fn ($p) => $this->formatPrediction($p, false))->values(),
                'total_odds' => round($totalOdds, 2),
                'date' => Carbon::today()->toDateString(),
                'is_free' => true,
            ];
        });

        if (! $data) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune prédiction disponible pour le combiné de bienvenue aujourd\'hui',
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

        // Garder uniquement les compétitions avec une priorité connue (tier 1-3)
        // + les pays prioritaires pour éviter un JSON énorme
        $competitions = $competitions->filter(function ($item) use ($competitionPriorities, $countryPriorities) {
            $hasKnownCompetition = isset($competitionPriorities[$item->competition]);
            $hasKnownCountry = isset($countryPriorities[$item->country]);

            return $hasKnownCompetition || $hasKnownCountry;
        });

        // Trier par priorité combinée
        $sortedCompetitions = $competitions->sortBy(function ($item) use ($competitionPriorities, $countryPriorities) {
            $competitionPriority = $competitionPriorities[$item->competition] ?? 100;
            $countryPriority = $countryPriorities[$item->country] ?? 50;

            return $countryPriority * 1000 + $competitionPriority;
        });

        // Grouper par pays
        $groupedByCountry = $sortedCompetitions->groupBy('country')->map(function ($items, $country) use ($competitionPriorities) {
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
        $user = auth('sanctum')->user();
        $isPremium = $this->resolvePremium($user);

        $date = $request->input('date', Carbon::today()->toDateString());
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
            $totalOdds = $dbPredictions->reduce(fn ($carry, $p) => $carry * (float) $p->odds, 1.0);
            $avgConfidence = $dbPredictions->avg('total_score');

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $targetDate->toDateString(),
                    'total_odds' => round($totalOdds, 2),
                    'avg_confidence' => round($avgConfidence, 2),
                    'matches' => $dbPredictions->map(fn ($p) => $this->formatPrediction($p, $isPremium))->values()->all(),
                ],
            ]);
        }

        // ── DB vide : premium génère à la demande, free attend ──────────────
        if (! $isPremium) {
            return response()->json([
                'success' => false,
                'message' => 'Le combiné du jour sera disponible après la génération automatique',
            ], 404);
        }

        $cacheKey = 'combined_daily_premium_'.$targetDate->format('Y-m-d');

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

            if (! ($coupon['success'] ?? false)) {
                return $coupon;
            }

            // Convertir les picks en format PredictionModel compatible Flutter
            $matches = array_map(function (array $pick): array {
                return [
                    'id' => 0,
                    'match_id' => null,
                    'prediction_type' => $pick['type'] ?? '1X2',
                    'predicted_outcome' => $pick['prediction'] ?? '',
                    'odds' => $pick['odds'] ?? 1.0,
                    'confidence_score' => (int) ($pick['confidence'] ?? 0),
                    'confidence_stars' => $pick['stars'] ?? 1,
                    'is_premium' => $pick['is_premium'] ?? true,
                    'result' => null,
                    'created_at' => now()->toIso8601String(),
                    'match' => [
                        'id' => null,
                        'fixture_id' => null,
                        'home_team' => explode(' vs ', $pick['match'] ?? ' vs ')[0] ?? '',
                        'away_team' => explode(' vs ', $pick['match'] ?? ' vs ')[1] ?? '',
                        'home_team_logo' => null,
                        'away_team_logo' => null,
                        'league' => $pick['league'] ?? '',
                        'league_logo' => null,
                        'match_date' => $pick['date'] ?? now()->toIso8601String(),
                        'status' => 'NS',
                        'home_score' => null,
                        'away_score' => null,
                    ],
                ];
            }, $coupon['picks']);

            return [
                'success' => true,
                'data' => [
                    'date' => $targetDate->toDateString(),
                    'total_odds' => $coupon['total_odds'],
                    'avg_confidence' => $coupon['avg_confidence'],
                    'matches' => $matches,
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
        $isPremium = $this->resolvePremium($user);

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
        if (! empty($query)) {
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

        // Toutes les prédictions accessibles gratuitement
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
     * GET /api/predictions/coupon-history — public
     * Historique des coupons IA gagnants, visible par tous pour montrer les performances
     */
    public function couponHistory(Request $request)
    {
        $limit = min((int) $request->query('limit', 10), 30);
        $cacheKey = 'coupon_history_public_'.$limit;

        $data = Cache::remember($cacheKey, 1800, function () use ($limit) {
            return DB::table('combined_bets')
                ->whereIn('status', ['won', 'lost', 'partial'])
                ->where('is_published', true)
                ->orderByDesc('date')
                ->limit($limit)
                ->get()
                ->map(function ($c) {
                    $picks = is_string($c->details) ? (json_decode($c->details, true) ?? []) : [];

                    return [
                        'date' => $c->date,
                        'type' => $c->type,
                        'status' => $c->status,
                        'picks_count' => $c->predictions_count,
                        'total_odds' => $c->total_odds,
                        'potential_gain' => $c->potential_payout,
                        'won_count' => $c->won_count,
                        'lost_count' => $c->lost_count,
                        'picks' => array_map(fn ($p) => [
                            'match' => $p['match'] ?? '—',
                            'league' => $p['league'] ?? '',
                            'prediction' => $p['prediction'] ?? '',
                            'odds' => $p['odds'] ?? null,
                            'confidence' => $p['confidence'] ?? null,
                        ], $picks),
                    ];
                });
        });

        $stats = Cache::remember('coupon_history_stats', 3600, function () {
            $total = DB::table('combined_bets')->whereIn('status', ['won', 'lost'])->count();
            $won = DB::table('combined_bets')->where('status', 'won')->count();

            return [
                'total' => $total,
                'won' => $won,
                'win_rate' => $total > 0 ? round(($won / $total) * 100, 1) : 0,
            ];
        });

        return response()->json(['success' => true, 'stats' => $stats, 'data' => $data]);
    }

    /**
     * Coupon du jour — 3 variantes (T4 CDC v3.1)
     * GET /api/predictions/coupon
     *   prudent   (Free)    : cotes 1.20–1.80, total ~8–10
     *   equilibre (Premium) : meilleur global
     *   audacieux (Premium) : cotes ≥ 2.50, is_risky = true
     */
    public function coupon(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        $isPremium = $this->resolvePremium($user);
        $date = $request->query('date', Carbon::today()->toDateString());
        $cacheKey = 'coupon_v3_'.$date.'_'.($isPremium ? 'premium' : 'free');
        // TTL court : un match du coupon qui démarre doit en sortir rapidement.
        // Sans ça, le cache « jusqu'à minuit » figeait le coupon avec des matchs
        // déjà commencés (le filtre match_time > now n'était jamais réévalué).
        $ttl = 900; // 15 min

        $result = Cache::remember($cacheKey, $ttl, function () use ($date) {

            // Fenêtre élargie J→J+3 : plus de matchs disponibles → coupons plus
            // gros et cote totale plus élevée (kamikaze atteignable). On exclut
            // les matchs déjà commencés aujourd'hui (jouables uniquement à venir).
            $windowStart = Carbon::parse($date);
            $windowEnd = $windowStart->copy()->addDays(3)->endOfDay();

            $rows = DB::table('predictions')
                ->where('is_published', true)
                ->whereDate('match_date', '>=', $windowStart->toDateString())
                ->whereDate('match_date', '<=', $windowEnd->toDateString())
                ->where(function ($q) use ($windowStart) {
                    // Matchs d'aujourd'hui : uniquement ceux pas encore commencés.
                    // Matchs des jours suivants : tous conservés.
                    if ($windowStart->isToday()) {
                        $now = Carbon::now()->format('H:i:s');
                        $today = $windowStart->toDateString();
                        $q->whereDate('match_date', '>', $today)
                            ->orWhereNull('match_time')
                            ->orWhere('match_time', '>', $now);
                    }
                })
                ->orderByDesc('total_score')
                ->limit(100)
                ->get();

            if ($rows->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Aucune prédiction disponible pour le '.$date,
                    'date' => $date,
                    'prudent' => null,
                    'equilibre' => null,
                    'audacieux' => null,
                ];
            }

            // Déterminer si le plancher a été appliqué ce jour
            $pools = $this->selection->buildPools($rows->map(fn ($r) => (array) $r)->toArray());
            $floorApplied = $pools['floor_applied'];

            // Pool élargi pour les coupons par compétition majeure (Coupe du monde…) :
            // une grande compétition s'étale sur plusieurs jours, on prend J→J+1 à venir.
            // Fenêtre élargie (7 jours) : une grande compétition s'étale sur
            // plusieurs jours → on veut un coupon par journée à venir.
            // Compétitions vedettes (tournois internationaux majeurs) : on élargit
            // au-delà du tier ≤ 2 pour capter CAN, Copa, Euro… qui sont en tier 3.
            $featured = config('cota.coupon.featured_competitions', []);
            $majorRows = DB::table('predictions')
                ->where('is_published', true)
                ->where(function ($q) use ($featured) {
                    $q->where('league_tier', '<=', 2);
                    foreach ($featured as $pattern) {
                        $q->orWhere('competition', 'like', '%'.$pattern.'%');
                    }
                })
                ->where('match_date', '>=', Carbon::now())
                ->where('match_date', '<=', Carbon::now()->addDays(7)->endOfDay())
                ->orderByDesc('total_score')
                ->limit(200)
                ->get();

            $variants = $this->couponBuilder->buildAll($rows, $floorApplied, $majorRows);

            return [
                'success' => true,
                'date' => $date,
                'generated_at' => Carbon::now()->toIso8601String(),
                'floor_applied' => $floorApplied,
                'prudent' => $variants['prudent'],
                'equilibre' => $variants['equilibre'],
                'audacieux' => $variants['audacieux'],
                'kamikaze' => $variants['kamikaze'] ?? null,
                'competitions' => $variants['competitions'] ?? [],
            ];
        });

        // Gate premium : équilibré/audacieux/kamikaze verrouillés pour les non-premium
        if (! $isPremium && ($result['success'] ?? false)) {
            foreach (['equilibre', 'audacieux', 'kamikaze'] as $variant) {
                if (! empty($result[$variant])) {
                    $result[$variant]['picks'] = [];
                    $result[$variant]['is_locked'] = true;
                    $result[$variant]['lock_message'] = 'Passez Premium pour débloquer cette variante';
                }
            }
        }

        return response()->json($result);
    }

    /**
     * Formater un pronostic pour la réponse
     */
    private function extractOddsSource(mixed $analysisDetails): string
    {
        if (! $analysisDetails) {
            return 'estimated';
        }
        $details = is_string($analysisDetails) ? json_decode($analysisDetails, true) : $analysisDetails;

        return $details['odds_source'] ?? 'estimated';
    }

    /**
     * GET /api/predictions/coupon-tabs
     * Onglets de coupon configurés depuis le dashboard (label, sous-titre, ordre).
     * Permet au mobile d'afficher des libellés dynamiques éditables côté admin.
     */
    public function couponTabs(): JsonResponse
    {
        $tabs = Cache::remember('coupon_tabs_v1', 600, function () {
            return \App\Models\CouponTab::activeOrdered()
                ->get(['key', 'label', 'subtitle'])
                ->map(fn ($t) => [
                    'key' => $t->key,
                    'label' => $t->label,
                    'subtitle' => $t->subtitle,
                ])
                ->values()
                ->all();
        });

        return response()->json(['success' => true, 'data' => $tabs]);
    }

    /**
     * GET /api/predictions/worldcup-upcoming
     * Prochains matchs Coupe du monde à venir (J→J+3) avec prono publié.
     * Alimente le bandeau « Coupe du monde » de l'accueil : l'accueil n'affiche
     * que le jour exact, ce bandeau met en avant les affiches CDM des jours suivants.
     */
    public function worldCupUpcoming(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        $isPremium = $this->resolvePremium($user);
        $limit = min((int) $request->query('limit', 10), 20);

        $cacheKey = 'wc_upcoming_v1_'.($isPremium ? 'premium' : 'free');
        $result = Cache::remember($cacheKey, 900, function () use ($isPremium, $limit) {
            $rows = DB::table('predictions')
                ->where('is_published', true)
                ->where('competition', 'like', '%World Cup%')
                ->where('match_date', '>', Carbon::now())
                ->where('match_date', '<=', Carbon::now()->addDays(3)->endOfDay())
                ->orderBy('match_date', 'asc')
                ->orderByDesc('total_score')
                ->limit($limit)
                ->get();

            $data = $rows->map(fn ($p) => $this->formatPrediction($p, $isPremium))->values();

            return [
                'success' => true,
                'data' => $data,
                'meta' => ['count' => $data->count()],
            ];
        });

        return response()->json($result);
    }

    /**
     * Charge les marchés switchables d'une prédiction, groupés par catégorie.
     * Retourne un tableau prêt pour le sélecteur de marchés côté mobile.
     */
    private function loadMarkets($predictionId): array
    {
        $markets = \App\Models\PredictionMarket::where('prediction_id', $predictionId)
            ->orderByDesc('is_primary')
            ->orderByDesc('market_score')
            ->get();

        if ($markets->isEmpty()) {
            return [];
        }

        return $markets->map(fn ($m) => [
            'category' => $m->category,
            'bet_type' => $m->bet_type,
            'outcome' => $m->outcome,
            'market_selection' => $m->market_selection,
            'odds' => (float) $m->odds,
            'market_score' => (float) $m->market_score,
            'score_tier' => $m->score_tier,
            'active_side' => $m->active_side,
            'is_primary' => (bool) $m->is_primary,
            'is_risky' => (bool) $m->is_risky,
            'status' => $m->status,
        ])->values()->all();
    }

    private function formatPrediction($prediction, $isPremium)
    {
        // Toutes les prédictions sont librement accessibles — seul le coupon est premium
        $isLocked = false;

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
                'home_team_logo' => $prediction->home_team_logo
                    ?? ($prediction->home_team_id > 0 ? 'https://media.api-sports.io/football/teams/'.$prediction->home_team_id.'.png' : null),
                'away_team_logo' => $prediction->away_team_logo
                    ?? ($prediction->away_team_id > 0 ? 'https://media.api-sports.io/football/teams/'.$prediction->away_team_id.'.png' : null),
                'competition' => $prediction->competition,
                'competition_id' => $prediction->competition_id,
                'competition_logo' => $prediction->competition_logo
                    ?? ($prediction->competition_id > 0 ? 'https://media.api-sports.io/football/leagues/'.$prediction->competition_id.'.png' : null),
                'country' => $prediction->country,
                'match_date' => $prediction->match_date,
                'match_time' => $prediction->match_time,
                'home_score' => $prediction->home_score,
                'away_score' => $prediction->away_score,
                'status' => $matchStatus,
                'venue' => $prediction->venue ?? null,
                'referee' => $prediction->referee ?? null,
            ],
            'bet_type' => $prediction->bet_type,
            'prediction' => $isLocked ? null : $prediction->prediction,
            'odds_source' => $this->extractOddsSource($prediction->analysis_details),
            'odds' => $isLocked ? null : (function () use ($prediction) {
                $odds = (float) ($prediction->odds ?? 0);
                $source = $this->extractOddsSource($prediction->analysis_details);

                // Coupe du monde : vitrine — on affiche toute cote RÉELLE, même < 1.50
                // (exception à C-03). Les cotes estimées restent masquées.
                if (($prediction->competition ?? '') === 'World Cup') {
                    return ($odds > 0 && $source !== 'estimated') ? $odds : null;
                }

                // C-03 : cote < 1.50 → pas de valeur pour l'utilisateur
                if ($odds > 0 && $odds < 1.50) {
                    return null;
                }

                return $source === 'estimated' ? null : $odds;
            })(),
            'confidence_stars' => $prediction->confidence_stars,
            'status' => $prediction->status,
            'is_correct' => $prediction->status === 'won' ? true : ($prediction->status === 'lost' ? false : null),
            'is_premium' => $prediction->is_premium,
            'is_locked' => $isLocked,
            'published_at' => $prediction->published_at,
            'value_score' => $prediction->value_score,
            'kelly_fraction' => $prediction->kelly_fraction,
            'ev_positive' => (bool) $prediction->ev_positive,
            'sure_bet_level' => $prediction->sure_bet_level,
            // Marchés switchables (cascade multi-marchés)
            'markets' => $this->loadMarkets($prediction->id),
        ];

        // Ajouter les détails complets si non verrouillé
        if (! $isLocked) {
            $data['scores'] = [
                'form' => $prediction->score_form,
                'h2h' => $prediction->score_h2h,
                'home_away' => $prediction->score_home_away,
                'league' => $prediction->score_league,
                'goals' => $prediction->score_goals,
                'time' => $prediction->score_time,
                'total' => $prediction->total_score,
            ];

            $details = $prediction->analysis_details
                ? json_decode($prediction->analysis_details, true)
                : null;

            $data['analysis_details'] = $details;

            // Analyse texte lisible (notamment pour les pronos importés sans
            // breakdown des 9 critères : le mobile l'affiche à la place du tableau).
            $data['analysis_text'] = $prediction->analysis_text ?? null;

            // Exposer les données tierces directement au niveau racine pour le mobile
            $thirdParty = $details['third_party'] ?? null;
            $data['third_party'] = $thirdParty ? [
                'prediction' => $thirdParty['prediction'] ?? null,
                'home_win_pct' => $thirdParty['home_win_pct'] ?? null,
                'draw_pct' => $thirdParty['draw_pct'] ?? null,
                'away_win_pct' => $thirdParty['away_win_pct'] ?? null,
                'btts' => $thirdParty['btts'] ?? null,
                'over25' => $thirdParty['over25'] ?? null,
                'agreement' => $thirdParty['agreement'] ?? null,
            ] : null;
        }

        return $data;
    }

    /**
     * Calculer les séries de victoires (current_streak et best_streak)
     *
     * current_streak : Nombre de victoires consécutives depuis la prédiction la plus récente
     * best_streak : La plus longue série de victoires consécutives dans tout l'historique
     *
     * @param  string|null  $competition  Filtre par compétition (optionnel)
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
     * Formater une prédiction depuis un tableau
     */
    private function formatPredictionFromArray(array $prediction, bool $isPremium): array
    {
        // Toutes les prédictions sont librement accessibles — seul le coupon est premium
        $isLocked = false;

        // Déterminer le statut du match
        $matchStatus = $prediction['status'] ?? 'NS'; // Utiliser le statut si disponible
        if ($matchStatus === 'NS' || ! isset($prediction['status'])) {
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
            'scores' => ! $isLocked ? [
                'form' => $prediction['score_form'] ?? 0,
                'h2h' => $prediction['score_h2h'] ?? 0,
                'home_away' => $prediction['score_home_away'] ?? 0,
                'league' => $prediction['score_league'] ?? 0,
                'goals' => $prediction['score_goals'] ?? 0,
                'time' => $prediction['score_time'] ?? 0,
                'total' => $prediction['total_score'] ?? 0,
            ] : null,
            'analysis_details' => ! $isLocked && isset($prediction['analysis_details'])
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
            'id' => $dbPrediction ? $dbPrediction->id : ('api_'.$sportEvent['id']),
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
            'is_premium' => $dbPrediction ? (bool) $dbPrediction->is_premium : false,
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
            Log::debug("Odds non disponibles pour le match {$sportEvent['id']}: ".$e->getMessage());
        }

        return $this->formatPredictionFromArray($predictionData, $isPremium);
    }

    /**
     * Construire une prédiction enrichie depuis les données API-Football
     */
    private function buildPredictionFromApiFootball(array $fixture, $dbPrediction, bool $isPremium): array
    {
        $info = $fixture['fixture'] ?? [];
        $teams = $fixture['teams'] ?? [];
        $goals = $fixture['goals'] ?? [];
        $league = $fixture['league'] ?? [];
        $status = $info['status'] ?? [];

        $matchDate = Carbon::parse($info['date'] ?? now());
        $homeScore = $goals['home'];
        $awayScore = $goals['away'];
        $matchStatus = match ($status['short'] ?? 'NS') {
            'FT', 'AET', 'PEN' => 'finished',
            '1H', '2H', 'ET', 'HT' => 'live',
            default => 'pending',
        };

        $predictionData = [
            'id' => $dbPrediction ? $dbPrediction->id : ('api_'.($info['id'] ?? '')),
            'match_id' => (string) ($info['id'] ?? ''),
            'home_team' => $teams['home']['name'] ?? 'Home',
            'away_team' => $teams['away']['name'] ?? 'Away',
            'home_team_id' => $teams['home']['id'] ?? '',
            'away_team_id' => $teams['away']['id'] ?? '',
            'home_team_logo' => $teams['home']['logo'] ?? null,
            'away_team_logo' => $teams['away']['logo'] ?? null,
            'competition' => $league['name'] ?? 'Unknown',
            'competition_id' => (string) ($league['id'] ?? ''),
            'competition_logo' => $league['logo'] ?? null,
            'country' => $league['country'] ?? 'Unknown',
            'match_date' => $matchDate->format('Y-m-d H:i:s'),
            'match_time' => $matchDate->format('H:i'),
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'status' => $matchStatus,
            'bet_type' => $dbPrediction ? $dbPrediction->bet_type : '1X2',
            'prediction' => $dbPrediction ? $dbPrediction->prediction : '1',
            'odds' => $dbPrediction ? $dbPrediction->odds : 1.85,
            'confidence_stars' => $dbPrediction ? $dbPrediction->confidence_stars : 2,
            'is_premium' => $dbPrediction ? (bool) $dbPrediction->is_premium : false,
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
            'venue' => $info['venue']['name'] ?? null,
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

    // =========================================================================
    // COUPON PREMIUM — génération sur cote cible
    // POST /api/predictions/coupon/custom   (auth:sanctum + premium)
    // Body : { "target_odds": 5.50, "max_picks": 5 }
    // =========================================================================

    public function couponCustom(Request $request)
    {
        $request->validate([
            'target_odds' => 'required|numeric|min:1.5|max:50',
            'max_picks' => 'integer|min:2|max:8',
        ]);

        $targetOdds = (float) $request->input('target_odds');
        $maxPicks = (int) $request->input('max_picks', 5);
        $date = Carbon::today()->toDateString();

        // Récupérer toutes les prédictions publiées du jour
        $rows = DB::table('predictions')
            ->where('is_published', true)
            ->whereDate('match_date', $date)
            ->orderBy('total_score', 'desc')
            ->limit(100)
            ->get();

        if ($rows->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Pas assez de prédictions disponibles aujourd\'hui.',
            ], 422);
        }

        // Algorithme glouton : on cherche la combinaison qui atteint target_odds
        // avec le maximum de confiance, sans dépasser maxPicks
        $best = null;
        $bestDelta = PHP_FLOAT_MAX;
        $pool = $rows->shuffle()->take(30); // limiter la recherche

        // Essais avec 2 à maxPicks picks
        for ($n = 2; $n <= min($maxPicks, $pool->count()); $n++) {
            $picks = $pool->take($n);
            $totalOdds = $picks->reduce(fn ($c, $p) => $c * (float) ($p->odds ?? 1.0), 1.0);
            $delta = abs($totalOdds - $targetOdds);

            if ($delta < $bestDelta) {
                $bestDelta = $delta;
                $best = $picks;
            }
            if ($delta < 0.5) {
                break;
            } // assez proche, stop
        }

        if (! $best || $best->isEmpty()) {
            $best = $pool->take($maxPicks);
        }

        $selected = $best->all();
        $totalOdds = array_reduce($selected, fn ($c, $p) => $c * (float) ($p->odds ?? 1.0), 1.0);
        $avgConfidence = array_sum(array_map(fn ($p) => (float) $p->total_score, $selected)) / count($selected);

        $picks = array_map(fn ($p) => [
            'match' => $p->home_team.' vs '.$p->away_team,
            'league' => $p->competition ?? null,
            'date' => $p->match_date,
            'time' => $p->match_time ?? null,
            'prediction' => $p->prediction,
            'type' => $p->bet_type,
            'odds' => round((float) ($p->odds ?? 1.0), 2),
            'confidence' => round((float) $p->total_score, 1),
            'stars' => $p->confidence_stars,
            'home_team_logo' => $p->home_team_logo ?? null,
            'away_team_logo' => $p->away_team_logo ?? null,
        ], $selected);

        // Analyse IA du coupon généré
        $analysis = $this->analyzeCouponWithClaude($picks, $totalOdds, $avgConfidence);

        return response()->json([
            'success' => true,
            'mode' => 'custom',
            'target_odds' => $targetOdds,
            'picks' => $picks,
            'matches_count' => count($picks),
            'total_odds' => round($totalOdds, 2),
            'avg_confidence' => round($avgConfidence, 1),
            'potential_gain_1000' => (int) round($totalOdds * 1000),
            'analysis' => $analysis,
            'generated_at' => Carbon::now()->toIso8601String(),
        ]);
    }

    // =========================================================================
    // COUPON PREMIUM — analyse d'un coupon saisi manuellement
    // POST /api/predictions/coupon/analyze   (auth:sanctum + premium)
    // Body : { "picks": [ { "match": "PSG vs Lyon", "prediction": "1", "odds": 1.8 } ] }
    // =========================================================================

    public function couponAnalyze(Request $request)
    {
        $request->validate([
            'picks' => 'required|array|min:1|max:20',
            'picks.*.match' => 'required|string|max:100',
            'picks.*.prediction' => 'required|string|max:50',
            'picks.*.odds' => 'required|numeric|min:1.01|max:1000',
        ]);

        $picks = $request->input('picks');
        $totalOdds = array_reduce($picks, fn ($c, $p) => $c * (float) $p['odds'], 1.0);

        // Calcul confiance moyen estimé (heuristique sur les cotes)
        $avgConfidence = array_sum(array_map(function ($p) {
            $odds = (float) $p['odds'];
            // Cote < 1.5 → forte confiance implicite du marché
            if ($odds < 1.3) {
                return 85.0;
            }
            if ($odds < 1.5) {
                return 75.0;
            }
            if ($odds < 2.0) {
                return 62.0;
            }
            if ($odds < 3.0) {
                return 50.0;
            }

            return 35.0;
        }, $picks)) / count($picks);

        $analysis = $this->analyzeCouponWithClaude($picks, $totalOdds, $avgConfidence);

        return response()->json([
            'success' => true,
            'picks_count' => count($picks),
            'total_odds' => round($totalOdds, 2),
            'avg_confidence' => round($avgConfidence, 1),
            'potential_gain_1000' => (int) round($totalOdds * 1000),
            'analysis' => $analysis,
        ]);
    }

    // =========================================================================
    // COUPON PREMIUM — analyse d'une image de ticket (OCR + IA)
    // POST /api/predictions/coupon/analyze-image   (auth:sanctum + premium)
    // Body : multipart/form-data  { "image": <file> }
    // =========================================================================

    public function couponAnalyzeImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        $key = config('services.anthropic.key', env('ANTHROPIC_API_KEY', ''));
        if (empty($key)) {
            return response()->json([
                'success' => false,
                'message' => 'Service d\'analyse non configuré.',
            ], 503);
        }

        // Lire et encoder l'image en base64
        $file = $request->file('image');
        $mime = $file->getMimeType();
        $b64 = base64_encode(file_get_contents($file->getRealPath()));

        $prompt = <<<'PROMPT'
Tu es un expert en paris sportifs. Analyse cette image d'un ticket de pari sportif.

Extrait toutes les informations visibles et retourne UNIQUEMENT un JSON valide :

{
  "picks": [
    {
      "match": "Équipe A vs Équipe B",
      "prediction": "1 (ou X, 2, Plus de 2.5, BTTS Oui, etc.)",
      "odds": 1.85,
      "league": "Ligue 1",
      "status": "pending"
    }
  ],
  "total_odds": 12.50,
  "stake": 1000,
  "potential_gain": 12500,
  "bookmaker": "1xBet",
  "ticket_date": "2026-05-18",
  "confidence_score": 62,
  "analysis": {
    "verdict": "RISQUÉ",
    "percentage_win": 38,
    "strengths": ["Cote raisonnable sur le pick 1", "Équipe domicile en forme"],
    "weaknesses": ["Cote totale trop élevée", "Pick 3 très incertain"],
    "advice": "Conseil court en français (max 150 caractères)"
  }
}

Règles :
- Si tu ne vois pas clairement une valeur, mets null
- verdict : "EXCELLENT" (>70%), "BON" (55-70%), "RISQUÉ" (40-55%), "TRÈS RISQUÉ" (<40%)
- percentage_win : estimation du % de chances de gagner ce coupon
- Réponds uniquement avec le JSON, sans explication
PROMPT;

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(45)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 1200,
                'messages' => [[
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mime,
                                'data' => $b64,
                            ],
                        ],
                        ['type' => 'text', 'text' => $prompt],
                    ],
                ]],
            ]);

            $content = $response->json('content.0.text', '{}');
            $content = trim($content);

            if (! str_starts_with($content, '{')) {
                preg_match('/\{.*\}/s', $content, $m);
                $content = $m[0] ?? '{}';
            }

            $data = json_decode($content, true) ?? [];

            return response()->json([
                'success' => true,
                'source' => 'image_ocr',
                'data' => $data,
            ]);

        } catch (\Throwable $e) {
            Log::error('[couponAnalyzeImage] '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse de l\'image.',
            ], 500);
        }
    }

    // =========================================================================
    // HELPER PRIVÉ — analyse Claude d'un coupon (picks + cote + confiance)
    // =========================================================================

    private function analyzeCouponWithClaude(array $picks, float $totalOdds, float $avgConfidence): array
    {
        $key = config('services.anthropic.key', env('ANTHROPIC_API_KEY', ''));
        if (empty($key)) {
            return $this->fallbackAnalysis($picks, $totalOdds, $avgConfidence);
        }

        $picksJson = json_encode($picks, JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
Tu es un analyste de paris sportifs expert. Analyse ce coupon de paris :

Picks : {$picksJson}
Cote totale : {$totalOdds}
Score de confiance moyen : {$avgConfidence}/100

Réponds UNIQUEMENT avec ce JSON valide :
{
  "verdict": "BON",
  "percentage_win": 52,
  "confidence_label": "Confiance modérée",
  "strengths": ["Point fort 1", "Point fort 2"],
  "weaknesses": ["Point faible 1"],
  "advice": "Conseil concis en français (max 120 caractères)",
  "risk_level": "MODÉRÉ"
}

Règles :
- verdict : "EXCELLENT" (>70%), "BON" (55-70%), "RISQUÉ" (40-55%), "TRÈS RISQUÉ" (<40%)
- percentage_win : % de chances de gagner ce coupon (tiens compte de la cote totale ET de la confiance)
- risk_level : "FAIBLE", "MODÉRÉ", "ÉLEVÉ", "TRÈS ÉLEVÉ"
- strengths et weaknesses : max 3 items chacun, en français
- Réponds uniquement avec le JSON
PROMPT;

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(20)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 400,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);

            $content = trim($response->json('content.0.text', '{}'));
            if (! str_starts_with($content, '{')) {
                preg_match('/\{.*\}/s', $content, $m);
                $content = $m[0] ?? '{}';
            }

            $data = json_decode($content, true);
            if (is_array($data) && ! empty($data)) {
                return $data;
            }
        } catch (\Throwable $e) {
            Log::warning('[analyzeCoupon] Claude error: '.$e->getMessage());
        }

        return $this->fallbackAnalysis($picks, $totalOdds, $avgConfidence);
    }

    /**
     * POST /predictions/generate — Premium only
     * Génère les prédictions pour une date donnée via l'algorithme IA.
     * Utilisé par le mobile quand un premium consulte une date sans prédictions.
     */
    public function generateForDate(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->is_premium) {
            return response()->json([
                'success' => false,
                'message' => 'Cette fonctionnalité est réservée aux membres Premium.',
            ], 403);
        }

        $date = $request->input('date', Carbon::tomorrow()->format('Y-m-d'));

        try {
            $targetDate = Carbon::parse($date);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'Date invalide.'], 422);
        }

        // Sécurité : max 7 jours dans le futur
        if ($targetDate->diffInDays(Carbon::today(), false) < -7) {
            return response()->json(['success' => false, 'message' => 'La date ne peut pas dépasser 7 jours dans le futur.'], 422);
        }

        $dateStr = $targetDate->format('Y-m-d');
        $cacheKey = "generated_predictions_premium_{$user->id}_{$dateStr}";

        // Limite : 1 génération par user par date toutes les 30 min
        if (Cache::has($cacheKey)) {
            $existing = \App\Models\Prediction::whereDate('match_date', $dateStr)->get();
            if ($existing->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'from_cache' => true,
                    'date' => $dateStr,
                    'generated' => $existing->count(),
                    'predictions' => $existing->map(fn ($p) => $this->formatPrediction($p, true)),
                ]);
            }
        }

        Log::info('Premium generate triggered', ['user' => $user->id, 'date' => $dateStr]);

        // Récupérer les matchs via API-Football pour cette date
        $fixtures = $this->footballApi->getMatchesByDate($dateStr);

        if (empty($fixtures)) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun match trouvé pour cette date via API-Football.',
                'date' => $dateStr,
                'generated' => 0,
            ]);
        }

        $generated = 0;
        $skipped = 0;

        foreach ($fixtures as $fixture) {
            try {
                $prediction = $this->algorithm->generatePrediction($fixture);
                if (! $prediction['should_publish']) {
                    $skipped++;

                    continue;
                }

                $fixtureInfo = $fixture['fixture'] ?? [];
                $teams = $fixture['teams'] ?? [];
                $league = $fixture['league'] ?? [];
                $matchDate = Carbon::parse($fixtureInfo['date'] ?? $dateStr);

                \App\Models\Prediction::updateOrCreate(
                    ['fixture_id' => $fixtureInfo['id'] ?? null],
                    [
                        'home_team' => $teams['home']['name'] ?? 'Unknown',
                        'away_team' => $teams['away']['name'] ?? 'Unknown',
                        'league' => $league['name'] ?? 'Unknown',
                        'country' => $league['country'] ?? 'Unknown',
                        'match_date' => $matchDate,
                        'prediction' => $prediction['outcome'],
                        'bet_type' => $prediction['type'],
                        'odds' => $prediction['odds'],
                        'confidence_score' => $prediction['confidence'],
                        'stars' => $prediction['stars'],
                        'is_premium' => $prediction['is_premium'],
                        'status' => 'pending',
                    ]
                );
                $generated++;
            } catch (\Exception $e) {
                Log::warning('generateForDate: erreur fixture', ['error' => $e->getMessage()]);
                $skipped++;
            }
        }

        Cache::put($cacheKey, true, 1800); // 30 min cooldown

        $savedPredictions = \App\Models\Prediction::whereDate('match_date', $dateStr)
            ->orderByDesc('total_score')
            ->get();

        return response()->json([
            'success' => true,
            'from_cache' => false,
            'date' => $dateStr,
            'analyzed' => count($fixtures),
            'generated' => $generated,
            'skipped' => $skipped,
            'predictions' => $savedPredictions->map(fn ($p) => $this->formatPrediction($p, true)),
        ]);
    }

    private function fallbackAnalysis(array $picks, float $totalOdds, float $avgConfidence): array
    {
        $pct = match (true) {
            $avgConfidence >= 75 && $totalOdds <= 5 => 65,
            $avgConfidence >= 65 && $totalOdds <= 8 => 52,
            $avgConfidence >= 55 && $totalOdds <= 15 => 40,
            default => 28,
        };

        $verdict = match (true) {
            $pct >= 70 => 'EXCELLENT',
            $pct >= 55 => 'BON',
            $pct >= 40 => 'RISQUÉ',
            default => 'TRÈS RISQUÉ',
        };

        return [
            'verdict' => $verdict,
            'percentage_win' => $pct,
            'confidence_label' => $pct >= 55 ? 'Confiance satisfaisante' : 'Confiance faible',
            'strengths' => $avgConfidence >= 65 ? ['Picks à confiance élevée'] : [],
            'weaknesses' => $totalOdds > 10 ? ['Cote totale très élevée'] : [],
            'advice' => $pct >= 55 ? 'Coupon intéressant, mise raisonnable conseillée.' : 'Cote trop élevée, préférer un coupon plus prudent.',
            'risk_level' => $totalOdds > 15 ? 'TRÈS ÉLEVÉ' : ($totalOdds > 8 ? 'ÉLEVÉ' : 'MODÉRÉ'),
        ];
    }
}
