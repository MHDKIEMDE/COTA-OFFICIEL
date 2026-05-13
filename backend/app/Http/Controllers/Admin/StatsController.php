<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatsController extends Controller
{
    public function index(): View
    {
        // --- Taux de réussite global ---
        $resolved   = Prediction::whereIn('status', ['won', 'lost'])->count();
        $won        = Prediction::where('status', 'won')->count();
        $lost       = Prediction::where('status', 'lost')->count();
        $pending    = Prediction::where('status', 'pending')->count();
        $winRate    = $resolved > 0 ? round(($won / $resolved) * 100, 1) : 0;

        // --- Taux de réussite par nombre d'étoiles ---
        $byStars = [];
        for ($stars = 1; $stars <= 4; $stars++) {
            $w = Prediction::where('confidence_stars', $stars)->where('status', 'won')->count();
            $l = Prediction::where('confidence_stars', $stars)->where('status', 'lost')->count();
            $byStars[] = [
                'stars'    => $stars,
                'won'      => $w,
                'lost'     => $l,
                'total'    => $w + $l,
                'win_rate' => ($w + $l) > 0 ? round(($w / ($w + $l)) * 100, 1) : 0,
            ];
        }

        // --- Taux de réussite par type de pari ---
        $byBetType = Prediction::select('bet_type',
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

        // --- Taux de réussite par compétition (top 10) ---
        $byCompetition = Prediction::select('competition',
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

        // --- Évolution du taux de réussite sur 30 jours ---
        $last30Days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $w = Prediction::whereDate('match_date', $date)->where('status', 'won')->count();
            $l = Prediction::whereDate('match_date', $date)->where('status', 'lost')->count();
            $last30Days[] = [
                'date'     => $date->format('d/m'),
                'won'      => $w,
                'lost'     => $l,
                'win_rate' => ($w + $l) > 0 ? round(($w / ($w + $l)) * 100) : null,
            ];
        }

        // --- Croissance utilisateurs sur 30 jours ---
        $userGrowth = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $userGrowth[] = [
                'date'  => $date->format('d/m'),
                'count' => User::whereDate('created_at', $date)->count(),
            ];
        }

        // --- ROI moyen (cote * win_rate) ---
        $avgOdds = Prediction::where('status', 'won')->avg('odds') ?? 0;
        $roi     = $winRate > 0 ? round(($winRate / 100) * $avgOdds - 1, 3) * 100 : 0;

        // --- Revenus mensuels sur 12 mois ---
        $revenueByMonth = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenueByMonth[] = [
                'month'       => $month->format('M Y'),
                'amount'      => (int) Subscription::where('payment_status', 'completed')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('amount'),
                'new_users'   => User::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
                'new_premium' => Subscription::where('payment_status', 'completed')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }

        return view('admin.stats.index', compact(
            'resolved', 'won', 'lost', 'pending', 'winRate',
            'byStars', 'byBetType', 'byCompetition',
            'last30Days', 'userGrowth', 'roi', 'avgOdds',
            'revenueByMonth'
        ));
    }
}
