<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Services\ValueBettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Tableau de bord personnel Value Betting (admin uniquement).
 *
 * Stratégie : 100 000 FCFA/semaine
 * Bankroll de référence : configuré via ?bankroll= (défaut 500 000 FCFA).
 */
class MyBetsDashboardController extends Controller
{
    public function __construct(private readonly ValueBettingService $valueBetting) {}

    // ── GET /admin/my-bets/dashboard ─────────────────────────────────────────

    public function dashboard(Request $request): JsonResponse
    {
        $bankroll = (int) $request->input('bankroll', 500_000);
        $weeks    = (int) $request->input('weeks', 4);

        $since = Carbon::now()->subWeeks($weeks)->startOfWeek();

        $predictions = Prediction::where('ev_positive', true)
            ->whereIn('status', ['won', 'lost', 'pending'])
            ->where('match_date', '>=', $since)
            ->orderByDesc('match_date')
            ->get();

        // ── Stats globales ────────────────────────────────────────────────────
        $finished = $predictions->whereIn('status', ['won', 'lost']);
        $won      = $finished->where('status', 'won');
        $lost     = $finished->where('status', 'lost');

        $totalBets  = $finished->count();
        $wonCount   = $won->count();
        $winRate    = $totalBets > 0 ? round($wonCount / $totalBets * 100, 1) : 0.0;

        $totalStaked = $finished->sum(fn($p) => $this->valueBetting->advisedStake((float) $p->kelly_fraction, $bankroll));
        $totalReturn = $won->sum(fn($p) => $this->valueBetting->advisedStake((float) $p->kelly_fraction, $bankroll) * (float) $p->odds);
        $totalProfit = (int) round($totalReturn - $totalStaked);
        $roi         = $totalStaked > 0 ? round($totalProfit / $totalStaked * 100, 1) : 0.0;

        // ── Stats par semaine ─────────────────────────────────────────────────
        $weeklyStats = [];
        for ($w = $weeks - 1; $w >= 0; $w--) {
            $start = Carbon::now()->subWeeks($w)->startOfWeek();
            $end   = Carbon::now()->subWeeks($w)->endOfWeek();

            $weekPred  = $finished->filter(fn($p) => Carbon::parse($p->match_date)->between($start, $end));
            $weekWon   = $weekPred->where('status', 'won');
            $weekStaked = $weekPred->sum(fn($p) => $this->valueBetting->advisedStake((float) $p->kelly_fraction, $bankroll));
            $weekReturn = $weekWon->sum(fn($p) => $this->valueBetting->advisedStake((float) $p->kelly_fraction, $bankroll) * (float) $p->odds);

            $weeklyStats[] = [
                'week_start'  => $start->toDateString(),
                'week_end'    => $end->toDateString(),
                'bets'        => $weekPred->count(),
                'won'         => $weekWon->count(),
                'win_rate'    => $weekPred->count() > 0
                    ? round($weekWon->count() / $weekPred->count() * 100, 1)
                    : 0.0,
                'staked_fcfa' => (int) round($weekStaked),
                'return_fcfa' => (int) round($weekReturn),
                'profit_fcfa' => (int) round($weekReturn - $weekStaked),
            ];
        }

        // ── Meilleur pari du jour (EV+ le plus élevé non encore joué) ────────
        $bestToday = Prediction::where('ev_positive', true)
            ->whereDate('match_date', Carbon::today())
            ->where('status', 'pending')
            ->orderByDesc('value_score')
            ->first();

        $bestTodayData = null;
        if ($bestToday) {
            $stake = $this->valueBetting->advisedStake((float) $bestToday->kelly_fraction, $bankroll);
            $bestTodayData = [
                'match'         => $bestToday->home_team . ' vs ' . $bestToday->away_team,
                'competition'   => $bestToday->competition,
                'bet_type'      => $bestToday->bet_type,
                'prediction'    => $bestToday->prediction,
                'odds'          => (float) $bestToday->odds,
                'confidence'    => (float) $bestToday->total_score,
                'stars'         => $bestToday->confidence_stars,
                'value_score'   => (float) $bestToday->value_score,
                'kelly_pct'     => round((float) $bestToday->kelly_fraction * 100, 1),
                'advised_stake' => $stake,
                'potential_gain' => $this->valueBetting->potentialGain((float) $bestToday->odds, $stake),
                'summary'       => $this->valueBetting->summary(
                    (float) $bestToday->value_score,
                    (float) $bestToday->kelly_fraction,
                    $bankroll
                ),
            ];
        }

        // ── Paris EV+ en attente aujourd'hui ─────────────────────────────────
        $todayPending = Prediction::where('ev_positive', true)
            ->whereDate('match_date', Carbon::today())
            ->where('status', 'pending')
            ->orderByDesc('value_score')
            ->get()
            ->map(fn($p) => [
                'id'            => $p->id,
                'match'         => $p->home_team . ' vs ' . $p->away_team,
                'competition'   => $p->competition,
                'match_time'    => $p->match_time,
                'bet_type'      => $p->bet_type,
                'prediction'    => $p->prediction,
                'odds'          => (float) $p->odds,
                'stars'         => $p->confidence_stars,
                'value_score'   => (float) $p->value_score,
                'kelly_pct'     => round((float) $p->kelly_fraction * 100, 1),
                'advised_stake' => $this->valueBetting->advisedStake((float) $p->kelly_fraction, $bankroll),
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'bankroll_fcfa'   => $bankroll,
                'target_weekly'   => 100_000,
                'analysis_weeks'  => $weeks,
                'global'          => [
                    'total_bets'     => $totalBets,
                    'won'            => $wonCount,
                    'lost'           => $lost->count(),
                    'pending'        => $predictions->where('status', 'pending')->count(),
                    'win_rate_pct'   => $winRate,
                    'total_staked'   => $totalStaked,
                    'total_profit'   => $totalProfit,
                    'roi_pct'        => $roi,
                ],
                'weekly_stats'    => $weeklyStats,
                'best_today'      => $bestTodayData,
                'today_ev_picks'  => $todayPending,
            ],
        ]);
    }
}
