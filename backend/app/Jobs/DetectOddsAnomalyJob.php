<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\OddsAnomaly;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\OddsAnomalyDetectorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Tourne toutes les 15 min.
 * 1. Nettoie les anomalies expirées
 * 2. Scanne les cotes live vs marché de référence
 * 3. Notifie immédiatement les admins pour chaque nouvelle anomalie
 */
class DetectOddsAnomalyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 90;

    public function handle(OddsAnomalyDetectorService $detector, NotificationService $notif): void
    {
        // 1. Purger les anomalies expirées
        $purged = $detector->purgeExpired();
        if ($purged > 0) {
            Log::info("DetectOddsAnomalyJob: {$purged} anomalies expirées supprimées");
        }

        // 2. Lancer le scan
        $detected = $detector->scan();

        if ($detected === 0) return;

        // 3. Notifier les nouvelles anomalies non encore notifiées
        $anomalies = OddsAnomaly::active()->unnotified()->get();

        foreach ($anomalies as $anomaly) {
            $this->notifyAnomaly($anomaly, $notif);
            $anomaly->update(['notified' => true]);
        }
    }

    private function notifyAnomaly(OddsAnomaly $anomaly, NotificationService $notif): void
    {
        $minutes  = $anomaly->minutes_remaining;
        $sign     = $anomaly->is_overpriced ? '⬆️' : '⬇️';
        $outcome  = match($anomaly->outcome) {
            '1' => $anomaly->home_team . ' gagne',
            '2' => $anomaly->away_team . ' gagne',
            'X' => 'Match nul',
            default => $anomaly->outcome,
        };

        $title = "⚡ COTE ANORMALE — {$anomaly->bookmaker}";
        $body  = "{$anomaly->home_team} vs {$anomaly->away_team}\n"
               . "{$outcome} @ {$anomaly->odd_value} (marché: {$anomaly->market_odd})\n"
               . "{$sign} Écart: +{$anomaly->gap_pct}% — ⏱ ~{$minutes} min restantes";

        $data = [
            'type'       => 'odds_anomaly',
            'anomaly_id' => (string) $anomaly->id,
            'screen'     => 'odds_anomalies',
        ];

        // Notifier tous les admins immédiatement
        User::whereNotNull('fcm_token')
            ->where('is_admin', true)
            ->chunkById(100, function ($admins) use ($notif, $title, $body, $data) {
                foreach ($admins as $admin) {
                    $notif->sendToUser($admin->id, $title, $body, $data);
                }
            });

        Log::info('DetectOddsAnomalyJob: notification envoyée', [
            'match'   => $anomaly->home_team . ' vs ' . $anomaly->away_team,
            'gap_pct' => $anomaly->gap_pct,
        ]);
    }
}
