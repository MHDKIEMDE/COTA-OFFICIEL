<?php

declare(strict_types=1);

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CacheEmptyStateDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $data = $this->buildEmptyStatePayload();

        Cache::put('empty_state_data', $data, now()->addMinutes(20));

        Log::info('CacheEmptyStateDataJob: payload mis à jour', [
            'win_rate_30d'    => $data['win_rate_30d'],
            'last_wins_count' => count($data['last_wins']),
        ]);
    }

    private function buildEmptyStatePayload(): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateString();

        // Win rate sur 30 jours (prédictions terminées uniquement)
        $finished = DB::table('predictions')
            ->where('is_published', true)
            ->whereIn('status', ['won', 'lost'])
            ->whereDate('match_date', '>=', $thirtyDaysAgo)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $won   = (int) ($finished['won']  ?? 0);
        $total = $won + (int) ($finished['lost'] ?? 0);
        $winRate = $total > 0 ? round($won / $total * 100, 1) : 0.0;

        // 5 dernières prédictions gagnées
        $lastWins = DB::table('predictions')
            ->where('is_published', true)
            ->where('status', 'won')
            ->orderByDesc('match_date')
            ->limit(5)
            ->get(['id', 'home_team', 'away_team', 'competition', 'prediction_outcome', 'odds', 'match_date'])
            ->map(fn ($p) => [
                'match'      => "{$p->home_team} vs {$p->away_team}",
                'league'     => $p->competition,
                'prediction' => $p->prediction_outcome,
                'odds'       => $p->odds,
                'date'       => $p->match_date,
            ])
            ->values()
            ->toArray();

        // Prochain match disponible (premier match non encore joué)
        $nextMatch = DB::table('matches')
            ->where('status', 'scheduled')
            ->where('match_date', '>=', Carbon::now()->toDateTimeString())
            ->orderBy('match_date')
            ->first(['match_date']);

        return [
            'win_rate_30d'    => $winRate,
            'wins_30d'        => $won,
            'total_30d'       => $total,
            'last_wins'       => $lastWins,
            'next_match_at'   => $nextMatch?->match_date,
            'computed_at'     => Carbon::now()->toIso8601String(),
        ];
    }
}
