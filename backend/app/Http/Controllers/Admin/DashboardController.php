<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Prediction;
use App\Models\Subscription;
use App\Models\AffiliationBonus;
use App\Models\Referral;
use App\Models\Feedback;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard principal
     */
    public function index()
    {
        // Statistiques globales
        $stats = [
            'total_users' => User::count(),
            'premium_users' => User::where('is_premium', true)->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
            'active_users' => User::whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(7))->count(),
        ];

        // Statistiques pronostics
        $predictionStats = [
            'total' => Prediction::count(),
            'pending' => Prediction::where('status', 'pending')->count(),
            'won' => Prediction::where('status', 'won')->count(),
            'lost' => Prediction::where('status', 'lost')->count(),
            'today' => Prediction::whereDate('created_at', today())->count(),
        ];

        // Calcul du taux de réussite
        $totalResolved = $predictionStats['won'] + $predictionStats['lost'];
        $predictionStats['win_rate'] = $totalResolved > 0 
            ? round(($predictionStats['won'] / $totalResolved) * 100, 1) 
            : 0;

        // Revenus (abonnements)
        $revenueStats = [
            'total_subscriptions' => Subscription::where('status', 'completed')->count(),
            'monthly_revenue' => Subscription::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'weekly_revenue' => Subscription::where('status', 'completed')
                ->whereBetween('created_at', [now()->startOfWeek(), now()])
                ->sum('amount'),
        ];

        // Affiliations
        $affiliationStats = [
            'total_bonuses' => AffiliationBonus::count(),
            'verified_bonuses' => AffiliationBonus::where('is_verified', true)->count(),
            'pending_bonuses' => AffiliationBonus::where('is_verified', false)->count(),
        ];

        // Parrainages
        $referralStats = [
            'total' => Referral::count(),
            'this_month' => Referral::whereMonth('created_at', now()->month)->count(),
        ];

        // Graphiques - Inscriptions sur 7 jours
        $userChartData = $this->getUserChartData();

        // Graphiques - Pronostics sur 7 jours
        $predictionChartData = $this->getPredictionChartData();

        // Graphiques - Revenus sur 30 jours
        $revenueChartData = $this->getRevenueChartData();

        // Dernières activités
        $recentActivities = $this->getRecentActivities();

        // Feedbacks récents
        $recentFeedbacks = Feedback::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Dernières prédictions (temps réel)
        $latestPredictions = Prediction::where('is_published', true)
            ->whereDate('match_date', today())
            ->orderByDesc('total_score')
            ->take(8)
            ->get(['id', 'home_team', 'away_team', 'home_team_logo', 'away_team_logo', 'prediction', 'bet_type', 'confidence_stars', 'total_score', 'odds', 'status', 'match_date', 'match_time', 'competition', 'is_premium']);

        // Derniers abonnements (temps réel)
        $latestSubscriptions = Subscription::with('user')
            ->latest()
            ->take(8)
            ->get(['id', 'user_id', 'plan', 'amount', 'status', 'created_at']);

        return view('admin.dashboard', compact(
            'stats',
            'predictionStats',
            'revenueStats',
            'affiliationStats',
            'referralStats',
            'userChartData',
            'predictionChartData',
            'revenueChartData',
            'recentActivities',
            'recentFeedbacks',
            'latestPredictions',
            'latestSubscriptions'
        ));
    }

    private function getUserChartData(): array
    {
        return Cache::remember('admin.chart.users_7d', 300, function () {
            $start = now()->subDays(6)->startOfDay();

            $rows = DB::table('users')
                ->selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
                ->where('created_at', '>=', $start)
                ->groupByRaw('DATE(created_at)')
                ->pluck('cnt', 'day');

            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $key  = $date->format('Y-m-d');
                $data[] = ['date' => $date->format('d/m'), 'count' => (int) ($rows[$key] ?? 0)];
            }
            return $data;
        });
    }

    private function getPredictionChartData(): array
    {
        return Cache::remember('admin.chart.predictions_7d', 300, function () {
            $start = now()->subDays(6)->startOfDay();

            $rows = DB::table('predictions')
                ->selectRaw('DATE(created_at) as day, status, COUNT(*) as cnt')
                ->where('created_at', '>=', $start)
                ->groupByRaw('DATE(created_at), status')
                ->get()
                ->groupBy('day');

            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date  = now()->subDays($i);
                $key   = $date->format('Y-m-d');
                $group = $rows[$key] ?? collect();
                $data[] = [
                    'date'    => $date->format('d/m'),
                    'won'     => (int) ($group->firstWhere('status', 'won')?->cnt     ?? 0),
                    'lost'    => (int) ($group->firstWhere('status', 'lost')?->cnt    ?? 0),
                    'pending' => (int) ($group->firstWhere('status', 'pending')?->cnt ?? 0),
                ];
            }
            return $data;
        });
    }

    private function getRevenueChartData(): array
    {
        return Cache::remember('admin.chart.revenue_30d', 300, function () {
            $start = now()->subDays(29)->startOfDay();

            $rows = DB::table('subscriptions')
                ->selectRaw('DATE(created_at) as day, SUM(amount) as total, COUNT(*) as cnt')
                ->where('status', 'completed')
                ->where('created_at', '>=', $start)
                ->groupByRaw('DATE(created_at)')
                ->get()
                ->keyBy('day');

            $data = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $key  = $date->format('Y-m-d');
                $row  = $rows[$key] ?? null;
                $data[] = [
                    'date'    => $date->format('d/m'),
                    'revenue' => (int) ($row?->total ?? 0),
                    'count'   => (int) ($row?->cnt   ?? 0),
                ];
            }
            return $data;
        });
    }

    /**
     * Activités récentes
     */
    private function getRecentActivities(): array
    {
        $activities = [];

        // Derniers utilisateurs
        $users = User::latest()->take(3)->get();
        foreach ($users as $user) {
            $activities[] = [
                'type' => 'user',
                'icon' => 'fa-user-plus',
                'color' => 'bg-blue-500',
                'message' => "Nouvel utilisateur: {$user->name}",
                'time' => $user->created_at,
            ];
        }

        // Derniers abonnements
        $subscriptions = Subscription::where('status', 'completed')->latest()->take(2)->get();
        foreach ($subscriptions as $sub) {
            $activities[] = [
                'type' => 'subscription',
                'icon' => 'fa-crown',
                'color' => 'bg-yellow-500',
                'message' => "Nouvel abonnement: {$sub->plan}",
                'time' => $sub->created_at,
            ];
        }

        // Derniers pronostics gagnants
        $predictions = Prediction::where('status', 'won')->latest()->take(2)->get();
        foreach ($predictions as $pred) {
            $activities[] = [
                'type' => 'prediction',
                'icon' => 'fa-check-circle',
                'color' => 'bg-green-500',
                'message' => "Pronostic gagné: {$pred->home_team} vs {$pred->away_team}",
                'time' => $pred->updated_at,
            ];
        }

        // Trier par date
        usort($activities, fn($a, $b) => $b['time'] <=> $a['time']);

        return array_slice($activities, 0, 8);
    }
}

