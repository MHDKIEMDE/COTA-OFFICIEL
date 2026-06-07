<?php

namespace App\Jobs;

use App\Models\Prediction;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendDailyNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationService $notificationService): void
    {
        $today = Carbon::today()->format('Y-m-d');

        // ── Prédictions publiées aujourd'hui ──────────────────────────────────
        $predictions = Prediction::where('is_published', true)
            ->whereDate('match_date', $today)
            ->orderByDesc('confidence_stars')
            ->orderByDesc('total_score')
            ->take(5)
            ->get(['home_team', 'away_team', 'predicted_outcome', 'odds', 'confidence_stars', 'total_score', 'competition']);

        $totalPredictions = Prediction::where('is_published', true)
            ->whereDate('match_date', $today)
            ->count();

        // ── Coupon IA du jour ─────────────────────────────────────────────────
        $coupon = DB::table('combined_bets')
            ->where('type', 'daily')
            ->whereDate('date', $today)
            ->where('is_published', true)
            ->first();

        // ── Construire le titre et le corps ───────────────────────────────────
        if ($predictions->isEmpty()) {
            $title = '⚽ COTA — Analyse du jour';
            $body  = 'Nos algorithmes analysent les matchs du jour. Revenez bientôt pour vos pronostics.';
        } else {
            $stars     = str_repeat('⭐', (int) ($predictions->first()->confidence_stars ?? 2));
            $topPick   = $predictions->first();
            $pickLine  = "{$topPick->home_team} vs {$topPick->away_team} → {$topPick->predicted_outcome}";
            $oddsLine  = $topPick->odds ? " @{$topPick->odds}" : '';

            $title = "⚽ {$totalPredictions} pronostics prêts {$stars}";
            $body  = "{$pickLine}{$oddsLine}";

            // Ajouter infos coupon si disponible
            if ($coupon) {
                $body .= "\n🎯 Coupon IA : cote combinée @{$coupon->total_odds} ({$coupon->predictions_count} picks)";
            }

            // Ajouter top 3 autres picks
            $otherPicks = $predictions->skip(1)->take(2);
            if ($otherPicks->isNotEmpty()) {
                $lines = $otherPicks->map(fn($p) =>
                    "• {$p->home_team} vs {$p->away_team} → {$p->predicted_outcome}" . ($p->odds ? " @{$p->odds}" : '')
                )->implode("\n");
                $body .= "\n{$lines}";
            }
        }

        // ── Données pour deep link ─────────────────────────────────────────────
        $data = [
            'type'              => 'daily_predictions',
            'screen'            => 'dashboard',
            'date'              => $today,
            'total_predictions' => $totalPredictions,
            'has_coupon'        => $coupon ? 'true' : 'false',
            'coupon_odds'       => $coupon ? (string) $coupon->total_odds : '',
            'top_pick'          => $predictions->isNotEmpty()
                ? "{$predictions->first()->home_team} vs {$predictions->first()->away_team}"
                : '',
        ];

        $sent = $notificationService->sendToAll($title, $body, $data);

        Log::info('SendDailyNotificationJob: terminé', [
            'sent'        => $sent,
            'predictions' => $totalPredictions,
            'has_coupon'  => (bool) $coupon,
        ]);
    }
}
