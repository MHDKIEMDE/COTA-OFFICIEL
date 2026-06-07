<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Prediction;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ValueBettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Notification quotidienne 9h WAT — meilleur pari EV+ du jour.
 * Envoyée aux admins + utilisateurs premium actifs.
 */
class SendBestValueBetNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function handle(NotificationService $notif, ValueBettingService $valueBetting): void
    {
        $best = Prediction::where('ev_positive', true)
            ->whereDate('match_date', Carbon::today())
            ->where('status', 'pending')
            ->where('is_published', true)
            ->orderByDesc('value_score')
            ->first();

        if (!$best) {
            Log::info('SendBestValueBetNotificationJob: aucun pari EV+ aujourd\'hui');
            return;
        }

        $stake   = $valueBetting->advisedStake((float) $best->kelly_fraction, 500_000);
        $gain    = $valueBetting->potentialGain((float) $best->odds, $stake);
        $pct     = (int) round((float) $best->value_score * 100);
        $match   = $best->home_team . ' vs ' . $best->away_team;
        $outcome = $best->prediction . ' @ ' . number_format((float) $best->odds, 2, '.', '');

        $title = "🏆 PARI DU JOUR — Valeur +{$pct}%";
        $body  = "{$match} | {$outcome}\nMise conseillée : "
               . number_format($stake, 0, ',', ' ') . ' FCFA'
               . ' → gain potentiel +' . number_format($gain, 0, ',', ' ') . ' FCFA';

        $data = [
            'type'          => 'best_value_bet',
            'prediction_id' => (string) $best->id,
            'screen'        => 'prediction_detail',
        ];

        $sent = 0;

        // Admins — toujours notifiés
        User::whereNotNull('fcm_token')
            ->where('is_admin', true)
            ->chunkById(200, function ($admins) use ($notif, $title, $body, $data, &$sent) {
                foreach ($admins as $admin) {
                    $sent += (int) $notif->sendToUser($admin->id, $title, $body, $data);
                }
            });

        // Utilisateurs premium actifs
        User::whereNotNull('fcm_token')
            ->where('is_premium', true)
            ->where('is_admin', false)
            ->where(fn($q) => $q->whereNull('subscription_expires_at')
                ->orWhere('subscription_expires_at', '>', now()))
            ->chunkById(500, function ($users) use ($notif, $title, $body, $data, &$sent) {
                foreach ($users as $user) {
                    $sent += (int) $notif->sendToUser($user->id, $title, $body, $data);
                }
            });

        Log::info('SendBestValueBetNotificationJob: terminé', [
            'prediction_id' => $best->id,
            'match'         => $match,
            'value_score'   => $best->value_score,
            'sent'          => $sent,
        ]);
    }
}
