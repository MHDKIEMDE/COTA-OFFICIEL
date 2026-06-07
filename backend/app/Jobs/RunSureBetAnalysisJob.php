<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Prediction;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SureBetAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Tourne à 09h30 UTC (après génération des prédictions).
 * Analyse les favoris à cote élevée et notifie les admins des "coups sûrs".
 */
class RunSureBetAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180;

    public function handle(SureBetAnalysisService $sureBet, NotificationService $notif): void
    {
        $count = $sureBet->analyzeToday();

        Log::info("RunSureBetAnalysisJob: {$count} coup(s) sûr(s) identifié(s)");

        if ($count === 0) return;

        // Notifier les admins pour chaque coup sûr
        $sureBets = Prediction::whereDate('match_date', today())
            ->whereNotNull('sure_bet_level')
            ->where('status', 'pending')
            ->orderByDesc('sure_bet_level')
            ->get();

        foreach ($sureBets as $prediction) {
            $level   = $prediction->sure_bet_level;
            $match   = $prediction->home_team . ' vs ' . $prediction->away_team;
            $outcome = $prediction->prediction . ' @ ' . number_format((float) $prediction->odds, 2);

            $title = "🔒 COUP SÛR {$level}% — {$prediction->competition}";
            $body  = "{$match}\n{$outcome}\nToutes conditions vérifiées (blessures, météo, forme, H2H)";

            $data = [
                'type'          => 'sure_bet',
                'prediction_id' => (string) $prediction->id,
                'level'         => $level,
                'screen'        => 'prediction_detail',
            ];

            User::whereNotNull('fcm_token')
                ->where('is_admin', true)
                ->chunkById(100, function ($admins) use ($notif, $title, $body, $data) {
                    foreach ($admins as $admin) {
                        $notif->sendToUser($admin->id, $title, $body, $data);
                    }
                });
        }
    }
}
