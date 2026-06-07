<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\Prediction;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatsController extends Controller
{
    public function index(): View
    {
        // --- Agrégats scalaires (cachés 5 min) ---
        [$resolved, $won, $lost, $pending] = Cache::remember('admin.stats.scalars', 300, function () {
            return [
                Prediction::whereIn('status', ['won', 'lost'])->count(),
                Prediction::where('status', 'won')->count(),
                Prediction::where('status', 'lost')->count(),
                Prediction::where('status', 'pending')->count(),
            ];
        });
        $winRate = $resolved > 0 ? round(($won / $resolved) * 100, 1) : 0;

        // --- Taux de réussite par nombre d'étoiles (1 requête groupée) ---
        $byStars = Cache::remember('admin.stats.by_stars', 300, function () {
            $rows = DB::table('predictions')
                ->selectRaw('confidence_stars, status, COUNT(*) as cnt')
                ->whereIn('status', ['won', 'lost'])
                ->groupByRaw('confidence_stars, status')
                ->get()->groupBy('confidence_stars');

            return collect(range(1, 4))->map(function ($stars) use ($rows) {
                $group = $rows[$stars] ?? collect();
                $w = (int) ($group->firstWhere('status', 'won')?->cnt  ?? 0);
                $l = (int) ($group->firstWhere('status', 'lost')?->cnt ?? 0);
                return ['stars' => $stars, 'won' => $w, 'lost' => $l, 'total' => $w + $l,
                    'win_rate' => ($w + $l) > 0 ? round(($w / ($w + $l)) * 100, 1) : 0];
            })->values()->toArray();
        });

        // --- Taux de réussite par type de pari (caché 5 min) ---
        $byBetType = Cache::remember('admin.stats.by_bet_type', 300, function () {
            return Prediction::select('bet_type',
                    DB::raw('SUM(CASE WHEN status="won" THEN 1 ELSE 0 END) as won'),
                    DB::raw('SUM(CASE WHEN status="lost" THEN 1 ELSE 0 END) as lost'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereIn('status', ['won', 'lost'])
                ->groupBy('bet_type')
                ->orderByDesc('total')
                ->get()
                ->map(fn($r) => array_merge($r->toArray(), [
                    'win_rate' => ($r->won + $r->lost) > 0 ? round(($r->won / ($r->won + $r->lost)) * 100, 1) : 0,
                ]));
        });

        // --- Taux de réussite par compétition top 10 (caché 5 min) ---
        $byCompetition = Cache::remember('admin.stats.by_competition', 300, function () {
            return Prediction::select('competition',
                    DB::raw('SUM(CASE WHEN status="won" THEN 1 ELSE 0 END) as won'),
                    DB::raw('SUM(CASE WHEN status="lost" THEN 1 ELSE 0 END) as lost'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereIn('status', ['won', 'lost'])
                ->groupBy('competition')
                ->orderByDesc('total')
                ->take(10)
                ->get()
                ->map(fn($r) => array_merge($r->toArray(), [
                    'win_rate' => ($r->won + $r->lost) > 0 ? round(($r->won / ($r->won + $r->lost)) * 100, 1) : 0,
                ]));
        });

        // --- Évolution du taux de réussite sur 30 jours (1 requête groupée) ---
        $last30Days = Cache::remember('admin.stats.last30days', 300, function () {
            $start = now()->subDays(29)->startOfDay();
            $rows  = DB::table('predictions')
                ->selectRaw('DATE(match_date) as day, status, COUNT(*) as cnt')
                ->whereIn('status', ['won', 'lost'])
                ->where('match_date', '>=', $start)
                ->groupByRaw('DATE(match_date), status')
                ->get()->groupBy('day');

            $data = [];
            for ($i = 29; $i >= 0; $i--) {
                $date  = now()->subDays($i);
                $key   = $date->format('Y-m-d');
                $group = $rows[$key] ?? collect();
                $w = (int) ($group->firstWhere('status', 'won')?->cnt  ?? 0);
                $l = (int) ($group->firstWhere('status', 'lost')?->cnt ?? 0);
                $data[] = [
                    'date'     => $date->format('d/m'),
                    'won'      => $w,
                    'lost'     => $l,
                    'win_rate' => ($w + $l) > 0 ? round(($w / ($w + $l)) * 100) : null,
                ];
            }
            return $data;
        });

        // --- Croissance utilisateurs sur 30 jours (1 requête groupée) ---
        $userGrowth = Cache::remember('admin.stats.user_growth_30d', 300, function () {
            $start = now()->subDays(29)->startOfDay();
            $rows  = DB::table('users')
                ->selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
                ->where('created_at', '>=', $start)
                ->groupByRaw('DATE(created_at)')
                ->pluck('cnt', 'day');

            $data = [];
            for ($i = 29; $i >= 0; $i--) {
                $date   = now()->subDays($i);
                $key    = $date->format('Y-m-d');
                $data[] = ['date' => $date->format('d/m'), 'count' => (int) ($rows[$key] ?? 0)];
            }
            return $data;
        });

        // --- ROI moyen (cote * win_rate) ---
        $avgOdds = Prediction::where('status', 'won')->avg('odds') ?? 0;
        $roi     = $winRate > 0 ? round(($winRate / 100) * $avgOdds - 1, 3) * 100 : 0;

        // --- Revenus mensuels sur 12 mois (3 requêtes groupées au lieu de 36) ---
        $revenueByMonth = Cache::remember('admin.stats.revenue_12m', 300, function () {
            $start = now()->subMonths(11)->startOfMonth();

            $revenues = DB::table('subscriptions')
                ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, SUM(amount) as total, COUNT(*) as cnt')
                ->where('payment_status', 'completed')
                ->where('created_at', '>=', $start)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->get()->keyBy(fn($r) => "{$r->y}-{$r->m}");

            $newUsers = DB::table('users')
                ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, COUNT(*) as cnt')
                ->where('created_at', '>=', $start)
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->get()->keyBy(fn($r) => "{$r->y}-{$r->m}");

            $data = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $key   = $month->year . '-' . $month->month;
                $data[] = [
                    'month'       => $month->format('M Y'),
                    'amount'      => (int) ($revenues[$key]?->total    ?? 0),
                    'new_users'   => (int) ($newUsers[$key]?->cnt      ?? 0),
                    'new_premium' => (int) ($revenues[$key]?->cnt      ?? 0),
                ];
            }
            return $data;
        });

        return view('admin.stats.index', compact(
            'resolved', 'won', 'lost', 'pending', 'winRate',
            'byStars', 'byBetType', 'byCompetition',
            'last30Days', 'userGrowth', 'roi', 'avgOdds',
            'revenueByMonth'
        ));
    }

    /**
     * GET /admin/stats/funnel — vue funnel acquisition (T7 CDC v3.1)
     */
    public function funnel(): \Illuminate\View\View
    {
        $days = 7;
        $from = now()->subDays($days)->startOfDay();

        // Compter chaque event P0 sur les N derniers jours
        $counts = AnalyticsEvent::where('created_at', '>=', $from)
            ->selectRaw('event_name, COUNT(*) as total, COUNT(DISTINCT session_hash) as unique_sessions')
            ->groupBy('event_name')
            ->pluck('unique_sessions', 'event_name')
            ->toArray();

        // Étapes du funnel dans l'ordre
        $steps = [
            ['key' => 'app_opened',              'label' => 'App ouverte'],
            ['key' => 'scratch_card_seen',        'label' => 'Carte à gratter vue'],
            ['key' => 'scratch_attempt_blocked',  'label' => 'Tap gratter (bloqué)'],
            ['key' => 'signup_started',           'label' => 'Inscription démarrée'],
            ['key' => 'signup_completed',         'label' => 'Compte créé'],
            ['key' => 'premium_wall_hit',         'label' => 'Mur premium atteint'],
            ['key' => 'subscription_started',     'label' => 'Abonnement démarré'],
            ['key' => 'subscription_completed',   'label' => 'Premium activé'],
            ['key' => 'bet_now_clicked',          'label' => 'Clic "Parier maintenant"'],
            ['key' => 'affiliate_redirect',       'label' => 'Redirection bookmaker'],
        ];

        $top = $counts['app_opened'] ?? 1;
        $funnel = array_map(function (array $step) use ($counts, $top): array {
            $count = (int) ($counts[$step['key']] ?? 0);
            return [
                'key'     => $step['key'],
                'label'   => $step['label'],
                'count'   => $count,
                'pct_top' => $top > 0 ? round($count / $top * 100, 1) : 0,
            ];
        }, $steps);

        // Événements jour par jour (sparkline 7j)
        $dailyTrend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dailyTrend[] = [
                'date'   => $date,
                'opens'  => AnalyticsEvent::whereDate('created_at', $date)->where('event_name', 'app_opened')->count(),
                'signups'=> AnalyticsEvent::whereDate('created_at', $date)->where('event_name', 'signup_completed')->count(),
                'subs'   => AnalyticsEvent::whereDate('created_at', $date)->where('event_name', 'subscription_completed')->count(),
            ];
        }

        return view('admin.stats.funnel', compact('funnel', 'dailyTrend', 'days'));
    }

    /**
     * GET /admin/stats/active-users — JSON, appelé en polling toutes les 30 s
     * "Actif" = token Sanctum utilisé dans les 5 dernières minutes
     */
    public function activeUsers(): JsonResponse
    {
        $data = Cache::remember('admin.active_users', 30, function () {
            $threshold = now()->subMinutes(5);

            $rows = DB::table('personal_access_tokens as t')
                ->join('users as u', 'u.id', '=', DB::raw('CAST(t.tokenable_id AS UNSIGNED)'))
                ->where('t.last_used_at', '>=', $threshold)
                ->select(
                    'u.id',
                    'u.name',
                    'u.email',
                    'u.is_premium',
                    't.last_used_at',
                )
                ->orderByDesc('t.last_used_at')
                ->limit(50)
                ->get();

            return [
                'count'     => $rows->count(),
                'premium'   => $rows->where('is_premium', true)->count(),
                'free'      => $rows->where('is_premium', false)->count(),
                'users'     => $rows->map(fn($r) => [
                    'id'         => $r->id,
                    'name'       => $r->name,
                    'email'      => $r->email,
                    'is_premium' => (bool) $r->is_premium,
                    'last_seen'  => $r->last_used_at,
                ])->values(),
                'updated_at' => now()->toIso8601String(),
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * GET /admin/stats/segments
     * Vue de segmentation : combien d'utilisateurs par bookmaker, profil, région.
     * Permet de cibler les features et les partenariats.
     */
    public function segments(): View
    {
        $total = User::count();

        // ── Par bookmaker déclaré ─────────────────────────────────────────────
        $byBookmaker = User::select('bookmaker_slug', DB::raw('COUNT(*) as count'))
            ->groupBy('bookmaker_slug')
            ->orderByDesc('count')
            ->get()
            ->map(fn($r) => [
                'slug'    => $r->bookmaker_slug ?? 'non renseigné',
                'count'   => $r->count,
                'pct'     => $total > 0 ? round($r->count / $total * 100, 1) : 0,
                'is_none' => $r->bookmaker_slug === '__none__',
            ]);

        // ── Par profil parieur ────────────────────────────────────────────────
        $profilLabels = [
            'daily'     => 'Chaque jour',
            'weekend'   => 'Le week-end',
            'big_games' => 'Gros matchs',
        ];
        $byProfil = User::select('parieur_profil', DB::raw('COUNT(*) as count'))
            ->groupBy('parieur_profil')
            ->orderByDesc('count')
            ->get()
            ->map(fn($r) => [
                'profil' => $profilLabels[$r->parieur_profil] ?? ($r->parieur_profil ?? 'non renseigné'),
                'count'  => $r->count,
                'pct'    => $total > 0 ? round($r->count / $total * 100, 1) : 0,
            ]);

        // ── Par région détectée ───────────────────────────────────────────────
        $regionLabels = [
            'west_africa'    => 'Afrique de l\'Ouest',
            'central_africa' => 'Afrique Centrale',
            'east_africa'    => 'Afrique de l\'Est',
            'north_africa'   => 'Afrique du Nord',
            'south_africa'   => 'Afrique du Sud',
            'europe'         => 'Europe',
            'global'         => 'Autre / Global',
        ];
        $byRegion = User::select('detected_region', DB::raw('COUNT(*) as count'))
            ->groupBy('detected_region')
            ->orderByDesc('count')
            ->get()
            ->map(fn($r) => [
                'region' => $regionLabels[$r->detected_region] ?? ($r->detected_region ?? 'non détectée'),
                'count'  => $r->count,
                'pct'    => $total > 0 ? round($r->count / $total * 100, 1) : 0,
            ]);

        // ── Utilisateurs sans compte bookmaker (à convertir) ──────────────────
        $toConvert = User::where('bookmaker_slug', '__none__')
            ->orWhereNull('bookmaker_slug')
            ->count();

        // ── Croisement bookmaker × profil (top opportunités) ─────────────────
        $crossSegments = User::select(
                'bookmaker_slug',
                'parieur_profil',
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('bookmaker_slug')
            ->whereNotNull('parieur_profil')
            ->groupBy('bookmaker_slug', 'parieur_profil')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return view('admin.stats.segments', compact(
            'total', 'byBookmaker', 'byProfil', 'byRegion',
            'toConvert', 'crossSegments'
        ));
    }
}
