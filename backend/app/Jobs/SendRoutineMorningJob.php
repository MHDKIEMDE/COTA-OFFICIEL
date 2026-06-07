<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Prediction;
use App\Models\User;
use App\Services\NotificationQuotaService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Routine matin — 08h00 UTC (09h WAT).
 * Envoie le pronostic phare du jour : le pick avec le score de confiance le plus élevé.
 */
class SendRoutineMorningJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function handle(NotificationService $notif, NotificationQuotaService $quota): void
    {
        // Récupérer le pick le plus fort du jour (publié, confiance max)
        $topPick = Prediction::whereDate('match_date', today())
            ->where('is_published', true)
            ->orderByDesc('confidence_score')
            ->first();

        if (!$topPick) {
            Log::info('SendRoutineMorningJob: aucun pronostic publié aujourd\'hui');
            return;
        }

        $stars   = str_repeat('⭐', $topPick->stars ?? 2);
        $match   = $topPick->home_team . ' vs ' . $topPick->away_team;
        $outcome = $topPick->outcome ?? $topPick->prediction ?? 'Voir l\'analyse';
        $odds    = $topPick->estimated_odds ?? '';

        $sent = 0;

        User::whereNotNull('fcm_token')
            ->chunkById(500, function ($users) use ($notif, $quota, $topPick, $stars, $match, $outcome, $odds, &$sent) {
                $eligible = $quota->filterEligible($users, 'routine_morning');

                foreach ($eligible as $user) {
                    $locale = $user->locale ?? 'fr';

                    [$title, $body] = $locale === 'en'
                        ? [
                            "{$stars} Pick of the day — {$match}",
                            "Our prediction: {$outcome}" . ($odds ? " @ {$odds}" : '') . '. Tap to see the full analysis.',
                        ]
                        : [
                            "{$stars} Pronostic du jour — {$match}",
                            "Notre prédiction : {$outcome}" . ($odds ? " @ {$odds}" : '') . '. Appuie pour voir l\'analyse complète.',
                        ];

                    $sent += (int) $notif->sendToUser($user->id, $title, $body, [
                        'type'          => 'routine_morning',
                        'screen'        => 'prediction_detail',
                        'prediction_id' => (string) $topPick->id,
                    ]);

                    $quota->increment($user->id);
                }
            });

        Log::info('SendRoutineMorningJob: terminé', ['sent' => $sent, 'prediction_id' => $topPick->id]);
    }
}
