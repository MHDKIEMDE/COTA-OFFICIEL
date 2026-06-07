<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OddsAnomaly;
use App\Models\Prediction;
use Illuminate\Http\JsonResponse;

class OddsAnomalyController extends Controller
{
    // ── GET /api/odds-anomalies/live ─────────────────────────────────────────

    public function live(): JsonResponse
    {
        $anomalies = OddsAnomaly::active()
            ->orderByDesc('gap_pct')
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'match'        => $a->home_team . ' vs ' . $a->away_team,
                'home_team'    => $a->home_team,
                'away_team'    => $a->away_team,
                'competition'  => $a->competition,
                'match_date'   => $a->match_date,
                'bet_type'     => $a->bet_type,
                'outcome'      => $a->outcome,
                'outcome_label' => $this->outcomeLabel($a->outcome, $a->home_team, $a->away_team),
                'bookmaker'    => $a->bookmaker,
                'odd_value'    => (float) $a->odd_value,
                'market_odd'   => (float) $a->market_odd,
                'gap_pct'      => (float) $a->gap_pct,
                'is_overpriced' => $a->is_overpriced,
                'minutes_remaining' => $a->minutes_remaining,
                'expires_at'   => $a->expires_at,
            ]);

        return response()->json([
            'success' => true,
            'count'   => $anomalies->count(),
            'data'    => $anomalies,
        ]);
    }

    // ── GET /api/predictions/sure-bets ───────────────────────────────────────

    public function sureBets(): JsonResponse
    {
        $sureBets = Prediction::whereDate('match_date', today())
            ->whereNotNull('sure_bet_level')
            ->where('is_published', true)
            ->orderByDesc('sure_bet_level')
            ->orderByDesc('total_score')
            ->get()
            ->map(fn($p) => [
                'id'              => $p->id,
                'match'           => $p->home_team . ' vs ' . $p->away_team,
                'home_team'       => $p->home_team,
                'away_team'       => $p->away_team,
                'home_team_logo'  => $p->home_team_logo ?? ($p->home_team_id > 0 ? 'https://media.api-sports.io/football/teams/' . $p->home_team_id . '.png' : null),
                'away_team_logo'  => $p->away_team_logo ?? ($p->away_team_id > 0 ? 'https://media.api-sports.io/football/teams/' . $p->away_team_id . '.png' : null),
                'competition'     => $p->competition,
                'competition_logo' => $p->competition_logo ?? ($p->competition_id > 0 ? 'https://media.api-sports.io/football/leagues/' . $p->competition_id . '.png' : null),
                'match_time'      => $p->match_time,
                'bet_type'        => $p->bet_type,
                'prediction'      => $p->prediction,
                'odds'            => (float) $p->odds,
                'confidence_stars' => $p->confidence_stars,
                'sure_bet_level'  => $p->sure_bet_level,
                'sure_bet_analysis' => $p->sure_bet_analysis ? json_decode($p->sure_bet_analysis, true) : null,
                'value_score'     => (float) $p->value_score,
                'ev_positive'     => (bool) $p->ev_positive,
            ]);

        return response()->json([
            'success' => true,
            'count'   => $sureBets->count(),
            'data'    => $sureBets,
        ]);
    }

    private function outcomeLabel(string $outcome, string $home, string $away): string
    {
        return match($outcome) {
            '1'     => $home . ' gagne',
            '2'     => $away . ' gagne',
            'X'     => 'Match nul',
            'over'  => 'Plus de 2.5 buts',
            'under' => 'Moins de 2.5 buts',
            default => $outcome,
        };
    }
}
